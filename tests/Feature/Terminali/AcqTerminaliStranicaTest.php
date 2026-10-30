<?php

namespace Tests\Feature\Terminali;

use App\Actions\TerminalAcq\TerminalAcqLista;
use App\Http\Livewire\TerminalAcq\DodajTerminal;
use App\Http\Livewire\TerminalAcq\AcqTerminali;
use App\Http\Livewire\TerminalAcq\UvozZahteva;
use App\Models\Lokacija;
use App\Models\PozicijaPrikazStranica;
use App\Models\Region;
use App\Models\Stranica;
use App\Models\Terminal;
use App\Models\TerminalAcq;
use App\Models\TerminalAcqHistory;
use App\Models\TerminalLokacija;
use App\Models\TerminalStatusTip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Faza 4.1: lista HALK TID-ova (pretraga, filteri po efektivnom statusu, detalj sa
 * istorijom) i "Dodaj terminal". Pristup: Admin i Call centar.
 */
class AcqTerminaliStranicaTest extends TestCase
{
    use RefreshDatabase;

    private const ADMIN = 1;

    private const CALL_CENTAR = 2;

    private const SERVISER = 4;

    private const STATUS_MAGACIN = 1;

    private const STATUS_INSTALIRAN = 2;

    private const STATUS_ZA_INSTALACIJU = 8;

    private int $regionId;

    private Lokacija $trgovac;

    protected function setUp(): void
    {
        parent::setUp();

        $this->regionId = Region::create(['r_naziv' => 'Beograd'])->id;
        TerminalStatusTip::forceCreate(['id' => self::STATUS_MAGACIN, 'ts_naziv' => 'Magacin']);
        TerminalStatusTip::forceCreate(['id' => self::STATUS_INSTALIRAN, 'ts_naziv' => 'Instaliran']);
        TerminalStatusTip::forceCreate(['id' => self::STATUS_ZA_INSTALACIJU, 'ts_naziv' => 'Za instalaciju']);
        $this->trgovac = $this->lokacija(['l_naziv' => 'SAVSKI VENAC RENT', 'pib' => '113735946', 'acq_mid' => '000000000004388', 'mcc' => null, 'ptt' => null]);
        $this->dodeliStranicu(AcqTerminali::ROUTE_NAME, [self::ADMIN, self::CALL_CENTAR]);
    }

    /**
     * @return array<string, array{int}>
     */
    public static function pozicijeSaPristupom(): array
    {
        return ['Admin' => [self::ADMIN], 'Call centar' => [self::CALL_CENTAR]];
    }

    #[Test]
    #[DataProvider('pozicijeSaPristupom')]
    public function admin_i_call_centar_otvaraju_stranicu(int $pozicijaId): void
    {
        $this->actingAs($this->korisnik($pozicijaId))
            ->get(route(AcqTerminali::ROUTE_NAME))
            ->assertOk()
            ->assertSeeLivewire(AcqTerminali::class)
            ->assertSeeLivewire(DodajTerminal::class);
    }

    #[Test]
    public function ostale_pozicije_nemaju_pristup(): void
    {
        $this->actingAs($this->korisnik(self::SERVISER))
            ->get(route(AcqTerminali::ROUTE_NAME))
            ->assertForbidden();

        Livewire::test(AcqTerminali::class)->assertForbidden();
        Livewire::test(DodajTerminal::class)->assertForbidden();
    }

    #[Test]
    public function dugme_za_uvoz_zahteva_vidi_samo_ko_ima_pravo_na_uvoz(): void
    {
        $link = 'href="'.route(UvozZahteva::ROUTE_NAME).'"';

        $this->actingAs($this->korisnik(self::ADMIN))->get(route(AcqTerminali::ROUTE_NAME))->assertDontSee($link, false);

        $this->dodeliStranicu(UvozZahteva::ROUTE_NAME, [self::ADMIN]);
        $this->actingAs($this->korisnik(self::ADMIN))->get(route(AcqTerminali::ROUTE_NAME))->assertSee($link, false);
    }

