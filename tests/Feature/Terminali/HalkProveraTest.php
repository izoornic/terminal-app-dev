<?php

namespace Tests\Feature\Terminali;

use App\Actions\TerminalAcq\HalkExcelCitac;
use App\Actions\TerminalAcq\HalkProvera;
use App\Models\Lokacija;
use App\Models\LokacijaKontaktOsoba;
use App\Models\Region;
use App\Models\Terminal;
use App\Models\TerminalAcq;
use App\Models\TerminalLokacija;
use App\Models\TerminalStatusTip;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

/**
 * HALK Faza 2: halk:provera poredi glavnu tabelu sa bazom i ništa ne upisuje.
 */
class HalkProveraTest extends TestCase
{
    use RefreshDatabase;

    private const STATUS_MAGACIN = 1;

    private const STATUS_INSTALIRAN = 2;

    private const STATUS_ZA_INSTALACIJU = 8;

    private int $regionId;

    private string $privremeniFolder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->regionId = Region::create(['r_naziv' => 'Beograd'])->id;
        TerminalStatusTip::forceCreate(['id' => self::STATUS_MAGACIN, 'ts_naziv' => 'Magacin']);
        TerminalStatusTip::forceCreate(['id' => self::STATUS_INSTALIRAN, 'ts_naziv' => 'Instaliran']);
        TerminalStatusTip::forceCreate(['id' => self::STATUS_ZA_INSTALACIJU, 'ts_naziv' => 'Za instalaciju']);

