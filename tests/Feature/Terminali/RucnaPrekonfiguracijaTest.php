<?php

namespace Tests\Feature\Terminali;

use App\Http\Livewire\TerminalAcq\AcqTerminali;
use App\Http\Livewire\TerminalAcq\RucnaPrekonfiguracija;
use App\Models\Lokacija;
use App\Models\LokacijaKontaktOsoba;
use App\Models\PozicijaPrikazStranica;
use App\Models\Region;
use App\Models\Stranica;
use App\Models\Terminal;
use App\Models\TerminalAcq;
use App\Models\TerminalAcqOpcija;
use App\Models\TerminalLokacija;
use App\Models\TerminalStatusTip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Ručna prekonfiguracija (zahtev stigao mailom): isti tok kao Excel zahtev, za istu i
 * za novu firmu; uz "izvrši odmah" i izvršenje u istoj transakciji.
 */
class RucnaPrekonfiguracijaTest extends TestCase
{
    use RefreshDatabase;

    private const ADMIN = 1;

    private const SERVISER = 4;

    private const STATUS_INSTALIRAN = 2;

    private int $beograd;

    private int $nis;

    private Lokacija $trgovac;

    private TerminalLokacija $uredjaj;

    private TerminalAcq $stari;

    protected function setUp(): void
    {
        parent::setUp();

        $this->beograd = Region::create(['r_naziv' => 'Beograd'])->id;
        $this->nis = Region::create(['r_naziv' => 'Niš'])->id;
        TerminalStatusTip::forceCreate(['id' => self::STATUS_INSTALIRAN, 'ts_naziv' => 'Instaliran']);
        TerminalStatusTip::forceCreate(['id' => 8, 'ts_naziv' => 'Za instalaciju']);
        $stranica = Stranica::forceCreate(['naziv' => 'ACQ terminali', 'route_name' => AcqTerminali::ROUTE_NAME, 'menu_order' => 39, 'show_in_meni' => 1]);
        PozicijaPrikazStranica::create(['pozicija_tipId' => self::ADMIN, 'stranicaId' => $stranica->id]);
        $upravljanje = Stranica::forceCreate(['naziv' => 'ACQ upravljanje', 'route_name' => AcqTerminali::PRAVO_UPRAVLJANJE, 'menu_order' => 39, 'show_in_meni' => 0]);
        PozicijaPrikazStranica::create(['pozicija_tipId' => self::ADMIN, 'stranicaId' => $upravljanje->id]);

        $this->trgovac = Lokacija::factory()->create([
            'regionId' => $this->nis, 'lokacija_tipId' => 3, 'distributerId' => null,
            'l_naziv' => 'SAVSKI VENAC RENT', 'adresa' => 'Dr. Aleksandra Kostića 15A', 'mesto' => 'Beograd',
            'ptt' => '11000', 'pib' => '113735946', 'acq_mid' => '000000000004388', 'mcc' => '5812',
        ]);
        LokacijaKontaktOsoba::create(['lokacijaId' => $this->trgovac->id, 'name' => 'Momčilo Radonjić', 'tel' => '+38166 5000002']);
        $terminal = Terminal::create(['sn' => 'A26-12RB-1K14121', 'terminal_tipId' => 1]);
        $this->uredjaj = TerminalLokacija::create([
            'terminalId' => $terminal->id, 'lokacijaId' => $this->trgovac->id,
            'terminal_statusId' => self::STATUS_INSTALIRAN, 'korisnikId' => 1, 'korisnikIme' => 'Test',
        ]);
        $this->stari = TerminalAcq::factory()->saUredjajem($terminal->id)->create(['acq_tid' => 'PN004096', 'naziv_na_slipu' => 'SAVSKI VENAC RENT']);

        $this->actingAs(User::factory()->create(['pozicija_tipId' => self::ADMIN, 'lokacijaId' => $this->trgovac->id]));
    }

    #[Test]
    public function ista_firma_pravi_tid_koji_ceka_tehnicara(): void
    {
        $cashback = TerminalAcqOpcija::where('naziv', 'Cashback')->sole();
        $rucniUnos = TerminalAcqOpcija::where('naziv', 'Ručni unos')->sole();
        $this->stari->opcijeVeze()->attach([$cashback->id => ['ukljucena' => true], $rucniUnos->id => ['ukljucena' => false]]);

        Livewire::test(RucnaPrekonfiguracija::class)
            ->dispatch('rucnaPrekonfiguracija', stariId: $this->stari->id)
            ->assertSet('modalVisible', true)
            ->assertSet('pib', '113735946')
            ->assertSet('kontakt', 'Momčilo Radonjić')
            ->set('novi_tid', 'PN005313')
            ->set('acq_mid', '000000000004845')
            ->set('napomena', 'mail Nikola 29.10.')
            ->call('proveri')
            ->assertSet('lokacija', (string) $this->trgovac->id)
            ->assertSee('biće zamenjen pri izvršenju')
            ->call('sacuvaj')
            ->assertSet('greska', null)
            ->assertSet('modalVisible', false)
            ->assertDispatched('tidDodat');

        $novi = TerminalAcq::where('acq_tid', 'PN005313')->sole();
        $this->assertSame(TerminalStatusTip::idPoNazivu(TerminalStatusTip::CEKA_PREKONFIGURACIJU), $novi->terminal_statusId);
        $this->assertSame($this->stari->id, $novi->prethodni_terminal_acqId);
        $this->assertSame($this->trgovac->id, $novi->lokacijaId);
        $this->assertSame('000000000004845', $novi->zahtev_acq_mid);
        $this->assertSame(TerminalAcq::IZVOR_RUCNO, $novi->izvor);
        $this->assertNull($novi->izvor_fajl);
        $this->assertSame('prekonfiguracija PN004096; mail Nikola 29.10.', $novi->dodatne_opcije);
        $this->assertNotNull($this->stari->fresh()->terminalId, 'stari TID ostaje aktivan do izvršenja');
        $this->assertSame(['Cashback'], $novi->opcije()->pluck('naziv')->all(), 'novi TID preuzima uključene opcije starog');
    }

