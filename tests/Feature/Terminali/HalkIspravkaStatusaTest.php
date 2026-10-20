<?php

namespace Tests\Feature\Terminali;

use App\Actions\TerminalAcq\HalkIspravkaStatusa;
use App\Models\Lokacija;
use App\Models\Region;
use App\Models\Terminal;
use App\Models\TerminalLokacija;
use App\Models\TerminalLokacijaHistory;
use App\Models\TerminalStatusTip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * halk:ispravi-status: "Za instalaciju" → "Instaliran" za uređaje koje banka vodi kao
 * instalirane, a u aplikaciji su kod korisnika. Bez --potvrdi ništa ne menja.
 */
class HalkIspravkaStatusaTest extends TestCase
{
    use RefreshDatabase;

    private const STATUS_MAGACIN = 1;

    private const STATUS_INSTALIRAN = 2;

    private const STATUS_ZA_INSTALACIJU = 8;

    private const PIB = '113735946';

    /**
     * Excel serijski broj za 17.03.2026.
     */
    private const DATUM_INSTALACIJE = 46098;

    private int $regionId;

    private Lokacija $korisnik;

    private string $privremeniFolder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->regionId = Region::create(['r_naziv' => 'Beograd'])->id;
        TerminalStatusTip::forceCreate(['id' => self::STATUS_MAGACIN, 'ts_naziv' => 'Magacin']);
        TerminalStatusTip::forceCreate(['id' => self::STATUS_INSTALIRAN, 'ts_naziv' => 'Instaliran']);
        TerminalStatusTip::forceCreate(['id' => self::STATUS_ZA_INSTALACIJU, 'ts_naziv' => 'Za instalaciju']);
        $this->korisnik = $this->lokacija(['pib' => self::PIB]);

