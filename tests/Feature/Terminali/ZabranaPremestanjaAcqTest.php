<?php

namespace Tests\Feature\Terminali;

use App\Http\Livewire\Distributer\DistTerminal;
use App\Http\Livewire\LicencaTerminal;
use App\Http\Livewire\Lokacijes;
use App\Models\DistributerUserIndex;
use App\Models\Lokacija;
use App\Models\LokacijaTip;
use App\Models\Region;
use App\Models\Terminal;
use App\Models\TerminalAcq;
use App\Models\TerminalLokacija;
use App\Models\TerminalLokacijaHistory;
use App\Models\TerminalStatusTip;
use App\Models\TerminalTip;
use App\Models\TerminalVendor;
use App\Models\TiketPrioritetTip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Uređaj pod aktivnim ACQ TID-om se ne premešta ni sa jedne stranice za terminale
 * (Licence, Lokacije, Distributer; Terminali je u AcqAkcijeTest), nego kroz ACQ terminali.
 */
class ZabranaPremestanjaAcqTest extends TestCase
{
    use RefreshDatabase;

    private const DIST = 11;

    private const STATUS_MAGACIN = 1;

    private const STATUS_INSTALIRAN = 2;

    private Lokacija $trgovac;

    private Lokacija $magacin;

    private TerminalLokacija $podTidom;

    private TerminalLokacija $slobodan;

    protected function setUp(): void
    {
        parent::setUp();

        $regionId = Region::create(['r_naziv' => 'Beograd'])->id;
        foreach ([1 => 'Servisni centar', 2 => 'Magacin', 3 => 'Korisnik terminala', 4 => 'Distributer'] as $id => $naziv) {
            LokacijaTip::forceCreate(['id' => $id, 'lt_naziv' => $naziv]);
        }
        TerminalStatusTip::forceCreate(['id' => self::STATUS_MAGACIN, 'ts_naziv' => 'Magacin']);
        TerminalStatusTip::forceCreate(['id' => self::STATUS_INSTALIRAN, 'ts_naziv' => 'Instaliran']);
        TiketPrioritetTip::forceCreate(['id' => 4, 'tp_naziv' => 'Normalan', 'btn_collor' => 'green-600', 'btn_hover_collor' => 'green-700', 'tr_bg_collor' => 'green-50']);
        TerminalVendor::forceCreate(['name' => 'HALK']);
        TerminalTip::forceCreate(['id' => 1, 'model' => 'CS10', 'proizvodjac' => 'Ciontek']);

        $this->trgovac = Lokacija::factory()->create(['regionId' => $regionId, 'lokacija_tipId' => 3, 'distributerId' => null]);
        $this->magacin = Lokacija::factory()->create(['regionId' => $regionId, 'lokacija_tipId' => 2, 'distributerId' => null]);
        $this->podTidom = $this->uredjaj('A26-12RB-POD-TIDOM', $this->trgovac->id);
        $this->slobodan = $this->uredjaj('A26-12RB-SLOBODAN', $this->trgovac->id);
        TerminalAcq::factory()->saUredjajem($this->podTidom->terminalId)->create(['acq_tid' => 'PN004096']);

        $korisnik = User::factory()->create(['pozicija_tipId' => 1, 'lokacijaId' => $this->trgovac->id]);
        DistributerUserIndex::create(['userId' => $korisnik->id, 'licenca_distributer_tipsId' => self::DIST]);
        $this->actingAs($korisnik);
    }

    #[Test]
    public function licence_ne_premestaju_uredjaj_pod_tid_om(): void
    {
        Livewire::test(LicencaTerminal::class)
            ->call('premestiShowModal', $this->podTidom->id, self::STATUS_INSTALIRAN)
            ->assertSet('licencaError', 'acq')
            ->assertSet('modalConfirmPremestiVisible', false)
            ->assertSee('pod aktivnim ACQ TID-om (PN004096)')
            ->call('premestiShowModal', $this->slobodan->id, self::STATUS_INSTALIRAN)
            ->assertSet('licencaError', '')
            ->assertSet('modalConfirmPremestiVisible', true);

        Livewire::test(LicencaTerminal::class)
            ->set('selectedTerminals', [(string) $this->slobodan->id, (string) $this->podTidom->id])
            ->call('premestiSelectedShowModal')
            ->assertSet('licencaError', 'acq')
            ->set('multiSelected', true)
            ->set('plokacija', $this->magacin->id)
            ->set('modalStatusPremesti', self::STATUS_MAGACIN)
            ->call('moveTerminal')
            ->assertSet('licencaError', 'acq');

        $this->assertNistaNijePremesteno();
    }

