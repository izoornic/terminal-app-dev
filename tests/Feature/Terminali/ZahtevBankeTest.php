<?php

namespace Tests\Feature\Terminali;

use App\Actions\TerminalAcq\DodeliUredjajTerminalAcq;
use App\Actions\TerminalAcq\ProcitajZahtevBanke;
use App\Actions\TerminalAcq\UpisiZahtevBanke;
use App\Models\Lokacija;
use App\Models\LokacijaKontaktOsoba;
use App\Models\Region;
use App\Models\Terminal;
use App\Models\TerminalAcq;
use App\Models\TerminalLokacija;
use App\Models\TerminalStatusTip;
use App\Models\TerminalTip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

/**
 * HALK Faza 3b: zahtevi banke za instalaciju i prekonfiguraciju (format kao u primerima:
 * kolona A prazna, "Model terminala", bez SN-a; stari TID u "dodatnim opcijama").
 */
class ZahtevBankeTest extends TestCase
{
    use RefreshDatabase;

    private const STATUS_MAGACIN = 1;

    private const STATUS_INSTALIRAN = 2;

    private const STATUS_ZA_INSTALACIJU = 8;

    private const ZAGLAVLJE = [null, 'ACQ Terminal ID', 'ACQ Merchant ID', 'Naziv Trgovca', 'Naziv lokacije - na slipu', 'Adresa lokacije', 'Mesto lokacije', 'PTT', 'MID/šifra trgovca', 'MCC', 'PIB', 'Telefon', 'Kontakt osoba', 'Model terminala', 'Komunikacija', 'dodatne opcije, (npr. povezivanje sa kasom, preauth, CNP, rate)'];

    private int $beograd;

    private int $nis;

    private User $korisnik;

    private Lokacija $magacin;

    private string $privremeniFolder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->beograd = Region::create(['r_naziv' => 'Beograd'])->id;
        $this->nis = Region::create(['r_naziv' => 'Niš'])->id;
        TerminalStatusTip::forceCreate(['id' => self::STATUS_MAGACIN, 'ts_naziv' => 'Magacin']);
        TerminalStatusTip::forceCreate(['id' => self::STATUS_INSTALIRAN, 'ts_naziv' => 'Instaliran']);
        TerminalStatusTip::forceCreate(['id' => self::STATUS_ZA_INSTALACIJU, 'ts_naziv' => 'Za instalaciju']);
        TerminalTip::forceCreate(['id' => 1, 'model' => 'CS10', 'proizvodjac' => 'Ciontek']);
        TerminalTip::forceCreate(['id' => 2, 'model' => 'Z100', 'proizvodjac' => 'ZCS']);
        $this->magacin = Lokacija::factory()->create(['regionId' => $this->beograd, 'lokacija_tipId' => 2, 'pib' => null]);
        $this->korisnik = User::factory()->create(['pozicija_tipId' => 1, 'lokacijaId' => $this->magacin->id]);
        $this->actingAs($this->korisnik);