        $this->privremeniFolder = sys_get_temp_dir().'/halk_ispravka_'.uniqid();
        mkdir($this->privremeniFolder);
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->privremeniFolder.'/*') ?: []);
        rmdir($this->privremeniFolder);

        parent::tearDown();
    }

    #[Test]
    public function bira_samo_instalirane_u_excelu_sa_statusom_za_instalaciju_kod_korisnika(): void
    {
        $this->uredjaj('SN-ISPRAVKA-1', $this->korisnik->id, self::STATUS_ZA_INSTALACIJU);
        $this->uredjaj('SN-VEC-INSTAL', $this->korisnik->id, self::STATUS_INSTALIRAN);
        $this->uredjaj('SN-U-MAGACINU', $this->lokacija(['lokacija_tipId' => 2, 'pib' => self::PIB])->id, self::STATUS_ZA_INSTALACIJU);
        $this->uredjaj('SN-DEINSTALIR', $this->korisnik->id, self::STATUS_ZA_INSTALACIJU);

        $kandidati = (new HalkIspravkaStatusa)->kandidati([
            $this->red(2, 'PN000001', 'SN-ISPRAVKA-1'),
            $this->red(3, 'PN000002', 'SN-VEC-INSTAL'),
            $this->red(4, 'PN000003', 'SN-U-MAGACINU'),
            $this->red(5, 'PN000004', 'SN-DEINSTALIR', ['status' => 'Deinstaliran']),
            $this->red(6, 'PN000005', 'SN-NEMA-U-BAZI'),
        ]);

        $this->assertSame(['SN-ISPRAVKA-1'], array_column($kandidati['ispravka'], 'sn'));
        $this->assertSame([], $kandidati['preskoceno']);
    }

    #[Test]
    public function prazan_status_sa_datumom_instalacije_se_racuna_kao_instaliran(): void
    {
        $this->uredjaj('SN-PRAZAN-ST1', $this->korisnik->id, self::STATUS_ZA_INSTALACIJU);

        $kandidati = (new HalkIspravkaStatusa)->kandidati([
            $this->red(2, 'PN005128', 'SN-PRAZAN-ST1', ['status' => null]),
        ]);

        $this->assertCount(1, $kandidati['ispravka']);
    }

    #[Test]
    public function preskace_drugi_pib_lokaciju_bez_pib_a_i_tid_konflikt(): void
    {
        $this->uredjaj('SN-DRUGI-PIB1', $this->korisnik->id, self::STATUS_ZA_INSTALACIJU);
        $this->uredjaj('SN-BEZ-PIB-01', $this->lokacija(['pib' => null])->id, self::STATUS_ZA_INSTALACIJU);
        $this->uredjaj('SN-KONFLIKT-1', $this->korisnik->id, self::STATUS_ZA_INSTALACIJU);

        $kandidati = (new HalkIspravkaStatusa)->kandidati([
            $this->red(2, 'PN000001', 'SN-DRUGI-PIB1', ['pib' => 100165724]),
            $this->red(3, 'PN000002', 'SN-BEZ-PIB-01'),
            $this->red(4, 'PN004519', 'SN-KONFLIKT-1'),
            $this->red(5, 'PN004563', 'SN-KONFLIKT-1'),
        ]);

        $this->assertSame([], $kandidati['ispravka']);
        $razlozi = array_column($kandidati['preskoceno'], 'razlog', 'red');
        $this->assertStringContainsString('PIB se razlikuje', $razlozi[2]);
        $this->assertStringContainsString('nema PIB', $razlozi[3]);
        $this->assertStringContainsString('više TID-ova', $razlozi[4]);
        $this->assertStringContainsString('više TID-ova', $razlozi[5]);
    }

    #[Test]
    public function primeni_upisuje_istoriju_status_autora_i_datum_instalacije(): void
    {
        $terminalLokacija = $this->uredjaj('SN-ISPRAVKA-1', $this->korisnik->id, self::STATUS_ZA_INSTALACIJU, '2026-03-10 09:00:00');
        $autor = $this->autor();
        $ispravka = new HalkIspravkaStatusa;

        $ispravljeno = $ispravka->primeni($ispravka->kandidati([$this->red(2, 'PN000001', 'SN-ISPRAVKA-1')])['ispravka'], $autor);

        $this->assertSame(1, $ispravljeno);
        $terminalLokacija->refresh();
        $this->assertSame(self::STATUS_INSTALIRAN, $terminalLokacija->terminal_statusId);
        $this->assertSame($this->korisnik->id, $terminalLokacija->lokacijaId);
        $this->assertSame($autor->id, $terminalLokacija->korisnikId);
        $this->assertSame($autor->name, $terminalLokacija->korisnikIme);
        $this->assertSame('2026-03-17', $terminalLokacija->updated_at->toDateString());

        $history = TerminalLokacijaHistory::where('terminal_lokacijaId', $terminalLokacija->id)->sole();
        $this->assertSame(self::STATUS_ZA_INSTALACIJU, $history->terminal_statusId);
        $this->assertSame('Test', $history->korisnikIme);
    }

    #[Test]
    public function datum_instalacije_stariji_od_poslednje_izmene_daje_danasnji_datum(): void
    {
        Carbon::setTestNow('2026-10-20 10:00:00');
        $terminalLokacija = $this->uredjaj('SN-ISPRAVKA-1', $this->korisnik->id, self::STATUS_ZA_INSTALACIJU, '2026-05-01 09:00:00');
        $ispravka = new HalkIspravkaStatusa;

        $ispravka->primeni($ispravka->kandidati([$this->red(2, 'PN000001', 'SN-ISPRAVKA-1')])['ispravka'], $this->autor());

        $this->assertSame('2026-10-20', $terminalLokacija->fresh()->updated_at->toDateString());
        Carbon::setTestNow();
    }

    #[Test]
    public function ponovno_pokretanje_ne_menja_nista(): void
    {
        $this->uredjaj('SN-ISPRAVKA-1', $this->korisnik->id, self::STATUS_ZA_INSTALACIJU);
        $ispravka = new HalkIspravkaStatusa;
        $kandidati = $ispravka->kandidati([$this->red(2, 'PN000001', 'SN-ISPRAVKA-1')])['ispravka'];

        $this->assertSame(1, $ispravka->primeni($kandidati, $this->autor()));
        $this->assertSame(0, $ispravka->primeni($kandidati, $this->autor()));
        $this->assertSame(1, TerminalLokacijaHistory::count());
    }

    #[Test]
    public function komanda_bez_potvrde_samo_pravi_listu(): void
    {
        $terminalLokacija = $this->uredjaj('SN-ISPRAVKA-1', $this->korisnik->id, self::STATUS_ZA_INSTALACIJU);

        $this->artisan('halk:ispravi-status', ['fajl' => $this->excel(), '--izlaz' => $this->privremeniFolder])
            ->expectsOutputToContain('Ništa nije promenjeno')
            ->assertSuccessful();

        $this->assertSame(self::STATUS_ZA_INSTALACIJU, $terminalLokacija->fresh()->terminal_statusId);
        $this->assertSame(0, TerminalLokacijaHistory::count());
        $lista = file_get_contents(glob($this->privremeniFolder.'/halk_ispravka_statusa_*.csv')[0]);
        $this->assertStringContainsString('ISPRAVKA;2;PN004096;SN-ISPRAVKA-1', $lista);
    }

    #[Test]
    public function komanda_sa_potvrdom_upisuje(): void
    {
        $terminalLokacija = $this->uredjaj('SN-ISPRAVKA-1', $this->korisnik->id, self::STATUS_ZA_INSTALACIJU);
        $autor = $this->autor();

        $this->artisan('halk:ispravi-status', [
            'fajl' => $this->excel(),
            '--izlaz' => $this->privremeniFolder,
            '--potvrdi' => true,
            '--korisnik' => $autor->id,
        ])
            ->expectsOutputToContain('Ispravljeno uređaja: 1')
            ->assertSuccessful();

        $this->assertSame(self::STATUS_INSTALIRAN, $terminalLokacija->fresh()->terminal_statusId);
    }

    #[Test]
    public function potvrda_bez_korisnika_se_odbija(): void
    {
        $terminalLokacija = $this->uredjaj('SN-ISPRAVKA-1', $this->korisnik->id, self::STATUS_ZA_INSTALACIJU);

        $this->artisan('halk:ispravi-status', ['fajl' => $this->excel(), '--potvrdi' => true, '--korisnik' => 999])
            ->expectsOutputToContain('obavezan --korisnik')
            ->assertFailed();

        $this->assertSame(self::STATUS_ZA_INSTALACIJU, $terminalLokacija->fresh()->terminal_statusId);
    }

    /**
     * @param  array<string, mixed>  $izmene
     * @return array<string, mixed>
     */
    private function red(int $red, string $tid, string $sn, array $izmene = []): array
    {
        return $izmene + [
            'red' => $red,
            'acq_tid' => $tid,
            'sn' => $sn,
            'pib' => (int) self::PIB,
            'datum_instalacije' => self::DATUM_INSTALACIJE,
            'status' => 'Instaliran',
        ];
    }

    /**
     * @param  array<string, mixed>  $atributi
     */
    private function lokacija(array $atributi = []): Lokacija
    {
        return Lokacija::factory()->create($atributi + ['regionId' => $this->regionId, 'lokacija_tipId' => 3]);
    }

    private function uredjaj(string $sn, int $lokacijaId, int $statusId, string $izmenjen = '2026-01-01 08:00:00'): TerminalLokacija
    {
        $terminal = Terminal::create(['sn' => $sn, 'terminal_tipId' => 1]);

        return TerminalLokacija::forceCreate([
            'terminalId' => $terminal->id,
            'lokacijaId' => $lokacijaId,
            'terminal_statusId' => $statusId,
            'korisnikId' => 1,
            'korisnikIme' => 'Test',
            'created_at' => $izmenjen,
            'updated_at' => $izmenjen,
        ]);
    }

    private function autor(): User
    {
        return User::factory()->create(['pozicija_tipId' => 1, 'lokacijaId' => $this->korisnik->id]);
    }

    private function excel(): string
    {
        $knjiga = new Spreadsheet;
        $list = $knjiga->getActiveSheet();
        $list->setTitle('HALK');
        $list->fromArray([
            ['ACQ Terminal ID', 'PIB', 'SN uređaja', 'datum instalacije', 'Instalirana'],
            ['PN004096', (int) self::PIB, 'SN-ISPRAVKA-1', self::DATUM_INSTALACIJE, 'Instaliran'],
        ], null, 'A1', true);

        $putanja = $this->privremeniFolder.'/'.uniqid().'.xlsx';
        (new Xlsx($knjiga))->save($putanja);

        return $putanja;
    }
}