        $this->privremeniFolder = sys_get_temp_dir().'/halk_test_'.uniqid();
        mkdir($this->privremeniFolder);
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->privremeniFolder.'/*') ?: []);
        rmdir($this->privremeniFolder);

        parent::tearDown();
    }

    #[Test]
    public function red_koji_se_slaze_sa_bazom_je_ok(): void
    {
        $lokacija = $this->lokacija(['pib' => '113735946']);
        $this->uredjaj('A26-12RB-1K14121', $lokacija->id, self::STATUS_INSTALIRAN);
        LokacijaKontaktOsoba::create(['lokacijaId' => $lokacija->id, 'name' => 'Momčilo Radonjić', 'tel' => '+38166 5000002']);

        $nalazi = $this->proveri([$this->red([
            'kontakt' => 'Radonjić Momčilo',
            'telefon' => '066 5000002',
        ])]);

        $this->assertSame([HalkProvera::OK], array_column($nalazi, 'kategorija'));
    }

    #[Test]
    public function pib_lokacije_prazan_ili_razlicit(): void
    {
        $bezPib = $this->lokacija(['pib' => null]);
        $this->uredjaj('SN-BEZ-PIB-1', $bezPib->id, self::STATUS_INSTALIRAN);
        $drugiPib = $this->lokacija(['pib' => '100165724']);
        $this->uredjaj('SN-DRUGI-PIB', $drugiPib->id, self::STATUS_INSTALIRAN);

        $nalazi = $this->proveri([
            $this->red(['red' => 2, 'acq_tid' => 'PN000001', 'sn' => 'SN-BEZ-PIB-1']),
            $this->red(['red' => 3, 'acq_tid' => 'PN000002', 'sn' => 'SN-DRUGI-PIB', 'pib' => 10165724]),
        ]);

        $this->assertNalaz($nalazi, 2, HalkProvera::LOKACIJA_BEZ_PIB);
        $razlicit = $this->assertNalaz($nalazi, 3, HalkProvera::PIB_RAZLICIT);
        $this->assertStringContainsString('Excel NEISPRAVNA, aplikacija ispravna', $razlicit['opis']);
    }

    #[Test]
    public function status_se_ne_slaze_u_oba_smera(): void
    {
        $magacin = $this->lokacija(['lokacija_tipId' => 2, 'pib' => null]);
        $this->uredjaj('SN-MAGACIN-01', $magacin->id, self::STATUS_MAGACIN);
        $korisnik = $this->lokacija(['pib' => '113735946']);
        $this->uredjaj('SN-ZA-INSTAL', $korisnik->id, self::STATUS_ZA_INSTALACIJU);
        $this->uredjaj('SN-I-DALJE-RADI', $korisnik->id, self::STATUS_INSTALIRAN);

        $nalazi = $this->proveri([
            $this->red(['red' => 2, 'acq_tid' => 'PN000001', 'sn' => 'SN-MAGACIN-01']),
            $this->red(['red' => 3, 'acq_tid' => 'PN000002', 'sn' => 'SN-ZA-INSTAL']),
            $this->red(['red' => 4, 'acq_tid' => 'PN000003', 'sn' => 'SN-I-DALJE-RADI', 'status' => 'deinstaliran']),
        ]);

        $this->assertStringContainsString('nije kod korisnika', $this->assertNalaz($nalazi, 2, HalkProvera::STATUS_SE_NE_SLAZE)['opis']);
        $this->assertSame(
            [HalkProvera::STATUS_SE_NE_SLAZE],
            array_column(array_filter($nalazi, fn (array $nalaz): bool => $nalaz['red'] === 2), 'kategorija'),
            'PIB lokacije magacina se ne poredi sa PIB-om trgovca.'
        );
        $this->assertStringContainsString('nije ažuriran', $this->assertNalaz($nalazi, 3, HalkProvera::STATUS_SE_NE_SLAZE)['opis']);
        $this->assertStringContainsString('U Excelu zatvoren', $this->assertNalaz($nalazi, 4, HalkProvera::STATUS_SE_NE_SLAZE)['opis']);
    }

    #[Test]
    public function tid_konflikti_i_istorijski_duplikat(): void
    {
        $lokacija = $this->lokacija(['pib' => '113735946']);
        foreach (['SN-A-0000001', 'SN-B-0000001', 'SN-C-0000001', 'SN-D-0000001'] as $sn) {
            $this->uredjaj($sn, $lokacija->id, self::STATUS_INSTALIRAN);
        }

        $nalazi = $this->proveri([
            $this->red(['red' => 2, 'acq_tid' => 'PN004262', 'sn' => 'SN-A-0000001']),
            $this->red(['red' => 3, 'acq_tid' => 'PN004262', 'sn' => 'SN-B-0000001']),
            $this->red(['red' => 4, 'acq_tid' => 'PN004519', 'sn' => 'SN-C-0000001']),
            $this->red(['red' => 5, 'acq_tid' => 'PN004563', 'sn' => 'SN-C-0000001']),
            $this->red(['red' => 6, 'acq_tid' => 'PN001758', 'sn' => 'SN-D-0000001']),
            $this->red(['red' => 7, 'acq_tid' => 'PN001758', 'sn' => 'SN-D-0000001', 'status' => 'Deinstaliran']),
        ]);

        $this->assertStringContainsString('aktivan u više redova', $this->assertNalaz($nalazi, 2, HalkProvera::TID_KONFLIKT)['opis']);
        $this->assertStringContainsString('PN004519, PN004563', $this->assertNalaz($nalazi, 5, HalkProvera::TID_KONFLIKT)['opis']);
        $this->assertStringContainsString('istorijski duplikat', $this->assertNalaz($nalazi, 7, HalkProvera::TID_KONFLIKT)['opis']);
    }

    #[Test]
    public function neispravan_tid_testni_bez_uredjaja_i_prazan_status(): void
    {
        $lokacija = $this->lokacija(['pib' => '113735946']);
        $this->uredjaj('SN-TESTNI-001', $lokacija->id, self::STATUS_INSTALIRAN);

        $nalazi = $this->proveri([
            $this->red(['red' => 2, 'acq_tid' => '/', 'sn' => 'OPOZVANO!', 'status' => null]),
            $this->red(['red' => 3, 'acq_tid' => 'PN004519 TEST', 'sn' => 'SN-TESTNI-001']),
            $this->red(['red' => 4, 'acq_tid' => 'PN002857', 'sn' => 'OPOZVANO!', 'status' => null]),
            $this->red(['red' => 5, 'acq_tid' => 'PN000005', 'sn' => 'A26-NEMA-U-BAZI']),
        ]);

        $this->assertNalaz($nalazi, 2, HalkProvera::NEISPRAVAN_TID);
        $this->assertStringContainsString('Testni TID', $this->assertNalaz($nalazi, 3, HalkProvera::NEISPRAVAN_TID)['opis']);
        $this->assertNalaz($nalazi, 4, HalkProvera::BEZ_UREDJAJA);
        $this->assertSame('Opozvan', $this->assertNalaz($nalazi, 4, HalkProvera::STATUS_PRAZAN)['aplikacija']);
        $this->assertStringContainsString('ne postoji', $this->assertNalaz($nalazi, 5, HalkProvera::BEZ_UREDJAJA)['opis']);
    }

    #[Test]
    public function mid_kontakt_telefon_i_precizna_lokacija(): void
    {
        $lokacija = $this->lokacija(['pib' => '113735946']);
        $this->uredjaj('SN-MID-00001', $lokacija->id, self::STATUS_INSTALIRAN);
        $this->uredjaj('SN-MID-00002', $lokacija->id, self::STATUS_INSTALIRAN);
        LokacijaKontaktOsoba::create(['lokacijaId' => $lokacija->id, 'name' => 'Vladan Plečić', 'tel' => '+381060 491 5554']);

        $nalazi = $this->proveri([
            $this->red([
                'red' => 2,
                'acq_tid' => 'PN000001',
                'sn' => 'SN-MID-00001',
                'acq_mid' => '000000000002053',
                'sifra_trgovca' => '000000000002054',
                'telefon' => '060 4915 555',
                'precizna_lokacija' => 'VGC9+45 Mrčajevci',
            ]),
            $this->red([
                'red' => 3,
                'acq_tid' => 'PN000002',
                'sn' => 'SN-MID-00002',
                'acq_mid' => '000000000003689',
                'sifra_trgovca' => '000000000003689',
                'telefon' => str_repeat('064 123 4567 ', 3).'za slip zvati drugi broj',
            ]),
        ]);

        $this->assertNalaz($nalazi, 2, HalkProvera::MID_RAZLICIT);
        $this->assertNalaz($nalazi, 2, HalkProvera::KONTAKT_RAZLICIT);
        $this->assertStringContainsString('plus kod', $this->assertNalaz($nalazi, 2, HalkProvera::PRECIZNA_LOKACIJA)['opis']);
        $this->assertNalaz($nalazi, 2, HalkProvera::LOKACIJA_VISE_MID);
        $this->assertNalaz($nalazi, 3, HalkProvera::LOKACIJA_VISE_MID);
        $this->assertNalaz($nalazi, 3, HalkProvera::TELEFON_PREDUGACAK);
    }

    #[Test]
    public function citac_nalazi_kolone_po_zaglavlju_i_kad_je_kolona_a_prazna(): void
    {
        $fajl = $this->excel([
            [null, 'ACQ Terminal ID', 'ACQ Merchant ID', 'Naziv Trgovca', 'Adresa lokacije', 'Mesto lokacije', 'PIB', 'Model terminala', 'dodatne opcije, (npr. preauth)'],
            [null, 'PN005313', '000000000004845', 'PRE SUDA DOO', 'Dr Aleksandra Kostića 15', 'Beograd', 115682682, 'Ciontek CS10', 'prekonfiguracija PN004096'],
            [null, null, null, null, null, null, null, null, null],
        ]);

        $redovi = (new HalkExcelCitac)->procitaj($fajl, ['acq_tid', 'acq_mid', 'naziv_trgovca', 'adresa', 'mesto', 'pib']);

        $this->assertCount(1, $redovi);
        $this->assertSame(2, $redovi[0]['red']);
        $this->assertSame('PN005313', $redovi[0]['acq_tid']);
        $this->assertSame('Ciontek CS10', $redovi[0]['model']);
        $this->assertSame('prekonfiguracija PN004096', $redovi[0]['dodatne_opcije']);
    }

    #[Test]
    public function citac_odbija_fajl_bez_obavezne_kolone(): void
    {
        $fajl = $this->excel([['ACQ Terminal ID', 'PIB'], ['PN005313', 115682682]]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('sn uređaja');

        (new HalkExcelCitac)->procitaj($fajl, ['acq_tid', 'sn']);
    }

    #[Test]
    public function komanda_pravi_csv_i_ne_upisuje_u_bazu(): void
    {
        $lokacija = $this->lokacija(['pib' => '113735946']);
        $this->uredjaj('A26-12RB-1K14121', $lokacija->id, self::STATUS_INSTALIRAN);
        $brojLokacija = Lokacija::count();

        $fajl = $this->excel([
            ['ACQ Terminal ID', 'PIB', 'SN uređaja', 'datum instalacije', 'Instalirana'],
            ['PN004096', 113735946, 'A26-12RB-1K14121', 46099, '=IF(ISBLANK(D2),"Za instalaciju","Instaliran")'],
        ], 'HALK');

        $this->artisan('halk:provera', ['fajl' => $fajl, '--izlaz' => $this->privremeniFolder])
            ->expectsOutputToContain('Pročitano redova: 1')
            ->assertSuccessful();

        $csv = glob($this->privremeniFolder.'/halk_provera_*.csv');
        $this->assertCount(1, $csv);
        $sadrzaj = file_get_contents($csv[0]);
        $this->assertStringStartsWith("\xEF\xBB\xBF", $sadrzaj);
        $this->assertStringContainsString('PN004096;A26-12RB-1K14121', str_replace('"', '', $sadrzaj));
        $this->assertSame(0, TerminalAcq::count());
        $this->assertSame($brojLokacija, Lokacija::count());
    }

    #[Test]
    public function komanda_javlja_nepostojeci_fajl(): void
    {
        $this->artisan('halk:provera', ['fajl' => '/nema/takvog.xlsx'])
            ->expectsOutputToContain('Fajl ne postoji')
            ->assertFailed();
    }

    /**
     * @param  list<array<string, mixed>>  $redovi
     * @return list<array<string, mixed>>
     */
    private function proveri(array $redovi): array
    {
        return (new HalkProvera)->proveri($redovi);
    }

    /**
     * @param  list<array<string, mixed>>  $nalazi
     * @return array<string, mixed>
     */
    private function assertNalaz(array $nalazi, int $red, int $kategorija): array
    {
        foreach ($nalazi as $nalaz) {
            if ($nalaz['red'] === $red && $nalaz['kategorija'] === $kategorija) {
                return $nalaz;
            }
        }

        $this->fail(sprintf(
            'Nema nalaza "%s" za red %d. Nalazi reda: %s',
            HalkProvera::KATEGORIJE[$kategorija],
            $red,
            implode(', ', array_column(array_filter($nalazi, fn (array $nalaz): bool => $nalaz['red'] === $red), 'naziv'))
        ));
    }

    /**
     * Red kao iz HalkExcelCitac; podrazumevano instaliran HALK uređaj SAVSKI VENAC RENT.
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
            'naziv_trgovca' => 'SAVSKI VENAC RENT',
            'pib' => 113735946,
            'mcc' => 5812,
            'kontakt' => null,
            'telefon' => null,
            'sn' => 'A26-12RB-1K14121',
            'datum_instalacije' => 46099,
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

    /**
     * @param  list<list<mixed>>  $redovi
     */
    private function excel(array $redovi, ?string $sheet = null): string
    {
        $knjiga = new Spreadsheet;
        $list = $knjiga->getActiveSheet();
        if ($sheet !== null) {
            $list->setTitle($sheet);
        }
        $list->fromArray($redovi, null, 'A1', true);

        $putanja = $this->privremeniFolder.'/'.uniqid().'.xlsx';
        (new Xlsx($knjiga))->save($putanja);

        return $putanja;
    }
}