        $this->privremeniFolder = sys_get_temp_dir().'/halk_zahtev_'.uniqid();
        mkdir($this->privremeniFolder);
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->privremeniFolder.'/*') ?: []);
        rmdir($this->privremeniFolder);

        parent::tearDown();
    }

    #[Test]
    public function zahtev_za_instalaciju_novih_trgovaca(): void
    {
        TerminalAcq::factory()->create(['acq_tid' => 'PN005311']);

        $zahtev = $this->procitaj('ANDROID INSTALACIJA Ciontek_13.10.2026.xlsx', [
            $this->red(['PN005306', '000000000004839', 'GRIZZLY CAFFE', 'GRIZZLY CAFFE', 'Kralja Petra I 33', 'Smederevo', 11300, '000000000004839', 5813, 115762060, '061 65 484 48 48', 'Blagoje Milenković']),
            $this->red(['PN005309', '000000000004842', 'STUDIO ATELJE HJERA', 'PR STUDIO ATELJE HJERA ', 'KOSMAJSKA 60A', 'BEOGRAD', 11032, '000000000004842', 7230, 110565060, '063 7245 373', 'TIJANA KOVAČEVIĆ']),
            $this->red(['PN005310', '000000000004842', 'STUDIO ATELJE HJERA', 'PR STUDIO ATELJE HJERA 1', 'KNEZA VIŠESLAVA 63', 'BEOGRAD', 11140, '000000000004842', 7230, 110565060, '063 7245 373', 'TIJANA KOVAČEVIĆ']),
            $this->red(['PN005309', '000000000004843', 'DUPLIKAT', null, 'Adresa 1', 'Ivanjica', 32250, null, 4816, 100930441, null, null]),
            $this->red(['PN005311', '000000000004843', 'MAKICA DOO', 'MAKICA DOO', '13 SEPTEMBRA 53', 'IVANJICA ', 32250, '000000000004843', 4816, 100930441, '063 468 443', 'BANE TANKOSIĆ']),
        ]);

        $this->assertSame('2026-10-13', $zahtev['datum_zahteva']);
        [$grizzly, $hjera, $hjera1, $duplikat, $makica] = $zahtev['redovi'];

        $this->assertSame(ProcitajZahtevBanke::INSTALACIJA, $grizzly['vrsta']);
        $this->assertSame(ProcitajZahtevBanke::LOKACIJA_NOVA, $grizzly['lokacija_akcija']);
        $this->assertSame(1, $grizzly['trazeni_terminal_tipId']);
        $this->assertSame('+38161 65 484 48 48', $grizzly['telefon']);
        $this->assertTrue($this->sadrzi($grizzly['upozorenja'], 'neočekivan broj cifara'));
        $this->assertTrue($grizzly['izabran']);

        $this->assertSame('PR STUDIO ATELJE HJERA', $hjera['naziv_na_slipu']);
        $this->assertSame([], $hjera['upozorenja']);
        $this->assertTrue($this->sadrzi($hjera1['upozorenja'], 'podlokacija'));

        $this->assertTrue($this->sadrzi($duplikat['greske'], 'već u redu'));
        $this->assertFalse($duplikat['izabran']);
        $this->assertTrue($this->sadrzi($makica['greske'], 'već postoji'));
    }

    #[Test]
    public function upis_instalacije_pravi_lokacije_podlokaciju_kontakt_i_tid_koji_ceka_uredjaj(): void
    {
        $zahtev = $this->procitaj('zahtev_13.10.2026.xlsx', [
            $this->red(['PN005309', '000000000004842', 'STUDIO ATELJE HJERA', 'PR STUDIO ATELJE HJERA', 'KOSMAJSKA 60A', 'BEOGRAD', 11032, null, 7230, 110565060, '063 7245 373', 'TIJANA KOVAČEVIĆ']),
            $this->red(['PN005310', '000000000004842', 'STUDIO ATELJE HJERA', 'PR STUDIO ATELJE HJERA 1', 'KNEZA VIŠESLAVA 63', 'BEOGRAD', 11140, null, 7230, 110565060, '063 7245 373', 'TIJANA KOVAČEVIĆ']),
            $this->red(['PN005312', '000000000004842', 'STUDIO ATELJE HJERA', 'PR STUDIO ATELJE HJERA 2', 'Kosmajska 60a', 'BEOGRAD', 11032, null, 7230, 110565060, null, null]),
        ]);

        $rezultat = $this->upisi($zahtev, [2 => [], 3 => [], 4 => []], $this->beograd);

        $this->assertSame(['tidova' => 3, 'lokacija' => 2, 'kontakata' => 2, 'upozorenja' => []], $rezultat, 'i podlokacija dobija kontakt iz svog reda');
        $lokacije = Lokacija::where('pib', '110565060')->orderBy('id')->get();
        $this->assertSame([0, 1], $lokacije->pluck('is_duplicate')->all(), 'druga adresa je podlokacija');
        $this->assertSame('000000000004842', $lokacije[0]->acq_mid);
        $this->assertSame('7230', $lokacije[0]->mcc);
        $this->assertSame($this->beograd, $lokacije[0]->regionId);

        $acq = TerminalAcq::where('acq_tid', 'PN005309')->sole();
        $this->assertNull($acq->terminalId);
        $this->assertSame(self::STATUS_ZA_INSTALACIJU, $acq->terminal_statusId);
        $this->assertSame($lokacije[0]->id, $acq->lokacijaId);
        $this->assertSame(TerminalAcq::IZVOR_ZAHTEV, $acq->izvor);
        $this->assertSame('zahtev_13.10.2026.xlsx', $acq->izvor_fajl);
        $this->assertSame('2026-10-13', $acq->datum_zahteva->toDateString());
        $this->assertSame(1, $acq->trazeni_terminal_tipId);
        $this->assertSame($lokacije[0]->id, TerminalAcq::where('acq_tid', 'PN005312')->sole()->lokacijaId, 'ista adresa koristi istu novu lokaciju');
        $this->assertSame('+38163 7245 373', LokacijaKontaktOsoba::where('lokacijaId', $lokacije[0]->id)->sole()->tel);
    }

    #[Test]
    public function postojeca_lokacija_dobija_samo_prazna_polja(): void
    {
        $lokacija = $this->lokacijaKorisnika(['pib' => '115762060', 'adresa' => 'Kralja Petra I 33', 'acq_mid' => '000000000000001', 'mcc' => null]);
        LokacijaKontaktOsoba::create(['lokacijaId' => $lokacija->id, 'name' => 'Postojeći', 'tel' => '+381111']);

        $zahtev = $this->procitaj('zahtev.xlsx', [
            $this->red(['PN005306', '000000000004839', 'GRIZZLY CAFFE', 'GRIZZLY CAFFE', 'Kralja Petra I 33', 'Smederevo', 11300, null, 5813, 115762060, '061 654 8448', 'Blagoje']),
        ]);
        $red = $zahtev['redovi'][0];

        $this->assertSame(ProcitajZahtevBanke::LOKACIJA_POSTOJECA, $red['lokacija_akcija']);
        $this->assertSame($lokacija->id, $red['lokacijaId']);
        $this->assertTrue($this->sadrzi($red['upozorenja'], 'ne menja se'));

        $this->upisi($zahtev, [2 => []]);

        $lokacija->refresh();
        $this->assertSame('000000000000001', $lokacija->acq_mid);
        $this->assertSame('5813', $lokacija->mcc);
        $this->assertSame(1, LokacijaKontaktOsoba::where('lokacijaId', $lokacija->id)->count());
        $this->assertSame(1, Lokacija::where('pib', '115762060')->count());
    }

    #[Test]
    public function sn_u_pregledu_odmah_dodeljuje_uredjaj_i_premesta_ga_na_lokaciju(): void
    {
        $terminalLokacija = $this->uredjaj('A26-12RB-1K99999', $this->magacin->id, self::STATUS_MAGACIN, 2);
        $zahtev = $this->procitaj('zahtev.xlsx', [
            $this->red(['PN005306', '000000000004839', 'GRIZZLY CAFFE', 'GRIZZLY CAFFE', 'Kralja Petra I 33', 'Smederevo', 11300, null, 5813, 115762060, null, null]),
        ]);

        $rezultat = $this->upisi($zahtev, [2 => ['sn' => ' A26-12RB-1K99999 ']], $this->beograd);

        $acq = TerminalAcq::where('acq_tid', 'PN005306')->sole();
        $terminalLokacija->refresh();
        $this->assertSame($terminalLokacija->terminalId, $acq->terminalId);
        $this->assertNull($acq->terminal_statusId);
        $this->assertNull($acq->lokacijaId);
        $this->assertSame(Lokacija::where('pib', '115762060')->sole()->id, $terminalLokacija->lokacijaId);
        $this->assertSame(self::STATUS_ZA_INSTALACIJU, $terminalLokacija->terminal_statusId);
        $this->assertSame(1, $acq->histories()->count(), 'stanje pre dodele (čekao uređaj)');
        $this->assertTrue($this->sadrzi($rezultat['upozorenja'], 'nije model iz zahteva'));
    }

    #[Test]
    public function greska_u_jednom_redu_ponistava_ceo_upis(): void
    {
        $zahtev = $this->procitaj('zahtev.xlsx', [
            $this->red(['PN005306', '000000000004839', 'GRIZZLY CAFFE', 'GRIZZLY CAFFE', 'Kralja Petra I 33', 'Smederevo', 11300, null, 5813, 115762060, null, null]),
            $this->red(['PN005307', '000000000004840', 'DINER 020', 'DINER 020', 'Stevana Nemanje 116', 'Novi Pazar', 36300, null, 5812, 115934331, null, null]),
        ]);

        try {
            $this->upisi($zahtev, [2 => [], 3 => ['sn' => 'NEMA-TAKVOG-SN']], $this->beograd);
            $this->fail('Očekivan izuzetak.');
        } catch (RuntimeException $e) {
            $this->assertStringStartsWith('Red 3 (PN005307):', $e->getMessage());
        }

        $this->assertSame(0, TerminalAcq::count());
        $this->assertSame(0, Lokacija::where('lokacija_tipId', 3)->count());
    }

    #[Test]
    public function neizabran_red_se_ne_upisuje_a_nova_lokacija_trazi_region(): void
    {
        $zahtev = $this->procitaj('zahtev.xlsx', [
            $this->red(['PN005306', '000000000004839', 'GRIZZLY CAFFE', 'GRIZZLY CAFFE', 'Kralja Petra I 33', 'Smederevo', 11300, null, 5813, 115762060, null, null]),
            $this->red(['PN005307', '000000000004840', 'DINER 020', 'DINER 020', 'Stevana Nemanje 116', 'Novi Pazar', 36300, null, 5812, 115934331, null, null]),
        ]);

        $this->assertSame(1, $this->upisi($zahtev, [2 => ['izabran' => false], 3 => []], $this->nis)['tidova']);
        $this->assertFalse(TerminalAcq::where('acq_tid', 'PN005306')->exists());

        $this->expectExceptionMessage('Izaberite region');
        $this->upisi($this->procitaj('zahtev.xlsx', [
            $this->red(['PN005399', '000000000004899', 'BEZ REGIONA', null, 'Ulica 1', 'Beograd', 11000, null, 5812, 100930441, null, null]),
        ]), [2 => []]);
    }

    #[Test]
    public function prekonfiguracija_druge_firme_pravi_tid_koji_ceka_tehnicara(): void
    {
        $staraLokacija = $this->lokacijaKorisnika(['pib' => '113735946', 'regionId' => $this->nis, 'acq_mid' => '000000000004388', 'adresa' => 'Dr. Aleksandra Kostića 15A']);
        $terminalLokacija = $this->uredjaj('A26-12RB-1K14121', $staraLokacija->id, self::STATUS_INSTALIRAN, 1);
        $stari = TerminalAcq::factory()->saUredjajem($terminalLokacija->terminalId)->create(['acq_tid' => 'PN004096']);

        $zahtev = $this->procitaj('ANDROID prekonfiguracijaCiontek_PRE SUDA DOO.xlsx', [
            $this->red(['PN005313', '000000000004845', 'PRE SUDA DOO', 'PRE-SUDA DOO', 'Dr Aleksandra Kostića 15', 'Beograd', 11000, '000000000004845', 5812, 115682682, '066 500 000 2', 'Momilo Radonjić', 'Ciontek CS10', 'GPRS', 'prekonfiguracija PN004096']),
        ]);
        $red = $zahtev['redovi'][0];

        $this->assertSame(ProcitajZahtevBanke::PREKONFIGURACIJA, $red['vrsta']);
        $this->assertSame('PN004096', $red['stari_tid']);
        $this->assertStringContainsString('SN A26-12RB-1K14121', $red['stari_opis']);
        $this->assertSame(ProcitajZahtevBanke::LOKACIJA_NOVA, $red['lokacija_akcija']);
        $this->assertSame($this->nis, $red['regionId'], 'region se predlaže sa stare lokacije');
        $this->assertSame([], $red['greske']);

        $this->upisi($zahtev, [2 => []]);

        $novi = TerminalAcq::where('acq_tid', 'PN005313')->sole();
        $this->assertSame(TerminalStatusTip::idPoNazivu(TerminalStatusTip::CEKA_PREKONFIGURACIJU), $novi->terminal_statusId);
        $this->assertSame($stari->id, $novi->prethodni_terminal_acqId);
        $this->assertNull($novi->zahtev_acq_mid, 'nova lokacija odmah dobija MID');
        $this->assertSame('000000000004845', Lokacija::find($novi->lokacijaId)->acq_mid);
        $this->assertSame($terminalLokacija->terminalId, $stari->fresh()->terminalId, 'stari TID se ne menja do izvršenja');
        $this->assertSame($staraLokacija->id, $terminalLokacija->fresh()->lokacijaId, 'uređaj se ne premešta do izvršenja');
    }

    #[Test]
    public function prekonfiguracija_iste_firme_sa_novim_mid_om_ceka_u_zahtev_acq_mid(): void
    {
        $lokacija = $this->lokacijaKorisnika(['pib' => '113735946', 'acq_mid' => '000000000004388']);
        $terminalLokacija = $this->uredjaj('A26-12RB-1K14121', $lokacija->id, self::STATUS_INSTALIRAN, 1);
        TerminalAcq::factory()->saUredjajem($terminalLokacija->terminalId)->create(['acq_tid' => 'PN004096']);

        $zahtev = $this->procitaj('prekonfiguracija.xlsx', [
            $this->red(['PN005313', '000000000004845', 'SAVSKI VENAC RENT', 'SAVSKI VENAC RENT', 'Druga adresa 1', 'Beograd', 11000, null, 5812, 113735946, null, null, 'Ciontek CS10', 'GPRS', 'prekonfiguracija PN004096']),
        ]);
        $red = $zahtev['redovi'][0];

        $this->assertSame(ProcitajZahtevBanke::LOKACIJA_POSTOJECA, $red['lokacija_akcija']);
        $this->assertSame($lokacija->id, $red['lokacijaId']);
        $this->assertTrue($this->sadrzi($red['upozorenja'], 'biće zamenjen pri izvršenju'));

        $this->upisi($zahtev, [2 => []]);

        $this->assertSame('000000000004845', TerminalAcq::where('acq_tid', 'PN005313')->sole()->zahtev_acq_mid);
        $this->assertSame('000000000004388', $lokacija->fresh()->acq_mid, 'MID se menja tek pri izvršenju');
    }

    #[Test]
    public function prekonfiguracija_nepostojeceg_zatvorenog_ili_vec_trazenog_starog_tid_a(): void
    {
        $lokacija = $this->lokacijaKorisnika(['pib' => '113735946']);
        TerminalAcq::factory()->bezUredjaja(TerminalStatusTip::idPoNazivu(TerminalStatusTip::DEINSTALIRAN), $lokacija->id)->create(['acq_tid' => 'PN000002']);
        $terminalLokacija = $this->uredjaj('A26-12RB-1K00003', $lokacija->id, self::STATUS_INSTALIRAN, 1);
        $aktivan = TerminalAcq::factory()->saUredjajem($terminalLokacija->terminalId)->create(['acq_tid' => 'PN000003']);
        TerminalAcq::factory()->create(['acq_tid' => 'PN000033', 'prethodni_terminal_acqId' => $aktivan->id]);

        $redovi = $this->procitaj('prekonfiguracija.xlsx', [
            $this->red(['PN005401', '000000000000001', 'A', null, 'Ulica 1', 'Beograd', 11000, null, 5812, 113735946, null, null, null, null, 'prekonfiguracija PN000001']),
            $this->red(['PN005402', '000000000000001', 'B', null, 'Ulica 1', 'Beograd', 11000, null, 5812, 113735946, null, null, null, null, 'prekonfiguracija PN000002']),
            $this->red(['PN005403', '000000000000001', 'C', null, 'Ulica 1', 'Beograd', 11000, null, 5812, 113735946, null, null, null, null, 'prekonfiguracija PN000003']),
            $this->red(['PN005404', '000000000000001', 'D', null, 'Ulica 1', 'Beograd', 11000, null, 5812, 113735946, null, null, null, null, 'nova kasa']),
        ])['redovi'];

        $this->assertTrue($this->sadrzi($redovi[0]['greske'], 'ne postoji'));
        $this->assertTrue($this->sadrzi($redovi[1]['greske'], 'nema uređaj'));
        $this->assertTrue($this->sadrzi($redovi[2]['greske'], 'već zatražena (PN000033)'));
        $this->assertSame(ProcitajZahtevBanke::INSTALACIJA, $redovi[3]['vrsta']);
        $this->assertTrue($this->sadrzi($redovi[3]['upozorenja'], 'uvozi se kao instalacija'));
    }

    #[Test]
    public function dodela_uredjaja_odbija_tid_koji_ne_ceka_i_zauzet_uredjaj(): void
    {
        $lokacija = $this->lokacijaKorisnika();
        $zauzet = $this->uredjaj('A26-12RB-ZAUZET1', $lokacija->id, self::STATUS_INSTALIRAN, 1);
        TerminalAcq::factory()->saUredjajem($zauzet->terminalId)->create(['acq_tid' => 'PN000010']);
        $ceka = TerminalAcq::factory()->bezUredjaja(self::STATUS_ZA_INSTALACIJU, $lokacija->id)->create(['acq_tid' => 'PN000011']);
        $zatvoren = TerminalAcq::factory()->bezUredjaja(TerminalStatusTip::idPoNazivu(TerminalStatusTip::DEINSTALIRAN), $lokacija->id)->create(['acq_tid' => 'PN000012']);
        $akcija = new DodeliUredjajTerminalAcq;

        try {
            $akcija->izvrsi($ceka, 'A26-12RB-ZAUZET1', $this->korisnik);
            $this->fail('Očekivan izuzetak za zauzet uređaj.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('PN000010', $e->getMessage());
        }

        $this->expectExceptionMessage('ne čeka instalaciju');
        $akcija->izvrsi($zatvoren, 'A26-12RB-ZAUZET1', $this->korisnik);
    }

    /**
     * @param  list<list<mixed>>  $redovi
     * @return array{datum_zahteva: string, redovi: list<array<string, mixed>>}
     */
    private function procitaj(string $nazivFajla, array $redovi): array
    {
        $knjiga = new Spreadsheet;
        $knjiga->getActiveSheet()->fromArray([self::ZAGLAVLJE, ...$redovi], null, 'A1', true);
        $putanja = $this->privremeniFolder.'/'.uniqid().'.xlsx';
        (new Xlsx($knjiga))->save($putanja);

        return app(ProcitajZahtevBanke::class)->procitaj($putanja, $nazivFajla);
    }

    /**
     * @param  array{datum_zahteva: string, redovi: list<array<string, mixed>>}  $zahtev
     * @param  array<int, array<string, mixed>>  $izbori
     * @return array{tidova: int, lokacija: int, kontakata: int, upozorenja: list<string>}
     */
    private function upisi(array $zahtev, array $izbori, ?int $regionId = null): array
    {
        foreach ($zahtev['redovi'] as $red) {
            $izbori[$red['red']] = ($izbori[$red['red']] ?? []) + ['izabran' => $red['izabran'], 'regionId' => $regionId ?? $red['regionId']];
        }

        return app(UpisiZahtevBanke::class)->upisi($zahtev['redovi'], $izbori, $zahtev['datum_zahteva'], 'zahtev_13.10.2026.xlsx', $this->korisnik);
    }

    /**
     * Red zahteva od kolone B; podrazumevano Ciontek CS10, GPRS, bez dodatnih opcija.
     *
     * @param  list<mixed>  $vrednosti
     * @return list<mixed>
     */
    private function red(array $vrednosti): array
    {
        return [null, ...array_pad(array_slice($vrednosti, 0, 12), 12, null), $vrednosti[12] ?? 'Ciontek CS10', $vrednosti[13] ?? 'GPRS', $vrednosti[14] ?? null];
    }

    /**
     * @param  array<string, mixed>  $atributi
     */
    private function lokacijaKorisnika(array $atributi = []): Lokacija
    {
        return Lokacija::factory()->create($atributi + ['regionId' => $this->beograd, 'lokacija_tipId' => 3, 'pib' => '100000001']);
    }

    private function uredjaj(string $sn, int $lokacijaId, int $statusId, int $terminalTipId): TerminalLokacija
    {
        $terminal = Terminal::create(['sn' => $sn, 'terminal_tipId' => $terminalTipId]);

        return TerminalLokacija::create([
            'terminalId' => $terminal->id,
            'lokacijaId' => $lokacijaId,
            'terminal_statusId' => $statusId,
            'korisnikId' => 1,
            'korisnikIme' => 'Test',
        ]);
    }

    /**
     * @param  list<string>  $poruke
     */
    private function sadrzi(array $poruke, string $tekst): bool
    {
        return collect($poruke)->contains(fn (string $poruka): bool => str_contains($poruka, $tekst));
    }
}
