<?php

namespace Tests\Feature\Terminali;

use App\Actions\TerminalAcq\DeinstalirajTerminalAcq;
use App\Actions\TerminalAcq\IzvrsiPrekonfiguraciju;
use App\Actions\TerminalAcq\OpozoviTerminalAcq;
use App\Actions\TerminalAcq\PremestiUredjajTerminalAcq;
use App\Http\Livewire\Terminal as TerminalStranica;
use App\Http\Livewire\TerminalAcq\AcqTerminali;
use App\Models\Lokacija;
use App\Models\LokacijaTip;
use App\Models\PozicijaPrikazStranica;
use App\Models\Region;
use App\Models\Stranica;
use App\Models\Terminal;
use App\Models\TerminalAcq;
use App\Models\TerminalAcqHistory;
use App\Models\TerminalLokacija;
use App\Models\TerminalLokacijaHistory;
use App\Models\TerminalStatusTip;
use App\Models\TerminalVendor;
use App\Models\TiketPrioritetTip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

/**
 * Faza 4.2: izvršenje prekonfiguracije, deinstalacija, opoziv, dodela uređaja i premeštanje
 * uređaja (TID ostaje) iz detalja TID-a, i zabrana premeštanja uređaja pod aktivnim TID-om
 * na stranici Terminali.
 */
class AcqAkcijeTest extends TestCase
{
    use RefreshDatabase;

    private const ADMIN = 1;

    private const STATUS_MAGACIN = 1;

    private const STATUS_INSTALIRAN = 2;

    private const STATUS_ZA_INSTALACIJU = 8;

    private int $regionId;

    private User $korisnik;

    private Lokacija $magacin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->regionId = Region::create(['r_naziv' => 'Beograd'])->id;
        foreach ([1 => 'Servisni centar', 2 => 'Magacin', 3 => 'Korisnik terminala'] as $id => $naziv) {
            LokacijaTip::forceCreate(['id' => $id, 'lt_naziv' => $naziv]);
        }
        TerminalStatusTip::forceCreate(['id' => self::STATUS_MAGACIN, 'ts_naziv' => 'Magacin']);
        TerminalStatusTip::forceCreate(['id' => self::STATUS_INSTALIRAN, 'ts_naziv' => 'Instaliran']);
        TerminalStatusTip::forceCreate(['id' => self::STATUS_ZA_INSTALACIJU, 'ts_naziv' => 'Za instalaciju']);
        $this->magacin = $this->lokacija(['lokacija_tipId' => 2, 'l_naziv' => 'Magacin VML', 'pib' => null]);
        $this->korisnik = User::factory()->create(['pozicija_tipId' => self::ADMIN, 'lokacijaId' => $this->magacin->id]);
        $this->actingAs($this->korisnik);

