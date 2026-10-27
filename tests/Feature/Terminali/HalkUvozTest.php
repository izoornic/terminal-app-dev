<?php

namespace Tests\Feature\Terminali;

use App\Actions\TerminalAcq\DodajTerminalAcq;
use App\Actions\TerminalAcq\HalkUvoz;
use App\Models\Lokacija;
use App\Models\LokacijaKontaktOsoba;
use App\Models\Region;
use App\Models\Terminal;
use App\Models\TerminalAcq;
use App\Models\TerminalAcqHistory;
use App\Models\TerminalLokacija;
use App\Models\TerminalStatusTip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * HALK Faza 3: jednokratan uvoz glavne tabele (Excel je dnevnik, poslednji red TID-a
 * određuje stanje, raniji redovi idu u istoriju).
 */
class HalkUvozTest extends TestCase
{
    use RefreshDatabase;

    private const STATUS_INSTALIRAN = 2;

    private const STATUS_ZA_INSTALACIJU = 8;

    private const PIB = '113735946';

    private int $regionId;

    private Lokacija $korisnik;

    private User $autor;

    private string $privremeniFolder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->regionId = Region::create(['r_naziv' => 'Beograd'])->id;
        TerminalStatusTip::forceCreate(['id' => self::STATUS_INSTALIRAN, 'ts_naziv' => 'Instaliran']);
        TerminalStatusTip::forceCreate(['id' => self::STATUS_ZA_INSTALACIJU, 'ts_naziv' => 'Za instalaciju']);
        $this->korisnik = $this->lokacija(['pib' => self::PIB, 'acq_mid' => null, 'mcc' => null, 'ptt' => null]);
        $this->autor = User::factory()->create(['pozicija_tipId' => 1, 'lokacijaId' => $this->korisnik->id]);

