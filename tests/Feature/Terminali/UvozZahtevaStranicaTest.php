<?php

namespace Tests\Feature\Terminali;

use App\Http\Livewire\TerminalAcq\UvozZahteva;
use App\Models\Lokacija;
use App\Models\PozicijaPrikazStranica;
use App\Models\Region;
use App\Models\Stranica;
use App\Models\TerminalAcq;
use App\Models\TerminalStatusTip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Stranica "Uvoz zahteva banke": samo Admin i Call centar; pregled pa upis;
 * pri upisu se fajl ponovo čita sa servera, a iz browsera se uzimaju samo izbori.
 */
class UvozZahtevaStranicaTest extends TestCase
{
    use RefreshDatabase;

    private const ADMIN = 1;

    private const CALL_CENTAR = 2;

    private const SERVISER = 4;

    private int $regionId;

    private string $privremeniFolder;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        $this->regionId = Region::create(['r_naziv' => 'Beograd'])->id;
        TerminalStatusTip::forceCreate(['id' => 8, 'ts_naziv' => 'Za instalaciju']);
        $stranica = Stranica::forceCreate(['naziv' => 'Uvoz zahteva banke', 'route_name' => UvozZahteva::ROUTE_NAME, 'menu_order' => 39, 'show_in_meni' => 1]);
        foreach ([self::ADMIN, self::CALL_CENTAR] as $pozicijaId) {
            PozicijaPrikazStranica::create(['pozicija_tipId' => $pozicijaId, 'stranicaId' => $stranica->id]);
        }