    #[Test]
    public function lista_prikazuje_efektivni_status_i_pretrazuje(): void
    {
        $this->actingAs($this->korisnik(self::ADMIN));
        $uredjaj = $this->uredjaj('A26-12RB-1K14121', $this->trgovac->id, self::STATUS_INSTALIRAN);
        TerminalAcq::factory()->saUredjajem($uredjaj->terminalId)->create(['acq_tid' => 'PN004096', 'naziv_na_slipu' => 'SAVSKI SLIP']);
        $drugaLokacija = $this->lokacija(['l_naziv' => 'GRIZZLY CAFFE', 'pib' => '115762060', 'acq_mid' => '000000000004839']);
        TerminalAcq::factory()->bezUredjaja(self::STATUS_ZA_INSTALACIJU, $drugaLokacija->id)->create(['acq_tid' => 'PN005306']);

        $lista = Livewire::test(AcqTerminali::class)
            ->assertSee('PN004096')
            ->assertSee('PN005306')
            ->assertSee('A26-12RB-1K14121')
            ->assertSee('GRIZZLY CAFFE');

        $lista->set('searchSn', '1K14121')->assertSee('PN004096')->assertDontSee('PN005306');
        $lista->set('searchSn', '')->set('searchPib', '115762060')->assertSee('PN005306')->assertDontSee('PN004096');
        $lista->set('searchPib', '')->set('searchMid', '4388')->assertSee('PN004096')->assertDontSee('PN005306');
        $lista->set('searchMid', '')->set('searchTrgovac', 'SAVSKI SLIP')->assertSee('PN004096')->assertDontSee('PN005306');
        $lista->set('searchTrgovac', '')->set('searchStatus', self::STATUS_INSTALIRAN)->assertSee('PN004096')->assertDontSee('PN005306');
    }

    #[Test]
    public function filter_statusa_nudi_samo_statuse_koje_tid_ovi_imaju(): void
    {
        $this->actingAs($this->korisnik(self::ADMIN));
        $prvi = $this->uredjaj('A26-12RB-0000001', $this->trgovac->id, self::STATUS_INSTALIRAN);
        $drugi = $this->uredjaj('A26-12RB-0000002', $this->trgovac->id, self::STATUS_INSTALIRAN);
        TerminalAcq::factory()->saUredjajem($prvi->terminalId)->create(['acq_tid' => 'PN000001']);
        TerminalAcq::factory()->saUredjajem($drugi->terminalId)->create(['acq_tid' => 'PN000002']);
        TerminalAcq::factory()->bezUredjaja(TerminalStatusTip::idPoNazivu(TerminalStatusTip::DEINSTALIRAN), $this->trgovac->id)->create(['acq_tid' => 'PN000003']);

        $this->assertSame(
            [self::STATUS_INSTALIRAN => 'Instaliran (2)', TerminalStatusTip::idPoNazivu(TerminalStatusTip::DEINSTALIRAN) => 'Deinstaliran (1)'],
            (new TerminalAcqLista)->statusiUpotrebljeni()
        );

        Livewire::test(AcqTerminali::class)
            ->assertSee('Instaliran (2)')
            ->assertSee('Deinstaliran (1)')
            ->assertDontSee('Magacin')
            ->assertDontSee('Opozvan');
    }