        $this->privremeniFolder = sys_get_temp_dir().'/halk_uvoz_'.uniqid();
        mkdir($this->privremeniFolder);
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->privremeniFolder.'/*') ?: []);
        rmdir($this->privremeniFolder);

        parent::tearDown();
    }

    #[Test]
    public function aktivan_red_pravi_tid_uz_uredjaj_dopunjuje_lokaciju_i_dodaje_kontakt(): void
    {
        $terminalId = $this->uredjaj('A26-12RB-1K14121', $this->korisnik->id);

        $izvestaj = $this->uvezi([$this->red([
            'naziv_na_slipu' => 'SAVSKI VENAC RENT ',
            'komunikacija' => 'wifi',
            'precizna_lokacija' => 'VGC9+45 Mrčajevci',
            'kontakt' => 'Momčilo Radonjić',
            'telefon' => '066 5000002',
        ])]);

        $acq = TerminalAcq::where('acq_tid', 'PN004096')->sole();
        $this->assertSame([HalkUvoz::AKTIVAN], array_column($izvestaj, 'rezultat'));
        $this->assertSame($terminalId, $acq->terminalId);
        $this->assertNull($acq->terminal_statusId);
        $this->assertSame('SAVSKI VENAC RENT', $acq->naziv_na_slipu);
        $this->assertSame('WIFI', $acq->komunikacija);
        $this->assertSame('2026-03-18', $acq->datum_instalacije->toDateString());
        $this->assertSame(TerminalAcq::IZVOR_UVOZ, $acq->izvor);
        $this->assertSame('tabela.xlsx', $acq->izvor_fajl);
        $this->assertSame($this->autor->id, $acq->korisnikId);

        $lokacija = $this->korisnik->fresh();
        $this->assertSame('000000000004388', $lokacija->acq_mid);
        $this->assertSame('5812', $lokacija->mcc);
        $this->assertSame('11000', $lokacija->ptt);
        $this->assertSame('VGC9+45 Mrčajevci', $lokacija->precizna_lokacija);

        $kontakt = LokacijaKontaktOsoba::where('lokacijaId', $this->korisnik->id)->sole();
        $this->assertSame('Momčilo Radonjić', $kontakt->name);
        $this->assertSame('+38166 5000002', $kontakt->tel);
    }

    #[Test]
    public function postojece_vrednosti_lokacije_i_kontakt_se_ne_prepisuju(): void
    {
        $this->korisnik->update(['acq_mid' => '000000000000001', 'mcc' => '1111']);
        LokacijaKontaktOsoba::create(['lokacijaId' => $this->korisnik->id, 'name' => 'Postojeći', 'tel' => '+381111']);
        $this->uredjaj('A26-12RB-1K14121', $this->korisnik->id);

        $this->uvezi([$this->red(['kontakt' => 'Novi Kontakt', 'telefon' => '066 5000002'])]);

        $lokacija = $this->korisnik->fresh();
        $this->assertSame('000000000000001', $lokacija->acq_mid);
        $this->assertSame('1111', $lokacija->mcc);
        $this->assertSame('11000', $lokacija->ptt, 'prazno polje se dopunjuje');
        $this->assertSame(['Postojeći'], LokacijaKontaktOsoba::where('lokacijaId', $this->korisnik->id)->pluck('name')->all());
    }

    #[Test]
    public function drugi_pib_ne_dopunjuje_lokaciju(): void
    {
        $this->uredjaj('A26-12RB-1K14121', $this->korisnik->id);

        $izvestaj = $this->uvezi([$this->red(['pib' => 115682682, 'kontakt' => 'Neko'])]);

        $this->assertStringContainsString('nije dopunjena', $izvestaj[0]['opis']);
        $this->assertNull($this->korisnik->fresh()->acq_mid);
        $this->assertSame(0, LokacijaKontaktOsoba::count());
        $this->assertTrue(TerminalAcq::where('acq_tid', 'PN004096')->exists(), 'TID se i dalje uvozi');
    }

    #[Test]
    public function zatvoren_red_pravi_tid_bez_uredjaja_sa_istorijom_uredjaja(): void
    {
        $terminalId = $this->uredjaj('A26-12RB-2D01030', $this->lokacija(['lokacija_tipId' => 2])->id);

        $this->uvezi([$this->red(['acq_tid' => 'PN001817', 'sn' => 'A26-12RB-2D01030', 'status' => 'deinstaliran'])]);

        $acq = TerminalAcq::where('acq_tid', 'PN001817')->sole();
        $this->assertNull($acq->terminalId);
        $this->assertSame(TerminalStatusTip::idPoNazivu('Deinstaliran'), $acq->terminal_statusId);
        $this->assertSame($this->korisnik->id, $acq->lokacijaId, 'lokacija po jedinstvenom PIB-u');

        $history = $acq->histories->sole();
        $this->assertSame($terminalId, $history->terminalId);
        $this->assertSame(self::STATUS_INSTALIRAN, $history->terminal_statusId);
        $this->assertSame('2026-03-18', $history->created_at->toDateString());
    }

    #[Test]
    public function poslednji_red_po_datumu_odredjuje_stanje_a_raniji_idu_u_istoriju(): void
    {
        $stariUredjaj = $this->uredjaj('A26-12RB-2D00992', $this->lokacija(['lokacija_tipId' => 2])->id);
        $noviUredjaj = $this->uredjaj('A26-12RB-1K14851', $this->korisnik->id);

        $izvestaj = $this->uvezi([
            $this->red(['red' => 2, 'acq_tid' => 'PN001758', 'sn' => 'A26-12RB-1K14851', 'datum_instalacije' => 46200]),
            $this->red(['red' => 3, 'acq_tid' => 'PN001758', 'sn' => 'A26-12RB-2D00992', 'datum_instalacije' => 45800, 'status' => 'Deinstaliran']),
        ]);

        $acq = TerminalAcq::where('acq_tid', 'PN001758')->sole();
        $this->assertSame($noviUredjaj, $acq->terminalId);
        $this->assertSame([2 => HalkUvoz::AKTIVAN, 3 => HalkUvoz::ISTORIJA], array_column($izvestaj, 'rezultat', 'red'));
        $this->assertSame($stariUredjaj, $acq->histories->sole()->terminalId);
    }

    #[Test]
    public function pn_pn_povezuje_novi_sa_starim_i_pravi_stari_kad_nema_svoj_red(): void
    {
        $this->uredjaj('A26-12RB-2D01913', $this->korisnik->id);

        $this->uvezi([$this->red(['acq_tid' => 'PN001158/PN002927', 'sn' => 'A26-12RB-2D01913'])]);

        $novi = TerminalAcq::where('acq_tid', 'PN002927')->sole();
        $stari = TerminalAcq::where('acq_tid', 'PN001158')->sole();
        $this->assertSame($stari->id, $novi->prethodni_terminal_acqId);
        $this->assertNull($stari->terminalId);
        $this->assertSame(TerminalStatusTip::idPoNazivu('Prekonfigurisan'), $stari->terminal_statusId);
        $this->assertNull($stari->datum_instalacije);
        $this->assertSame(1, $stari->histories()->count(), 'trag da je stari TID bio na uređaju');
    }

    #[Test]
    public function testni_tid_prazan_status_i_opozvan_bez_sn(): void
    {
        $this->uredjaj('SN-TESTNI-001', $this->korisnik->id);
        $this->uredjaj('A26-12RB-1K13127', $this->korisnik->id);

        $this->uvezi([
            $this->red(['red' => 2, 'acq_tid' => 'PN004519 TEST', 'sn' => 'SN-TESTNI-001']),
            $this->red(['red' => 3, 'acq_tid' => 'PN005128', 'sn' => 'A26-12RB-1K13127', 'status' => null]),
            $this->red(['red' => 4, 'acq_tid' => 'PN002857', 'sn' => 'OPOZVANO!', 'status' => null, 'datum_instalacije' => null]),
        ]);

        $this->assertTrue(TerminalAcq::where('acq_tid', 'PN004519')->sole()->testni);
        $this->assertNotNull(TerminalAcq::where('acq_tid', 'PN005128')->sole()->terminalId, 'prazan status + datum → Instaliran');
        $opozvan = TerminalAcq::where('acq_tid', 'PN002857')->sole();
        $this->assertSame(TerminalStatusTip::idPoNazivu('Opozvan'), $opozvan->terminal_statusId);
        $this->assertSame(0, $opozvan->histories()->count());
    }

    #[Test]
    public function konflikti_i_aktivan_bez_uredjaja_se_preskacu(): void
    {
        foreach (['SN-A-0000001', 'SN-B-0000001', 'SN-C-0000001'] as $sn) {
            $this->uredjaj($sn, $this->korisnik->id);
        }

        $izvestaj = $this->uvezi([
            $this->red(['red' => 2, 'acq_tid' => 'PN004262', 'sn' => 'SN-A-0000001']),
            $this->red(['red' => 3, 'acq_tid' => 'PN004262', 'sn' => 'SN-B-0000001']),
            $this->red(['red' => 4, 'acq_tid' => 'PN004519', 'sn' => 'SN-C-0000001']),
            $this->red(['red' => 5, 'acq_tid' => 'PN004563', 'sn' => 'SN-C-0000001']),
            $this->red(['red' => 6, 'acq_tid' => 'PN000006', 'sn' => 'NEMA-U-BAZI-1']),
            $this->red(['red' => 7, 'acq_tid' => '/', 'sn' => 'OPOZVANO!']),
        ]);

        $this->assertSame([HalkUvoz::PRESKOCEN], array_values(array_unique(array_column($izvestaj, 'rezultat'))));
        $this->assertSame(0, TerminalAcq::count());
    }

    #[Test]
    public function ponovni_uvoz_ne_pravi_duplikate_i_ne_menja_postojece(): void
    {
        $this->uredjaj('A26-12RB-1K14121', $this->korisnik->id);
        $this->uvezi([$this->red()]);
        TerminalAcq::where('acq_tid', 'PN004096')->update(['naziv_na_slipu' => 'IZMENJENO U APLIKACIJI']);

        $izvestaj = $this->uvezi([$this->red()]);

        $this->assertSame([HalkUvoz::POSTOJI], array_column($izvestaj, 'rezultat'));
        $this->assertSame(1, TerminalAcq::count());
        $this->assertSame('IZMENJENO U APLIKACIJI', TerminalAcq::sole()->naziv_na_slipu);
    }

    #[Test]
    public function komanda_bez_potvrde_ne_upisuje_nista(): void
    {
        $this->uredjaj('A26-12RB-1K14121', $this->korisnik->id);

        $this->artisan('halk:uvoz', ['fajl' => $this->excel(), '--izlaz' => $this->privremeniFolder])
            ->expectsOutputToContain('Probni uvoz')
            ->assertSuccessful();

        $this->assertSame(0, TerminalAcq::count());
        $this->assertSame(0, TerminalAcqHistory::count());
        $this->assertNull($this->korisnik->fresh()->acq_mid);
        $izvestaj = file_get_contents(glob($this->privremeniFolder.'/halk_uvoz_probni_*.csv')[0]);
        $this->assertStringContainsString('2;PN004096;A26-12RB-1K14121;AKTIVAN', $izvestaj);
    }

    #[Test]
    public function komanda_sa_potvrdom_upisuje(): void
    {
        $this->uredjaj('A26-12RB-1K14121', $this->korisnik->id);

        $this->artisan('halk:uvoz', [
            'fajl' => $this->excel(),
            '--izlaz' => $this->privremeniFolder,
            '--potvrdi' => true,
            '--korisnik' => $this->autor->id,
        ])
            ->expectsOutputToContain('Uvoz upisan')
            ->assertSuccessful();

        $this->assertSame($this->autor->name, TerminalAcq::where('acq_tid', 'PN004096')->sole()->korisnikIme);
    }

    #[Test]
    public function potvrda_bez_korisnika_se_odbija(): void
    {
        $this->artisan('halk:uvoz', ['fajl' => $this->excel(), '--potvrdi' => true])
            ->expectsOutputToContain('obavezan --korisnik')
            ->assertFailed();
    }

    /**
     * @param  list<array<string, mixed>>  $redovi
     * @return list<array<string, mixed>>
     */
    private function uvezi(array $redovi): array
    {
        return (new HalkUvoz(new DodajTerminalAcq))->uvezi($redovi, $this->autor, 'tabela.xlsx');
    }

    /**
     * Red kao iz HalkExcelCitac; podrazumevano instaliran SAVSKI VENAC RENT.
     *
     * @param  array<string, mixed>  $izmene
     * @return array<string, mixed>
     */
    private function red(array $izmene = []): array
    {
        return $izmene + [
            'red' => 2,
            'acq_tid' => 'PN004096',
            'acq_mid' => '000000000004388',
            'sifra_trgovca' => '000000000004388',
            'naziv_na_slipu' => 'SAVSKI VENAC RENT',
            'pib' => (int) self::PIB,
            'mcc' => 5812,
            'ptt' => 11000,
            'kontakt' => null,
            'telefon' => null,
            'komunikacija' => 'GPRS',
            'dodatne_opcije' => null,
            'datum_zahteva' => 46098,
            'datum_instalacije' => 46099,
            'sn' => 'A26-12RB-1K14121',
            'inventarni_broj' => 2000034603,
            'iccid' => '89381030000359086511',
            'status' => 'Instaliran',
            'precizna_lokacija' => null,
        ];
    }

    /**
     * @param  array<string, mixed>  $atributi
     */
    private function lokacija(array $atributi = []): Lokacija
    {
        return Lokacija::factory()->create($atributi + ['regionId' => $this->regionId, 'lokacija_tipId' => 3]);
    }

    private function uredjaj(string $sn, int $lokacijaId): int
    {
        $terminal = Terminal::create(['sn' => $sn, 'terminal_tipId' => 1]);
        TerminalLokacija::create([
            'terminalId' => $terminal->id,
            'lokacijaId' => $lokacijaId,
            'terminal_statusId' => self::STATUS_INSTALIRAN,
            'korisnikId' => 1,
            'korisnikIme' => 'Test',
        ]);

        return $terminal->id;
    }

    private function excel(): string
    {
        $knjiga = new Spreadsheet;
        $list = $knjiga->getActiveSheet();
        $list->setTitle('HALK');
        $list->fromArray([
            ['ACQ Terminal ID', 'ACQ Merchant ID', 'PIB', 'SN uređaja', 'datum instalacije', 'Instalirana'],
            ['PN004096', '000000000004388', (int) self::PIB, 'A26-12RB-1K14121', 46099, 'Instaliran'],
        ], null, 'A1', true);

        $putanja = $this->privremeniFolder.'/'.uniqid().'.xlsx';
        (new Xlsx($knjiga))->save($putanja);

        return $putanja;
    }
}