    #[Test]
    public function lokacije_ne_dodaju_uredjaj_pod_tid_om(): void
    {
        Livewire::test(Lokacijes::class)
            ->call('addTerminalShowModal', $this->magacin->id)
            ->set('addingType', 'location')
            ->set('selsectedTerminals', [$this->slobodan->id, $this->podTidom->id])
            ->set('t_status', self::STATUS_MAGACIN)
            ->call('addTerminal')
            ->assertSet('licencaError', 'acq')
            ->assertSet('modalAddTerminalVisible', false)
            ->assertSee('pod aktivnim ACQ TID-om (PN004096)');

        $this->assertNistaNijePremesteno();
    }

    #[Test]
    public function distributer_ne_premesta_uredjaj_pod_tid_om(): void
    {
        Livewire::test(DistTerminal::class)
            ->call('premestiShowModal', $this->podTidom->id, self::STATUS_INSTALIRAN)
            ->assertSet('licencaError', 'acq')
            ->assertSet('modalErorLicencaVisible', true)
            ->assertSet('modalConfirmPremestiVisible', false)
            ->assertSee('pod aktivnim ACQ TID-om (PN004096)');

        Livewire::test(DistTerminal::class)
            ->set('selectedTerminals', [(string) $this->podTidom->id])
            ->set('multiSelected', true)
            ->set('plokacija', $this->magacin->id)
            ->set('modalStatusPremesti', self::STATUS_MAGACIN)
            ->call('moveTerminal')
            ->assertSet('licencaError', 'acq');

        $this->assertNistaNijePremesteno();
    }

    #[Test]
    public function licence_i_distributer_boje_red_uredjaja_pod_tid_om(): void
    {
        foreach ([LicencaTerminal::class, DistTerminal::class] as $komponenta) {
            $html = Livewire::test($komponenta)->assertSee('A26-12RB-SLOBODAN')->html();

            preg_match_all('/class="(bg-sky-(?:50|100))"\s+title="ACQ TID (PN\d{6})"/', $html, $pogodci);
            $this->assertSame(['PN004096'], $pogodci[2], $komponenta);
            $this->assertSame(1, preg_match_all('/<tr\s+class="bg-sky-/', $html), $komponenta.': samo red sa TID-om je plav');
        }
    }

    #[Test]
    public function pretraga_po_sn_ili_tid_na_listama_terminala(): void
    {
        $sn = fn (array $search): array => \App\Actions\Terminali\TerminaliReadActions::TerminaliRead($search)->pluck('sn')->sort()->values()->all();

        $this->assertSame(['A26-12RB-POD-TIDOM'], $sn(['searchSB' => 'pn0040']));
        $this->assertSame(['A26-12RB-SLOBODAN'], $sn(['searchSB' => 'SLOBODAN']));
        $this->assertSame(['A26-12RB-POD-TIDOM', 'A26-12RB-SLOBODAN'], $sn(['searchSB' => 'A26-12RB']));

        Livewire::test(\App\Http\Livewire\Terminal::class)
            ->set('searchSB', 'PN004096')
            ->assertSee('A26-12RB-POD-TIDOM')
            ->assertDontSee('A26-12RB-SLOBODAN');
    }

    private function assertNistaNijePremesteno(): void
    {
        $this->assertSame($this->trgovac->id, $this->podTidom->fresh()->lokacijaId);
        $this->assertSame($this->trgovac->id, $this->slobodan->fresh()->lokacijaId);
        $this->assertSame(0, TerminalLokacijaHistory::count());
    }

    private function uredjaj(string $sn, int $lokacijaId): TerminalLokacija
    {
        $terminal = Terminal::create(['sn' => $sn, 'terminal_tipId' => 1, 'vendor_id' => null]);

        return TerminalLokacija::create([
            'terminalId' => $terminal->id,
            'lokacijaId' => $lokacijaId,
            'terminal_statusId' => self::STATUS_INSTALIRAN,
            'korisnikId' => 1,
            'korisnikIme' => 'Test',
            'distributerId' => self::DIST,
        ]);
    }
}