    #[Test]
    public function brzi_filteri(): void
    {
        $uredjaj = $this->uredjaj('A26-12RB-1K14121', $this->trgovac->id, self::STATUS_INSTALIRAN);
        TerminalAcq::factory()->saUredjajem($uredjaj->terminalId)->create(['acq_tid' => 'PN000001']);
        TerminalAcq::factory()->bezUredjaja(self::STATUS_ZA_INSTALACIJU, $this->trgovac->id)->create(['acq_tid' => 'PN000002']);
        TerminalAcq::factory()->bezUredjaja(TerminalStatusTip::idPoNazivu(TerminalStatusTip::CEKA_PREKONFIGURACIJU), $this->trgovac->id)->create(['acq_tid' => 'PN000003']);
        TerminalAcq::factory()->bezUredjaja(TerminalStatusTip::idPoNazivu(TerminalStatusTip::DEINSTALIRAN), $this->trgovac->id)->create(['acq_tid' => 'PN000004']);
        $lista = new TerminalAcqLista;

        $tidovi = fn (string $filter): array => $lista->upit(['brzi' => $filter])->pluck('acq_tid')->sort()->values()->all();

        $this->assertSame(['PN000001'], $tidovi(TerminalAcqLista::FILTER_AKTIVNI));
        $this->assertSame(['PN000002'], $tidovi(TerminalAcqLista::FILTER_CEKA_UREDJAJ));
        $this->assertSame(['PN000003'], $tidovi(TerminalAcqLista::FILTER_CEKA_PREKONFIGURACIJU));
        $this->assertSame(['PN000004'], $tidovi(TerminalAcqLista::FILTER_ZATVORENI));
    }

    #[Test]
    public function detalj_prikazuje_uredjaj_trgovca_i_istoriju(): void
    {
        $this->actingAs($this->korisnik(self::CALL_CENTAR));
        $stariUredjaj = Terminal::create(['sn' => 'A26-12RB-2D00992', 'terminal_tipId' => 1]);
        $uredjaj = $this->uredjaj('A26-12RB-1K14851', $this->trgovac->id, self::STATUS_INSTALIRAN);
        $acq = TerminalAcq::factory()->saUredjajem($uredjaj->terminalId)->create(['acq_tid' => 'PN001758']);
        TerminalAcqHistory::create([
            'terminal_acqId' => $acq->id, 'acq_tid' => 'PN001758', 'terminalId' => $stariUredjaj->id,
            'terminal_statusId' => self::STATUS_INSTALIRAN, 'lokacijaId' => $this->trgovac->id, 'acq_mid' => '000000000000123',
            'izvor' => TerminalAcq::IZVOR_UVOZ, 'korisnikId' => 1, 'korisnikIme' => 'Uvoz',
        ]);

        Livewire::test(AcqTerminali::class)
            ->call('prikaziDetalj', $acq->id)
            ->assertSet('detaljVisible', true)
            ->assertSee('A26-12RB-1K14851')
            ->assertSee('113735946')
            ->assertSee('A26-12RB-2D00992')
            ->assertSee('000000000000123')
            ->assertSee('(status uređaja)');
    }

    #[Test]
    public function dodaj_terminal_pravi_tid_i_dopunjuje_prazna_polja_lokacije(): void
    {
        $this->actingAs($this->korisnik(self::ADMIN));
        $uredjaj = $this->uredjaj('A26-12RB-1K14121', $this->trgovac->id, self::STATUS_INSTALIRAN);

        Livewire::test(DodajTerminal::class)
            ->dispatch('dodajTerminal')
            ->assertSet('modalVisible', true)
            ->set('acq_tid', ' pn005313 ')
            ->set('sn', 'A26-12RB-1K14121')
            ->assertSee('SAVSKI VENAC RENT')
            ->assertSee('Instaliran')
            ->set('komunikacija', 'GPRS')
            ->set('datum_instalacije', '2026-10-27')
            ->set('acq_mid', '999')
            ->set('mcc', '5812')
            ->set('ptt', '11000')
            ->call('sacuvaj')
            ->assertHasNoErrors()
            ->assertSet('modalVisible', false)
            ->assertDispatched('tidDodat');

        $acq = TerminalAcq::where('acq_tid', 'PN005313')->sole();
        $this->assertSame($uredjaj->terminalId, $acq->terminalId);
        $this->assertSame(TerminalAcq::IZVOR_RUCNO, $acq->izvor);
        $this->assertSame('2026-10-27', $acq->datum_instalacije->toDateString());
        $this->trgovac->refresh();
        $this->assertSame('000000000004388', $this->trgovac->acq_mid, 'postojeći MID se ne menja');
        $this->assertSame('5812', $this->trgovac->mcc);
        $this->assertSame('11000', $this->trgovac->ptt);
    }