        $this->privremeniFolder = sys_get_temp_dir().'/acq_stranica_'.uniqid();
        mkdir($this->privremeniFolder);
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->privremeniFolder.'/*') ?: []);
        rmdir($this->privremeniFolder);

        parent::tearDown();
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
            ->get(route(UvozZahteva::ROUTE_NAME))
            ->assertOk()
            ->assertSeeLivewire(UvozZahteva::class);
    }

    #[Test]
    public function ostale_pozicije_nemaju_pristup_ni_stranici_ni_komponenti(): void
    {
        $this->actingAs($this->korisnik(self::SERVISER))
            ->get(route(UvozZahteva::ROUTE_NAME))
            ->assertForbidden();

        Livewire::test(UvozZahteva::class)->assertForbidden();
    }

    #[Test]
    public function ucitavanje_arhivira_fajl_i_prikazuje_pregled_bez_upisa(): void
    {
        $this->actingAs($this->korisnik(self::ADMIN));

        $komponenta = Livewire::test(UvozZahteva::class)
            ->set('fajl', $this->zahtev('ANDROID INSTALACIJA Ciontek_13.10.2026.xlsx'))
            ->call('procitaj')
            ->assertHasNoErrors()
            ->assertSet('datumZahteva', '2026-10-13')
            ->assertSee('GRIZZLY CAFFE')
            ->assertSee('Nova lokacija');

        $this->assertCount(2, $komponenta->get('redovi'));
        $this->assertSame(['izabran' => true, 'lokacija' => UvozZahteva::NOVA_LOKACIJA, 'regionId' => null, 'sn' => ''], $komponenta->get('izbori')[2]);
        $this->assertCount(1, Storage::disk('local')->files('acq-zahtevi'));
        $this->assertSame(0, TerminalAcq::count());
    }

    #[Test]
    public function potvrda_upisuje_izabrane_redove_sa_regionom_za_sve(): void
    {
        $this->actingAs($this->korisnik(self::CALL_CENTAR));

        Livewire::test(UvozZahteva::class)
            ->set('fajl', $this->zahtev('zahtev_13.10.2026.xlsx'))
            ->call('procitaj')
            ->set('regionZaSve', $this->regionId)
            ->call('primeniRegionZaSve')
            ->set('izbori.3.izabran', false)
            ->call('potvrdi')
            ->assertSet('greska', null)
            ->assertSet('rezultat.tidova', 1)
            ->assertSee('Zahtev je upisan');

        $acq = TerminalAcq::where('acq_tid', 'PN005306')->sole();
        $this->assertSame(8, $acq->terminal_statusId);
        $this->assertSame($this->regionId, Lokacija::find($acq->lokacijaId)->regionId);
        $this->assertFalse(TerminalAcq::where('acq_tid', 'PN005307')->exists());
    }

    #[Test]
    public function izmenjeni_podaci_reda_u_browseru_se_ignorisu(): void
    {
        $this->actingAs($this->korisnik(self::ADMIN));

        Livewire::test(UvozZahteva::class)
            ->set('fajl', $this->zahtev('zahtev_13.10.2026.xlsx'))
            ->call('procitaj')
            ->set('redovi.0.tid', 'PN999999')
            ->set('redovi.0.naziv_trgovca', 'PODMETNUTO')
            ->set('izbori.2.regionId', $this->regionId)
            ->set('izbori.3.izabran', false)
            ->call('potvrdi')
            ->assertSet('rezultat.tidova', 1);

        $this->assertFalse(TerminalAcq::where('acq_tid', 'PN999999')->exists());
        $this->assertSame('GRIZZLY CAFFE', Lokacija::find(TerminalAcq::where('acq_tid', 'PN005306')->sole()->lokacijaId)->l_naziv);
    }

    #[Test]
    public function red_sa_greskom_se_ne_upisuje_ni_kad_ga_browser_oznaci(): void
    {
        TerminalAcq::factory()->create(['acq_tid' => 'PN005307']);
        $this->actingAs($this->korisnik(self::ADMIN));

        Livewire::test(UvozZahteva::class)
            ->set('fajl', $this->zahtev('zahtev_13.10.2026.xlsx'))
            ->call('procitaj')
            ->assertSet('izbori.3.izabran', false)
            ->set('izbori.2.regionId', $this->regionId)
            ->set('izbori.3.izabran', true)
            ->set('redovi.1.greske', [])
            ->call('potvrdi')
            ->assertSet('greska', null)
            ->assertSet('rezultat.tidova', 1);

        $this->assertSame(2, TerminalAcq::count());
    }

    #[Test]
    public function greska_pri_upisu_se_prikazuje_i_nista_nije_upisano(): void
    {
        $this->actingAs($this->korisnik(self::ADMIN));

        Livewire::test(UvozZahteva::class)
            ->set('fajl', $this->zahtev('zahtev_13.10.2026.xlsx'))
            ->call('procitaj')
            ->call('potvrdi')
            ->assertSee('Ništa nije upisano')
            ->assertSee('Izaberite region');

        $this->assertSame(0, TerminalAcq::count());
    }

    #[Test]
    public function fajl_koji_nije_excel_se_odbija(): void
    {
        $this->actingAs($this->korisnik(self::ADMIN));

        Livewire::test(UvozZahteva::class)
            ->set('fajl', UploadedFile::fake()->create('zahtev.pdf', 10, 'application/pdf'))
            ->call('procitaj')
            ->assertHasErrors(['fajl' => 'mimes']);
    }

    private function zahtev(string $naziv): UploadedFile
    {
        $knjiga = new Spreadsheet;
        $knjiga->getActiveSheet()->fromArray([
            [null, 'ACQ Terminal ID', 'ACQ Merchant ID', 'Naziv Trgovca', 'Adresa lokacije', 'Mesto lokacije', 'PTT', 'MCC', 'PIB', 'Telefon', 'Kontakt osoba', 'Model terminala'],
            [null, 'PN005306', '000000000004839', 'GRIZZLY CAFFE', 'Kralja Petra I 33', 'Smederevo', 11300, 5813, 115762060, '061 654 8448', 'Blagoje', 'Ciontek CS10'],
            [null, 'PN005307', '000000000004840', 'DINER 020', 'Stevana Nemanje 116', 'Novi Pazar', 36300, 5812, 115934331, '064 391 79 17', 'Amar', 'Ciontek CS10'],
        ], null, 'A1', true);
        $putanja = $this->privremeniFolder.'/'.uniqid().'.xlsx';
        (new Xlsx($knjiga))->save($putanja);

        return UploadedFile::fake()->createWithContent($naziv, file_get_contents($putanja));
    }

    private function korisnik(int $pozicijaId): User
    {
        $lokacija = Lokacija::factory()->create(['regionId' => $this->regionId, 'lokacija_tipId' => 2]);

        return User::factory()->create(['pozicija_tipId' => $pozicijaId, 'lokacijaId' => $lokacija->id]);
    }
}
