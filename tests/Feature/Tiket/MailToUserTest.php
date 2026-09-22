<?php

namespace Tests\Feature\Tiket;

use App\Actions\Tiket\MailToUser;
use App\Models\Lokacija;
use App\Models\Region;
use App\Models\Terminal;
use App\Models\TerminalLokacija;
use App\Models\Tiket;
use App\Models\TiketPrioritetTip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\NedostupanSmtp;
use Tests\TestCase;

/**
 * Neuspelo slanje maila ne sme da sruši akciju nad tiketom (tiket je tada već upisan),
 * niti da prekine slanje ostalim primaocima.
 */
class MailToUserTest extends TestCase
{
    use NedostupanSmtp;
    use RefreshDatabase;

    private const KREIRAO = 'kreirao@test.rs';

    private const DODELJEN = 'dodeljen@test.rs';

    #[Test]
    public function svi_primaoci_dobijaju_mail_kad_smtp_radi(): void
    {
        $this->smtpNedostupanZa([]);

        $neuspesne = (new MailToUser($this->tiket()->id))->sendEmails('novi');

        $this->assertSame([], $neuspesne);
        $this->assertSame([self::KREIRAO, self::DODELJEN], $this->transport->isporuceno);
    }

    #[Test]
    public function nedostupan_smtp_ne_baca_izuzetak_i_loguje_svaku_adresu(): void
    {
        $this->smtpNedostupanZa([self::KREIRAO, self::DODELJEN]);
        Log::spy();
        $tiket = $this->tiket();

        $neuspesne = (new MailToUser($tiket->id))->sendEmails('novi');

        $this->assertSame([self::KREIRAO, self::DODELJEN], $neuspesne);
        $this->assertSame([], $this->transport->isporuceno);
        foreach ([self::KREIRAO, self::DODELJEN] as $adresa) {
            Log::shouldHaveReceived('error')
                ->with(Mockery::on(fn (string $poruka) => str_contains($poruka, "Tiket #{$tiket->id}") && str_contains($poruka, $adresa)))
                ->once();
        }
    }

    #[Test]
    public function neuspeh_za_jednog_primaoca_ne_prekida_slanje_ostalima(): void
    {
        $this->smtpNedostupanZa([self::KREIRAO]);
        Log::spy();

        $neuspesne = (new MailToUser($this->tiket()->id))->sendEmails('novi');

        $this->assertSame([self::KREIRAO], $neuspesne);
        $this->assertSame([self::DODELJEN], $this->transport->isporuceno);
    }

    /**
     * Tiket bez prijavljenog korisnika (online prijava) — mail ide i kreatoru i dodeljenom.
     */
    private function tiket(): Tiket
    {
        $region = Region::create(['r_naziv' => 'Beograd']);
        $lokacija = Lokacija::factory()->create(['regionId' => $region->id]);
        $terminal = Terminal::create(['sn' => fake()->unique()->numerify('SN########'), 'terminal_tipId' => 1]);
        $terminalLokacija = TerminalLokacija::create([
            'terminalId' => $terminal->id,
            'lokacijaId' => $lokacija->id,
            'terminal_statusId' => 1,
            'korisnikId' => 1,
            'korisnikIme' => 'Test',
        ]);
        $prioritet = TiketPrioritetTip::forceCreate([
            'tp_naziv' => 'Normalan',
            'btn_collor' => 'green-600',
            'btn_hover_collor' => 'green-700',
            'tr_bg_collor' => 'green-50',
        ]);

        return Tiket::create([
            'tremina_lokacijalId' => $terminalLokacija->id,
            'tiket_statusId' => 2,
            'opis_kvaraId' => 1,
            'korisnik_prijavaId' => User::factory()->create(['email' => self::KREIRAO, 'pozicija_tipId' => 1, 'lokacijaId' => $lokacija->id])->id,
            'korisnik_dodeljenId' => User::factory()->create(['email' => self::DODELJEN, 'pozicija_tipId' => 4, 'lokacijaId' => $lokacija->id])->id,
            'opis' => 'Ne radi štampač',
            'tiket_prioritetId' => $prioritet->id,
            'br_komentara' => 0,
            'korisnik_zatvorio_id' => 0,
        ]);
    }
}
