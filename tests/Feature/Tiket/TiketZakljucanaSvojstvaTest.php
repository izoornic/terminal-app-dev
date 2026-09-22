<?php

namespace Tests\Feature\Tiket;

use App\Http\Livewire\Terminal;
use App\Http\Livewire\Tikets;
use App\Http\Livewire\Tiketview;
use App\Models\Lokacija;
use App\Models\LokacijaTip;
use App\Models\Region;
use App\Models\TerminalStatusTip;
use App\Models\TerminalVendor;
use App\Models\TiketAkcijaKorisnikPozicija;
use App\Models\TiketAkcijaTip;
use App\Models\TiketAkcijaVrednostTip;
use App\Models\TiketPrioritetTip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Prava korisnika nad tiketima (akcije, region, pozicija, id tiketa) računaju se u mount() i
 * ne smeju da se menjaju s klijenta — inače serviser iz konzole ($wire.set) vidi tuđe tikete.
 */
class TiketZakljucanaSvojstvaTest extends TestCase
{
    use RefreshDatabase;

    private const SERVISER = 4;

    /**
     * Tiketview sa nepostojećim id-jem ima najjednostavniji render (errortiket), a zaključavanje
     * se proverava pre rendera.
     *
     * @return array<string, array{class-string, string, mixed}>
     */
    public static function zakljucanaSvojstva(): array
    {
        return [
            'Tikets: vidi tiket' => [Tikets::class, 'tiketAkcija.1', 'sve'],
            'Tikets: kreira tiket' => [Tikets::class, 'tiketAkcija.2', 'sve'],
            'Tikets: region korisnika' => [Tikets::class, 'userRegion', 999],
            'Tikets: pozicija korisnika' => [Tikets::class, 'userPozicija', 1],
            'Tiketview: id tiketa' => [Tiketview::class, 'tikid', 1],
            'Tiketview: validan tiket' => [Tiketview::class, 'validTiket', true],
            'Tiketview: vidi tiket' => [Tiketview::class, 'tiketAkcija', [1 => 'sve']],
            'Tiketview: region korisnika' => [Tiketview::class, 'userRegion', 999],
            'Tiketview: region tiketa' => [Tiketview::class, 'tiketRegion', 999],
            'Terminal: vidi tiket' => [Terminal::class, 'tiketAkcija.1', 'sve'],
            'Terminal: region korisnika' => [Terminal::class, 'userRegion', 999],
            'Terminal: pozicija korisnika' => [Terminal::class, 'userPozicija', 1],
        ];
    }

    #[Test]
    #[DataProvider('zakljucanaSvojstva')]
    public function svojstvo_se_ne_moze_promeniti_s_klijenta(string $komponenta, string $svojstvo, mixed $vrednost): void
    {
        $this->actingAs($this->serviser());

        $test = Livewire::withQueryParams(['id' => 999999])->test($komponenta);

        $this->expectException(CannotUpdateLockedPropertyException::class);

        $test->set($svojstvo, $vrednost);
    }

    /**
     * Serviser: vidi samo dodeljene, ne kreira, ne dodeljuje.
     * Liste u view-ovima (TiketPrioritetTip::prioritetiList, LokacijaTip::tipoviList,
     * TerminalStatusTip::tipoviList, TerminalVendor::allList) padaju nad praznom tabelom, zato po jedan red.
     */
    private function serviser(): User
    {
        $dodeljen = TiketAkcijaVrednostTip::forceCreate(['akcija_vrednost_opis' => 'dodeljen']);
        $ne = TiketAkcijaVrednostTip::forceCreate(['akcija_vrednost_opis' => 'ne']);

        foreach ([1 => ['vidi tiket', $dodeljen], 2 => ['kreira tiket', $ne], 3 => ['dodeljuje tiket', $ne]] as $akcijaId => [$akcija, $vrednost]) {
            TiketAkcijaKorisnikPozicija::forceCreate([
                'korisnik_pozicijaId' => self::SERVISER,
                'tiket_akcijaId' => TiketAkcijaTip::forceCreate(['id' => $akcijaId, 'tiket_akcija' => $akcija])->id,
                'tiket_akcijavrednostId' => $vrednost->id,
            ]);
        }

        TiketPrioritetTip::forceCreate([
            'tp_naziv' => 'Normalan',
            'btn_collor' => 'green-600',
            'btn_hover_collor' => 'green-700',
            'tr_bg_collor' => 'green-50',
        ]);

        LokacijaTip::forceCreate(['lt_naziv' => 'Kupac']);
        TerminalStatusTip::forceCreate(['ts_naziv' => 'Instaliran']);
        TerminalVendor::forceCreate(['name' => 'Ingenico']);

        $region = Region::create(['r_naziv' => 'Beograd']);
        $lokacija = Lokacija::factory()->create(['regionId' => $region->id]);

        return User::factory()->create(['pozicija_tipId' => self::SERVISER, 'lokacijaId' => $lokacija->id]);
    }
}