        $stranica = Stranica::forceCreate(['naziv' => 'ACQ terminali', 'route_name' => AcqTerminali::ROUTE_NAME, 'menu_order' => 39, 'show_in_meni' => 1]);
        PozicijaPrikazStranica::create(['pozicija_tipId' => self::ADMIN, 'stranicaId' => $stranica->id]);
    }

    #[Test]
    public function prekonfiguracija_druge_firme_premesta_uredjaj_istim_statusom_i_menja_tid(): void
    {
        [$stari, $uredjaj, $staraLokacija] = $this->aktivanTid('PN004096', ['pib' => '113735946', 'acq_mid' => '000000000004388']);
        $novaLokacija = $this->lokacija(['pib' => '115682682', 'acq_mid' => '000000000004845']);
        $novi = $this->cekaPrekonfiguraciju('PN005313', $stari, $novaLokacija);

        $obavestenja = (new IzvrsiPrekonfiguraciju)->izvrsi($novi, $this->korisnik);

        $this->assertSame([], $obavestenja);
        $uredjaj->refresh();
        $this->assertSame($novaLokacija->id, $uredjaj->lokacijaId);
        $this->assertSame(self::STATUS_INSTALIRAN, $uredjaj->terminal_statusId, 'status uređaja se ne menja');

        $stari->refresh();
        $this->assertNull($stari->terminalId);
        $this->assertSame(TerminalStatusTip::idPoNazivu(TerminalStatusTip::PREKONFIGURISAN), $stari->terminal_statusId);
        $this->assertSame($staraLokacija->id, $stari->lokacijaId);
        $this->assertSame('000000000004388', $stari->histories->sole()->acq_mid);

        $novi->refresh();
        $this->assertSame($uredjaj->terminalId, $novi->terminalId);
        $this->assertNull($novi->terminal_statusId);
        $this->assertNull($novi->lokacijaId);
        $this->assertSame(1, $novi->histories()->count());
        $this->assertSame(self::STATUS_INSTALIRAN, $novi->efektivniStatusId());
    }

    #[Test]
    public function prekonfiguracija_iste_firme_menja_mid_lokacije_bez_premestanja(): void
    {
        [$stari, $uredjaj, $lokacija] = $this->aktivanTid('PN004096', ['pib' => '113735946', 'acq_mid' => '000000000004388']);
        $novi = $this->cekaPrekonfiguraciju('PN005313', $stari, $lokacija, '000000000004845');
        $brojPremestanja = TerminalLokacijaHistory::count();

        $obavestenja = (new IzvrsiPrekonfiguraciju)->izvrsi($novi, $this->korisnik);

        $this->assertSame(['MID lokacije zamenjen: 000000000004388 → 000000000004845.'], $obavestenja);
        $this->assertSame('000000000004845', $lokacija->fresh()->acq_mid);
        $this->assertNull($novi->fresh()->zahtev_acq_mid);
        $this->assertSame($brojPremestanja, TerminalLokacijaHistory::count(), 'uređaj se ne premešta');
        $this->assertSame('000000000004388', $stari->fresh()->histories->sole()->acq_mid, 'stari MID ostaje u istoriji');
    }

    #[Test]
    public function prekonfiguracija_odbija_tid_koji_ne_ceka_i_stari_bez_uredjaja(): void
    {
        [$aktivan] = $this->aktivanTid('PN000001');
        $lokacija = $this->lokacija();
        $zatvoren = TerminalAcq::factory()->bezUredjaja(TerminalStatusTip::idPoNazivu(TerminalStatusTip::DEINSTALIRAN), $lokacija->id)->create(['acq_tid' => 'PN000002']);
        $ceka = $this->cekaPrekonfiguraciju('PN000003', $zatvoren, $lokacija);

        try {
            (new IzvrsiPrekonfiguraciju)->izvrsi($aktivan, $this->korisnik);
            $this->fail('Očekivan izuzetak.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('ne čeka prekonfiguraciju', $e->getMessage());
        }

        $this->expectExceptionMessage('nema uređaj');
        (new IzvrsiPrekonfiguraciju)->izvrsi($ceka, $this->korisnik);
    }

    #[Test]
    public function deinstalacija_zatvara_tid_i_premesta_uredjaj(): void
    {
        [$acq, $uredjaj, $lokacija] = $this->aktivanTid('PN004096');

        (new DeinstalirajTerminalAcq)->izvrsi($acq, $this->magacin->id, self::STATUS_MAGACIN, $this->korisnik);

        $acq->refresh();
        $this->assertNull($acq->terminalId);
        $this->assertSame(TerminalStatusTip::idPoNazivu(TerminalStatusTip::DEINSTALIRAN), $acq->terminal_statusId);
        $this->assertSame($lokacija->id, $acq->lokacijaId, 'TID pamti poslednju lokaciju uređaja');
        $this->assertSame(self::STATUS_INSTALIRAN, $acq->histories->sole()->terminal_statusId);

        $uredjaj->refresh();
        $this->assertSame($this->magacin->id, $uredjaj->lokacijaId);
        $this->assertSame(self::STATUS_MAGACIN, $uredjaj->terminal_statusId);
        $this->assertSame(1, TerminalLokacijaHistory::where('terminal_lokacijaId', $uredjaj->id)->count());
    }

    #[Test]
    public function deinstalacija_odbija_lokaciju_trgovca_status_samo_za_tid_i_tid_bez_uredjaja(): void
    {
        [$acq, , $lokacija] = $this->aktivanTid('PN004096');
        $akcija = new DeinstalirajTerminalAcq;

        foreach ([
            [$lokacija->id, self::STATUS_MAGACIN, 'servisni centar ili magacin'],
            [$this->magacin->id, TerminalStatusTip::idPoNazivu(TerminalStatusTip::DEINSTALIRAN), 'status uređaja'],
        ] as [$lokacijaId, $statusId, $poruka]) {
            try {
                $akcija->izvrsi($acq, $lokacijaId, $statusId, $this->korisnik);
                $this->fail('Očekivan izuzetak.');
            } catch (RuntimeException $e) {
                $this->assertStringContainsString($poruka, $e->getMessage());
            }
        }
        $this->assertNotNull($acq->fresh()->terminalId);

        $bezUredjaja = TerminalAcq::factory()->bezUredjaja(self::STATUS_ZA_INSTALACIJU, $lokacija->id)->create(['acq_tid' => 'PN000009']);
        $this->expectExceptionMessage('nema uređaj');
        $akcija->izvrsi($bezUredjaja, $this->magacin->id, self::STATUS_MAGACIN, $this->korisnik);
    }

    #[Test]
    public function opoziv_tid_a_koji_ceka(): void
    {
        [$stari] = $this->aktivanTid('PN004096');
        $lokacija = $this->lokacija();
        $cekaUredjaj = TerminalAcq::factory()->bezUredjaja(self::STATUS_ZA_INSTALACIJU, $lokacija->id)->create(['acq_tid' => 'PN005306']);
        $cekaPrekonfiguraciju = $this->cekaPrekonfiguraciju('PN005313', $stari, $lokacija);
        $akcija = new OpozoviTerminalAcq;

        $akcija->izvrsi($cekaUredjaj, $this->korisnik);
        $akcija->izvrsi($cekaPrekonfiguraciju, $this->korisnik);

        $opozvan = TerminalStatusTip::idPoNazivu(TerminalStatusTip::OPOZVAN);
        $this->assertSame($opozvan, $cekaUredjaj->fresh()->terminal_statusId);
        $this->assertSame($lokacija->id, $cekaUredjaj->fresh()->lokacijaId);
        $this->assertSame(1, $cekaUredjaj->histories()->count());
        $this->assertSame($opozvan, $cekaPrekonfiguraciju->fresh()->terminal_statusId);
        $this->assertNotNull($stari->fresh()->terminalId, 'stari TID ostaje aktivan');

        $this->expectExceptionMessage('samo dok čeka');
        $akcija->izvrsi($stari, $this->korisnik);
    }

    #[Test]
    public function akcije_iz_detalja_tid_a(): void
    {
        $lokacija = $this->lokacija();
        $slobodan = $this->uredjaj('A26-12RB-SLOBODAN', $this->magacin->id, self::STATUS_MAGACIN);
        $ceka = TerminalAcq::factory()->bezUredjaja(self::STATUS_ZA_INSTALACIJU, $lokacija->id)->create(['acq_tid' => 'PN005306']);

        Livewire::test(AcqTerminali::class)
            ->call('prikaziDetalj', $ceka->id)
            ->assertSee('Dodeli uređaj')
            ->call('dodeliUredjaj')
            ->assertSet('greskaAkcije', 'Unesite SN uređaja.')
            ->set('dodelaSn', 'A26-12RB-SLOBODAN')
            ->call('dodeliUredjaj')
            ->assertSet('greskaAkcije', null)
            ->assertSet('porukaAkcije', 'Uređaj je dodeljen TID-u.')
            ->assertSee('Deinstaliraj')
            ->set('deinstalacijaLokacijaId', $this->magacin->id)
            ->set('deinstalacijaStatusId', self::STATUS_MAGACIN)
            ->call('deinstaliraj')
            ->assertSet('porukaAkcije', 'TID je deinstaliran, a uređaj premešten.')
            ->assertSee('TID je zatvoren; nema akcija.');

        $this->assertSame(TerminalStatusTip::idPoNazivu(TerminalStatusTip::DEINSTALIRAN), $ceka->fresh()->terminal_statusId);
        $this->assertSame($this->magacin->id, $slobodan->fresh()->lokacijaId);
    }

    #[Test]
    public function premestanje_uredjaja_tid_ostaje_isti_uz_istoriju_oba(): void
    {
        [$acq, $uredjaj, $staraLokacija] = $this->aktivanTid('PN004096', ['pib' => '113735946', 'acq_mid' => '000000000004388']);
        $nova = $this->lokacija(['l_naziv' => 'Savski venac rent 2', 'pib' => '113735946', 'acq_mid' => '000000000004388']);

        Livewire::test(AcqTerminali::class)
            ->call('prikaziDetalj', $acq->id)
            ->assertSee('Premesti uređaj (TID ostaje)')
            ->assertSet('premestanjeStatusId', self::STATUS_INSTALIRAN)
            ->set('premestanjeTrazi', 'venac rent 2')
            ->assertSee('Savski venac rent 2')
            ->call('izaberiLokacijuPremestanja', $nova->id)
            ->assertSet('premestanjeLokacijaId', $nova->id)
            ->assertDontSee('drugi PIB')
            ->call('premestiUredjaj')
            ->assertSet('greskaAkcije', null)
            ->assertSet('porukaAkcije', 'Uređaj je premešten, a TID PN004096 ostaje isti.')
            ->assertSet('premestanjeLokacijaId', null);

        $acq->refresh();
        $this->assertSame($uredjaj->terminalId, $acq->terminalId);
        $this->assertSame($nova->id, $acq->efektivnaLokacijaId());
        $this->assertSame($staraLokacija->id, TerminalAcqHistory::where('terminal_acqId', $acq->id)->sole()->lokacijaId);

        $uredjaj->refresh();
        $this->assertSame($nova->id, $uredjaj->lokacijaId);
        $this->assertSame(self::STATUS_INSTALIRAN, $uredjaj->terminal_statusId);
        $this->assertSame($this->korisnik->id, $uredjaj->korisnikId);
        $this->assertSame($staraLokacija->id, TerminalLokacijaHistory::where('terminal_lokacijaId', $uredjaj->id)->sole()->lokacijaId);
    }

    #[Test]
    public function premestanje_na_drugu_firmu_upozorava_ali_dozvoljava(): void
    {
        [$acq, $uredjaj] = $this->aktivanTid('PN004096', ['pib' => '113735946', 'acq_mid' => '000000000004388']);
        $drugaFirma = $this->lokacija(['pib' => '115682682', 'acq_mid' => '000000000004845']);

        Livewire::test(AcqTerminali::class)
            ->call('prikaziDetalj', $acq->id)
            ->call('izaberiLokacijuPremestanja', $drugaFirma->id)
            ->assertSee('Ciljna lokacija ima drugi PIB (113735946 → 115682682)')
            ->assertSee('Ciljna lokacija ima drugi MID')
            ->set('premestanjeStatusId', self::STATUS_ZA_INSTALACIJU)
            ->call('premestiUredjaj')
            ->assertSet('greskaAkcije', null);

        $this->assertSame($drugaFirma->id, $uredjaj->fresh()->lokacijaId);
        $this->assertSame(self::STATUS_ZA_INSTALACIJU, $uredjaj->fresh()->terminal_statusId);
    }

    #[Test]
    public function premestanje_odbija_magacin_istu_lokaciju_status_samo_za_tid_i_tid_bez_uredjaja(): void
    {
        [$acq, $uredjaj, $lokacija] = $this->aktivanTid('PN004096');
        $druga = $this->lokacija();
        $akcija = new PremestiUredjajTerminalAcq;

        foreach ([
            [$this->magacin->id, self::STATUS_INSTALIRAN, 'lokaciju korisnika terminala'],
            [$lokacija->id, self::STATUS_INSTALIRAN, 'već na toj lokaciji'],
            [$druga->id, TerminalStatusTip::idPoNazivu(TerminalStatusTip::DEINSTALIRAN), 'status uređaja'],
        ] as [$lokacijaId, $statusId, $poruka]) {
            try {
                $akcija->izvrsi($acq, $lokacijaId, $statusId, $this->korisnik);
                $this->fail('Očekivan izuzetak.');
            } catch (RuntimeException $e) {
                $this->assertStringContainsString($poruka, $e->getMessage());
            }
        }
        $this->assertSame($lokacija->id, $uredjaj->fresh()->lokacijaId);
        $this->assertSame(0, TerminalLokacijaHistory::count());

        Livewire::test(AcqTerminali::class)
            ->call('prikaziDetalj', $acq->id)
            ->set('premestanjeLokacijaId', $this->magacin->id)
            ->call('premestiUredjaj')
            ->assertSet('greskaAkcije', 'Izaberite lokaciju korisnika terminala.');

        $bezUredjaja = TerminalAcq::factory()->bezUredjaja(self::STATUS_ZA_INSTALACIJU, $lokacija->id)->create(['acq_tid' => 'PN000009']);
        $this->expectExceptionMessage('nema uređaj');
        $akcija->izvrsi($bezUredjaja, $druga->id, self::STATUS_INSTALIRAN, $this->korisnik);
    }

    #[Test]
    public function stranica_terminali_ne_premesta_uredjaj_pod_aktivnim_tid_om(): void
    {
        TiketPrioritetTip::forceCreate(['id' => 4, 'tp_naziv' => 'Normalan', 'btn_collor' => 'green-600', 'btn_hover_collor' => 'green-700', 'tr_bg_collor' => 'green-50']);
        TerminalVendor::forceCreate(['name' => 'HALK']);
        [, $podTidom] = $this->aktivanTid('PN004096');
        $slobodan = $this->uredjaj('A26-12RB-SLOBODAN', $this->magacin->id, self::STATUS_MAGACIN);

        Livewire::test(TerminalStranica::class)
            ->call('premestiShowModal', $podTidom->id, self::STATUS_INSTALIRAN)
            ->assertSet('licencaError', 'acq')
            ->assertSet('acqTidovi', ['PN004096'])
            ->assertSet('modalConfirmPremestiVisible', false)
            ->assertSee('pod aktivnim ACQ TID-om (PN004096)')
            ->call('premestiShowModal', $slobodan->id, self::STATUS_MAGACIN)
            ->assertSet('licencaError', '')
            ->assertSet('modalConfirmPremestiVisible', true);

        Livewire::test(TerminalStranica::class)
            ->set('selectedTerminals', [(string) $slobodan->id, (string) $podTidom->id])
            ->set('multiSelected', true)
            ->set('plokacija', $this->magacin->id)
            ->set('modalStatusPremesti', self::STATUS_MAGACIN)
            ->call('moveTerminal')
            ->assertSet('licencaError', 'acq');

        $this->assertNotSame($this->magacin->id, $podTidom->fresh()->lokacijaId);
        $this->assertSame(0, TerminalLokacijaHistory::count(), 'ništa nije premešteno');
    }

    #[Test]
    public function stranica_terminali_boji_red_uredjaja_pod_tid_om(): void
    {
        TiketPrioritetTip::forceCreate(['id' => 4, 'tp_naziv' => 'Normalan', 'btn_collor' => 'green-600', 'btn_hover_collor' => 'green-700', 'tr_bg_collor' => 'green-50']);
        TerminalVendor::forceCreate(['name' => 'HALK']);
        [, $prvi] = $this->aktivanTid('PN004096');
        [, $drugi] = $this->aktivanTid('PN004097');
        $slobodan = $this->uredjaj('A26-12RB-SLOBODAN', $this->magacin->id, self::STATUS_MAGACIN);

        $html = Livewire::test(TerminalStranica::class)->html();

        preg_match_all('/wire:key="terminal-(\d+)"\s+class="(bg-sky-(?:50|100))"\s+title="ACQ TID (PN\d{6})"/', $html, $pogodci);
        $this->assertEqualsCanonicalizing([(string) $prvi->id, (string) $drugi->id], $pogodci[1]);
        $this->assertEqualsCanonicalizing(['bg-sky-50', 'bg-sky-100'], $pogodci[2], 'parni i neparni redovi sa TID-om imaju različite nijanse');
        $this->assertEqualsCanonicalizing(['PN004096', 'PN004097'], $pogodci[3]);
        $this->assertDoesNotMatchRegularExpression('/wire:key="terminal-'.$slobodan->id.'"\s+class="bg-sky/', $html);
    }

    /**
     * @param  array<string, mixed>  $lokacija
     * @return array{0: TerminalAcq, 1: TerminalLokacija, 2: Lokacija}
     */
    private function aktivanTid(string $tid, array $lokacija = []): array
    {
        $trgovac = $this->lokacija($lokacija);
        $uredjaj = $this->uredjaj('SN-'.$tid, $trgovac->id, self::STATUS_INSTALIRAN);

        return [TerminalAcq::factory()->saUredjajem($uredjaj->terminalId)->create(['acq_tid' => $tid]), $uredjaj, $trgovac];
    }

    private function cekaPrekonfiguraciju(string $tid, TerminalAcq $stari, Lokacija $lokacija, ?string $zahtevMid = null): TerminalAcq
    {
        return TerminalAcq::factory()
            ->bezUredjaja(TerminalStatusTip::idPoNazivu(TerminalStatusTip::CEKA_PREKONFIGURACIJU), $lokacija->id)
            ->create(['acq_tid' => $tid, 'prethodni_terminal_acqId' => $stari->id, 'zahtev_acq_mid' => $zahtevMid]);
    }

    /**
     * @param  array<string, mixed>  $atributi
     */
    private function lokacija(array $atributi = []): Lokacija
    {
        return Lokacija::factory()->create($atributi + ['regionId' => $this->regionId, 'lokacija_tipId' => 3, 'distributerId' => null]);
    }

    private function uredjaj(string $sn, int $lokacijaId, int $statusId): TerminalLokacija
    {
        $terminal = Terminal::create(['sn' => $sn, 'terminal_tipId' => 1, 'vendor_id' => null]);

        return TerminalLokacija::create([
            'terminalId' => $terminal->id,
            'lokacijaId' => $lokacijaId,
            'terminal_statusId' => $statusId,
            'korisnikId' => 1,
            'korisnikIme' => 'Test',
        ]);
    }
}
