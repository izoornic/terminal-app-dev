<?php

namespace Tests\Feature\Terminali;

use App\Actions\TerminalAcq\TidInfoUredjaja;
use App\Http\Livewire\Komponente\TerminalInfo;
use App\Http\Livewire\Tiketview;
use App\Models\Tiket;
use App\Models\TiketAkcijaKorisnikPozicija;
use App\Models\TiketAkcijaTip;
use App\Models\TiketAkcijaVrednostTip;
use App\Models\TiketOpisKvaraTip;
use App\Models\TiketPrioritetTip;
use App\Models\TiketStatusTip;
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

    #[Test]
    public function tid_info_uredjaja(): void
    {
        $podTidom = $this->uredjaj('A26-12RB-POD-TIDOM');
        $bezTida = $this->uredjaj('A26-12RB-BEZ-TID');
        TerminalAcq::factory()->saUredjajem($podTidom->terminalId)->create(['acq_tid' => 'PN004096', 'naziv_na_slipu' => 'SLIP']);

        $this->assertSame(
            ['acq_tid' => 'PN004096', 'naziv_na_slipu' => 'SLIP', 'acq_mid' => '000000000004388', 'mcc' => '5812'],
            array_intersect_key(TidInfoUredjaja::zaTerminalLokaciju($podTidom->id), array_flip(['acq_tid', 'naziv_na_slipu', 'acq_mid', 'mcc']))
        );
        $this->assertNull(TidInfoUredjaja::zaTerminalLokaciju($bezTida->id));
        $this->assertNull(TidInfoUredjaja::zaTerminalLokaciju(null));
    }

    #[Test]
    public function stranica_tiketa_prikazuje_tid_terminala(): void
    {
        $sve = TiketAkcijaVrednostTip::forceCreate(['akcija_vrednost_opis' => 'sve']);
        foreach ([1 => 'vidi tiket', 2 => 'kreira tiket', 3 => 'dodeljuje tiket'] as $akcijaId => $akcija) {
            TiketAkcijaKorisnikPozicija::forceCreate([
                'korisnik_pozicijaId' => 1,
                'tiket_akcijaId' => TiketAkcijaTip::forceCreate(['id' => $akcijaId, 'tiket_akcija' => $akcija])->id,
                'tiket_akcijavrednostId' => $sve->id,
            ]);
        }
        TiketStatusTip::forceCreate(['id' => 1, 'tks_naziv' => 'Otvoren']);
        $prioritet = TiketPrioritetTip::forceCreate(['tp_naziv' => 'Normalan', 'btn_collor' => 'green-600', 'btn_hover_collor' => 'green-700', 'tr_bg_collor' => 'green-50']);
        $kvar = TiketOpisKvaraTip::forceCreate(['termnal_tipId' => 1, 'tok_naziv' => 'Ne radi štampač', 'tok_dodela_nadleznostiId' => 1, 'list_order' => 1]);

        $uredjaj = $this->uredjaj('A26-12RB-1K14121');
        TerminalAcq::factory()->saUredjajem($uredjaj->terminalId)->create(['acq_tid' => 'PN004096', 'naziv_na_slipu' => 'SAVSKI VENAC RENT']);
        $tiket = Tiket::create([
            'tremina_lokacijalId' => $uredjaj->id, 'tiket_statusId' => 1, 'opis_kvaraId' => $kvar->id,
            'opis' => 'Test kvar', 'tiket_prioritetId' => $prioritet->id, 'br_komentara' => 0, 'korisnik_zatvorio_id' => 0,
            'korisnik_prijavaId' => auth()->id(),
        ]);

        Livewire::withQueryParams(['id' => $tiket->id])->test(Tiketview::class)
            ->assertSee('A26-12RB-1K14121')
            ->assertSee('PN004096')
            ->assertSee('SAVSKI VENAC RENT')
            ->assertSee('000000000004388');
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