    #[Test]
    public function dodaj_terminal_validacija(): void
    {
        $this->actingAs($this->korisnik(self::ADMIN));
        TerminalAcq::factory()->create(['acq_tid' => 'PN000001']);
        Terminal::create(['sn' => 'SN-BEZ-LOKACIJE', 'terminal_tipId' => 1]);
        $zauzet = $this->uredjaj('A26-12RB-ZAUZET1', $this->trgovac->id, self::STATUS_INSTALIRAN);
        TerminalAcq::factory()->saUredjajem($zauzet->terminalId)->create(['acq_tid' => 'PN000002']);

        $forma = Livewire::test(DodajTerminal::class)->dispatch('dodajTerminal');

        $forma->set('acq_tid', 'PN12')->set('sn', 'NEMA')->call('sacuvaj')
            ->assertHasErrors(['acq_tid' => 'regex', 'sn' => 'exists']);
        $forma->set('acq_tid', 'PN000001')->call('sacuvaj')->assertHasErrors(['acq_tid' => 'unique']);
        $forma->set('acq_tid', 'PN000009')->set('sn', 'SN-BEZ-LOKACIJE')->call('sacuvaj')->assertHasErrors('sn');
        $forma->set('sn', 'A26-12RB-ZAUZET1')->assertSee('već pod aktivnim TID-om PN000002')->call('sacuvaj')->assertHasErrors('sn');

        $this->assertFalse(TerminalAcq::where('acq_tid', 'PN000009')->exists());
    }

    #[Test]
    public function dodaj_terminal_upozorava_kad_je_uredjaj_u_magacinu(): void
    {
        $this->actingAs($this->korisnik(self::ADMIN));
        $magacin = $this->lokacija(['lokacija_tipId' => 2, 'l_naziv' => 'Magacin VML']);
        $this->uredjaj('A26-12RB-MAGACIN', $magacin->id, self::STATUS_MAGACIN);

        Livewire::test(DodajTerminal::class)
            ->dispatch('dodajTerminal')
            ->set('sn', 'A26-12RB-MAGACIN')
            ->assertSee('Uređaj je u magacinu ili servisnom centru');
    }

    /**
     * @param  list<int>  $pozicije
     */
    private function dodeliStranicu(string $routeName, array $pozicije): void
    {
        $stranica = Stranica::forceCreate(['naziv' => $routeName, 'route_name' => $routeName, 'menu_order' => 99, 'show_in_meni' => 0]);
        foreach ($pozicije as $pozicijaId) {
            PozicijaPrikazStranica::create(['pozicija_tipId' => $pozicijaId, 'stranicaId' => $stranica->id]);
        }
    }

    /**
     * @param  array<string, mixed>  $atributi
     */
    private function lokacija(array $atributi = []): Lokacija
    {
        return Lokacija::factory()->create($atributi + ['regionId' => $this->regionId, 'lokacija_tipId' => 3]);
    }

    private function uredjaj(string $sn, int $lokacijaId, int $statusId): TerminalLokacija
    {
        $terminal = Terminal::create(['sn' => $sn, 'terminal_tipId' => 1]);

        return TerminalLokacija::create([
            'terminalId' => $terminal->id,
            'lokacijaId' => $lokacijaId,
            'terminal_statusId' => $statusId,
            'korisnikId' => 1,
            'korisnikIme' => 'Test',
        ]);
    }

    private function korisnik(int $pozicijaId): User
    {
        return User::factory()->create(['pozicija_tipId' => $pozicijaId, 'lokacijaId' => $this->trgovac->id]);
    }
}
