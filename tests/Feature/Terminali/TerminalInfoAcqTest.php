<?php

namespace Tests\Feature\Terminali;

use App\Http\Livewire\Komponente\TerminalInfo;
use App\Models\Lokacija;
use App\Models\Region;
use App\Models\Terminal;
use App\Models\TerminalAcq;
use App\Models\TerminalLokacija;
use App\Models\TerminalStatusTip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Komponenta "Terminal info" prikazuje i podatke ACQ TID-a kad je uređaj pod aktivnim TID-om.
 */
class TerminalInfoAcqTest extends TestCase
{
    use RefreshDatabase;

    private Lokacija $trgovac;

    protected function setUp(): void
    {
        parent::setUp();

        TerminalStatusTip::forceCreate(['id' => 2, 'ts_naziv' => 'Instaliran']);
        $this->trgovac = Lokacija::factory()->create([
            'regionId' => Region::create(['r_naziv' => 'Beograd'])->id,
            'lokacija_tipId' => 3,
            'distributerId' => null,
            'acq_mid' => '000000000004388',
            'mcc' => '5812',
        ]);
        $this->actingAs(User::factory()->create(['pozicija_tipId' => 1, 'lokacijaId' => $this->trgovac->id]));
    }

    #[Test]
    public function uredjaj_pod_tid_om_prikazuje_podatke_tid_a(): void
    {
        $uredjaj = $this->uredjaj('A26-12RB-1K14121');
        $acq = TerminalAcq::factory()->saUredjajem($uredjaj->terminalId)->create([
            'acq_tid' => 'PN004096',
            'naziv_na_slipu' => 'SAVSKI VENAC RENT',
            'komunikacija' => 'GPRS',
            'datum_instalacije' => '2026-03-18',
        ]);
        TerminalAcq::factory()->create(['acq_tid' => 'PN005313', 'prethodni_terminal_acqId' => $acq->id]);

        Livewire::test(TerminalInfo::class, ['terminal_lokacija_id' => $uredjaj->id])
            ->assertSee('A26-12RB-1K14121')
            ->assertSee('PN004096')
            ->assertSee('SAVSKI VENAC RENT')
            ->assertSee('000000000004388')
            ->assertSee('5812')
            ->assertSee('18.03.2026')
            ->assertSee('Zatražena prekonfiguracija na:')
            ->assertSee('PN005313');
    }

    #[Test]
    public function uredjaj_bez_tid_a_nema_deo_za_tid(): void
    {
        $uredjaj = $this->uredjaj('A26-12RB-BEZ-TID');

        Livewire::test(TerminalInfo::class, ['terminal_lokacija_id' => $uredjaj->id])
            ->assertSee('A26-12RB-BEZ-TID')
            ->assertDontSee('ACQ TID')
            ->assertSet('acq', null);
    }

    #[Test]
    public function vise_izabranih_prikazuje_tid_uz_sn(): void
    {
        $podTidom = $this->uredjaj('A26-12RB-POD-TIDOM');
        $bezTida = $this->uredjaj('A26-12RB-BEZ-TID');
        TerminalAcq::factory()->saUredjajem($podTidom->terminalId)->create(['acq_tid' => 'PN004096']);

        Livewire::test(TerminalInfo::class, [
            'terminal_lokacija_id' => 0,
            'multySelected' => true,
            'multySelectedArray' => [$podTidom->id, $bezTida->id],
        ])
            ->assertSee('A26-12RB-POD-TIDOM')
            ->assertSeeText('TID: PN004096')
            ->assertSee('A26-12RB-BEZ-TID');
    }

    private function uredjaj(string $sn): TerminalLokacija
    {
        $terminal = Terminal::create(['sn' => $sn, 'terminal_tipId' => 1]);

        return TerminalLokacija::create([
            'terminalId' => $terminal->id,
            'lokacijaId' => $this->trgovac->id,
            'terminal_statusId' => 2,
            'korisnikId' => 1,
            'korisnikIme' => 'Test',
        ]);
    }
}
