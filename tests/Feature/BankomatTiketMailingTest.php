<?php

namespace Tests\Feature;

use App\Actions\Bankomati\BankomatTiketMailingActions;
use App\Models\Bankomat;
use App\Models\BankomatLokacija;
use App\Models\BankomatProductTip;
use App\Models\BankomatRegion;
use App\Models\BankomatTiket;
use App\Models\BankomatTiketPrioritetTip;
use App\Models\BankomatTip;
use App\Models\Blokacija;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\NedostupanSmtp;
use Tests\TestCase;

/**
 * Neuspelo slanje maila za tiket bankomata ne sme da sruši akciju nad tiketom,
 * niti da prekine slanje ostalim primaocima.
 */
class BankomatTiketMailingTest extends TestCase
{
    use NedostupanSmtp;
    use RefreshDatabase;

    private const KREIRAO = 'kreirao@test.rs';

    private const DODELJEN = 'dodeljen@test.rs';

    #[Test]
    public function svi_primaoci_dobijaju_mail_kad_smtp_radi(): void
    {
        $this->smtpNedostupanZa([]);

        $neuspesne = (new BankomatTiketMailingActions($this->tiket()->id))->sendEmails('novi');

        $this->assertSame([], $neuspesne);
        $this->assertSame([self::KREIRAO, self::DODELJEN], $this->transport->isporuceno);
    }

    #[Test]
    public function nedostupan_smtp_ne_baca_izuzetak_i_loguje_svaku_adresu(): void
    {
        $this->smtpNedostupanZa([self::KREIRAO, self::DODELJEN]);
        Log::spy();
        $tiket = $this->tiket();

        $neuspesne = (new BankomatTiketMailingActions($tiket->id))->sendEmails('novi');

        $this->assertSame([self::KREIRAO, self::DODELJEN], $neuspesne);
        $this->assertSame([], $this->transport->isporuceno);
        foreach ([self::KREIRAO, self::DODELJEN] as $adresa) {
            Log::shouldHaveReceived('error')
                ->with(Mockery::on(fn (string $poruka) => str_contains($poruka, "Bankomat tiket #{$tiket->id}") && str_contains($poruka, $adresa)))
                ->once();
        }
    }

    #[Test]
    public function neuspeh_za_jednog_primaoca_ne_prekida_slanje_ostalima(): void
    {
        $this->smtpNedostupanZa([self::KREIRAO]);
        Log::spy();

        $neuspesne = (new BankomatTiketMailingActions($this->tiket()->id))->sendEmails('novi');

        $this->assertSame([self::KREIRAO], $neuspesne);
        $this->assertSame([self::DODELJEN], $this->transport->isporuceno);
    }

    /**
     * Akciju pokreće treći korisnik, pa mail ide i kreatoru i dodeljenom
     * (prijavljeni korisnik ne dobija mail o sopstvenoj akciji).
     */
    private function tiket(): BankomatTiket
    {
        $region = BankomatRegion::forceCreate(['r_naziv' => 'Beograd']);
        $blokacija = Blokacija::forceCreate(['bl_naziv' => 'Test lokacija', 'bl_mesto' => 'Beograd', 'bankomat_region_id' => $region->id]);
        $productTip = BankomatProductTip::forceCreate(['bp_tip_naziv' => 'Bankomat']);
        $bankomatTip = BankomatTip::forceCreate(['model' => 'Test model', 'bankomat_produkt_tip_id' => $productTip->id]);
        $bankomat = Bankomat::forceCreate(['b_sn' => 'SN-TEST-1', 'b_terminal_id' => 'TID-1', 'bankomat_tip_id' => $bankomatTip->id]);
        $bankomatLokacija = BankomatLokacija::forceCreate(['bankomat_id' => $bankomat->id, 'blokacija_id' => $blokacija->id]);
        $prioritet = BankomatTiketPrioritetTip::forceCreate([
            'btpt_naziv' => 'Normalan',
            'btpt_opis' => 'Test',
            'time_frame' => '120:00:00',
            'btn_collor' => 'bg-red-600',
            'btn_hover_collor' => 'bg-red-700',
            'tr_bg_collor' => 'bg-red-50',
        ]);

        $this->actingAs(User::factory()->create(['pozicija_tipId' => 9, 'lokacijaId' => $blokacija->id]));

        return BankomatTiket::forceCreate([
            'bankomat_lokacija_id' => $bankomatLokacija->id,
            'bankomat_tiket_prioritet_id' => $prioritet->id,
            'opis' => 'Ne izdaje novac',
            'status' => 'Otvoren',
            'user_prijava_id' => User::factory()->create(['email' => self::KREIRAO, 'pozicija_tipId' => 9, 'lokacijaId' => $blokacija->id])->id,
            'user_dodeljen_id' => User::factory()->create(['email' => self::DODELJEN, 'pozicija_tipId' => 11, 'lokacijaId' => $blokacija->id])->id,
        ]);
    }
}