    #[Test]
    public function nova_firma_sa_izvrsenjem_odmah(): void
    {
        Livewire::test(RucnaPrekonfiguracija::class)
            ->dispatch('rucnaPrekonfiguracija', stariId: $this->stari->id)
            ->set('novi_tid', 'PN005313')
            ->set('naziv_trgovca', 'PRE SUDA DOO')
            ->set('naziv_na_slipu', 'PRE-SUDA DOO')
            ->set('pib', '115682682')
            ->set('acq_mid', '000000000004845')
            ->set('kontakt', 'Momčilo Radonjić')
            ->call('proveri')
            ->assertSet('lokacija', RucnaPrekonfiguracija::NOVA_LOKACIJA)
            ->assertSet('regionId', $this->nis)
            ->set('izvrsiOdmah', true)
            ->call('sacuvaj')
            ->assertSet('greska', null)
            ->assertDispatched('tidDodat', poruka: 'Prekonfiguracija PN004096 → PN005313 je izvršena.');

        $nova = Lokacija::where('pib', '115682682')->sole();
        $this->assertSame('PRE SUDA DOO', $nova->l_naziv);
        $this->assertSame($this->nis, $nova->regionId);
        $this->assertSame('000000000004845', $nova->acq_mid);

        $novi = TerminalAcq::where('acq_tid', 'PN005313')->sole();
        $this->assertSame($this->uredjaj->terminalId, $novi->terminalId);
        $this->assertSame($nova->id, $this->uredjaj->fresh()->lokacijaId);
        $this->assertSame(self::STATUS_INSTALIRAN, $this->uredjaj->fresh()->terminal_statusId);
        $this->assertSame(TerminalStatusTip::idPoNazivu(TerminalStatusTip::PREKONFIGURISAN), $this->stari->fresh()->terminal_statusId);
    }

    #[Test]
    public function greske_ne_upisuju_nista(): void
    {
        TerminalAcq::factory()->create(['acq_tid' => 'PN000001']);

        $forma = Livewire::test(RucnaPrekonfiguracija::class)
            ->dispatch('rucnaPrekonfiguracija', stariId: $this->stari->id);

        $forma->set('novi_tid', 'PN12')->call('proveri')->assertSee('Neispravan TID');
        $forma->set('novi_tid', 'PN000001')->call('sacuvaj')->assertSet('greska', 'Ispravite greške pa sačuvajte ponovo.')->assertSee('TID već postoji');
        $forma->set('novi_tid', 'PN005313')->set('pib', '115682682')->set('mesto', '')->call('sacuvaj')
            ->assertSet('greska', 'Ispravite greške pa sačuvajte ponovo.')
            ->assertSee('Nedostaje mesto');

        $this->assertFalse(TerminalAcq::where('acq_tid', 'PN005313')->exists());
        $this->assertSame(0, Lokacija::where('pib', '115682682')->count());
    }

    #[Test]
    public function dugme_u_detalju_otvara_formu_samo_dok_prekonfiguracija_nije_trazena(): void
    {
        Livewire::test(AcqTerminali::class)
            ->call('prikaziDetalj', $this->stari->id)
            ->assertSee('Prekonfiguriši')
            ->call('otvoriPrekonfiguraciju')
            ->assertSet('detaljVisible', false)
            ->assertDispatched('rucnaPrekonfiguracija', stariId: $this->stari->id);

        TerminalAcq::factory()
            ->bezUredjaja(TerminalStatusTip::idPoNazivu(TerminalStatusTip::CEKA_PREKONFIGURACIJU), $this->trgovac->id)
            ->create(['acq_tid' => 'PN005313', 'prethodni_terminal_acqId' => $this->stari->id]);

        Livewire::test(AcqTerminali::class)
            ->call('prikaziDetalj', $this->stari->id)
            ->assertDontSee('Prekonfiguriši')
            ->assertSee('Prekonfiguracija je već zatražena: PN005313');
    }

    #[Test]
    public function bez_prava_forma_se_ne_ucitava(): void
    {
        $this->actingAs(User::factory()->create(['pozicija_tipId' => self::SERVISER, 'lokacijaId' => $this->trgovac->id]));

        Livewire::test(RucnaPrekonfiguracija::class)->assertForbidden();
    }
}
