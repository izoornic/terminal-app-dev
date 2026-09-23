<?php

namespace Tests\Feature\Terminali;

use App\Http\Livewire\Distributer\DistTerminal;
use App\Models\DistributerUserIndex;
use App\Models\Lokacija;
use App\Models\Region;
use App\Models\Terminal as TerminalModel;
use App\Models\TerminalLokacija;
use App\Models\TerminalStatusTip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Modal za premeštanje više izabranih terminala: status se uzima sa prvog izabranog
 * terminal_lokacijas reda (checkbox nosi tlid), a ako tog reda nema - "Instaliran".
 */
class DistTerminalPremestiTest extends TestCase
{
    use RefreshDatabase;

    private const DIST = 11;

    private const STATUS_MAGACIN = 1;

    private const STATUS_INSTALIRAN = 2;

    private const STATUS_ZAMENSKI = 3;

    private int $lokacijaId;

    protected function setUp(): void
    {
        parent::setUp();

        $region = Region::create(['r_naziv' => 'Beograd']);
        $this->lokacijaId = Lokacija::factory()->create(['regionId' => $region->id])->id;

        TerminalStatusTip::forceCreate(['id' => self::STATUS_MAGACIN, 'ts_naziv' => 'Magacin']);
        TerminalStatusTip::forceCreate(['id' => self::STATUS_INSTALIRAN, 'ts_naziv' => 'Instaliran']);
        TerminalStatusTip::forceCreate(['id' => self::STATUS_ZAMENSKI, 'ts_naziv' => 'Zamenski']);
    }

    #[Test]
    public function status_se_uzima_sa_prvog_izabranog_terminal_lokacija_reda(): void
    {
        $this->actingAs($this->distributerKorisnik());

        /**
         * Terminal bez lokacije, da se id-evi terminala i terminal_lokacijas redova ne poklope.
         */
        $this->terminal();

        $prvi = $this->terminalLokacija(self::STATUS_ZAMENSKI);

        /**
         * Red čiji je terminalId jednak id-u prvog izabranog: pretraga po pogrešnoj
         * koloni bi pokupila baš njega.
         */
        $this->terminalLokacija(self::STATUS_MAGACIN, $prvi->id);

        $this->assertNotSame($prvi->id, $prvi->terminalId);

        Livewire::test(DistTerminal::class)
            ->set('selectedTerminals', [(string) $prvi->id])
            ->call('premestiSelectedShowModal')
            ->assertOk()
            ->assertSet('multiSelected', true)
            ->assertSet('modalConfirmPremestiVisible', true)
            ->assertSet('modalStatusPremesti', self::STATUS_ZAMENSKI);
    }

    #[Test]
    public function bez_terminal_lokacija_reda_status_je_instaliran(): void
    {
        $this->actingAs($this->distributerKorisnik());

        $nepostojeci = $this->terminalLokacija(self::STATUS_ZAMENSKI)->id + 100;

        Livewire::test(DistTerminal::class)
            ->set('selectedTerminals', [(string) $nepostojeci])
            ->call('premestiSelectedShowModal')
            ->assertOk()
            ->assertSet('modalConfirmPremestiVisible', true)
            ->assertSet('modalStatusPremesti', self::STATUS_INSTALIRAN);
    }

    /**
     * DistTerminal::mount() traži distributera preko indeksa korisnika.
     */
    private function distributerKorisnik(): User
    {
        $user = User::factory()->create(['pozicija_tipId' => 1, 'lokacijaId' => $this->lokacijaId]);

        DistributerUserIndex::create(['userId' => $user->id, 'licenca_distributer_tipsId' => self::DIST]);

        return $user;
    }

    private function terminal(): TerminalModel
    {
        return TerminalModel::create([
            'sn' => fake()->unique()->numerify('SN########'),
            'terminal_tipId' => 1,
        ]);
    }

    private function terminalLokacija(int $statusId, ?int $terminalId = null): TerminalLokacija
    {
        return TerminalLokacija::create([
            'terminalId' => $terminalId ?? $this->terminal()->id,
            'lokacijaId' => $this->lokacijaId,
            'terminal_statusId' => $statusId,
            'korisnikId' => 1,
            'korisnikIme' => 'test',
            'distributerId' => self::DIST,
        ]);
    }
}
