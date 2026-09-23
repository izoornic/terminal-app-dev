<?php

namespace Tests\Feature\Tiket;

use App\Http\Livewire\TiketVrsteKvara;
use App\Models\Lokacija;
use App\Models\PozicijaPrikazStranica;
use App\Models\Region;
use App\Models\Stranica;
use App\Models\Tiket;
use App\Models\TiketAkcijaKorisnikPozicija;
use App\Models\TiketAkcijaTip;
use App\Models\TiketAkcijaVrednostTip;
use App\Models\TiketOpisAkcijaIndex;
use App\Models\TiketOpisKvaraTip;
use App\Models\TiketPrioritetTip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Stranica "Vrste kvara": vide je (i dugme na stranici tiketi) samo pozicije kojima je ruta
 * dodeljena u pozicija_prikaz_stranicas — Admin i Call centar.
 */
class TiketVrsteKvaraTest extends TestCase
{
    use RefreshDatabase;

    private const ADMIN = 1;

    private const CALL_CENTAR = 2;

    private const SEF_SERVISA = 3;

    private const SERVISER = 4;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dodeliStranicu('tiket-vrste-kvara', [self::ADMIN, self::CALL_CENTAR]);
    }

    /**
     * @return array<string, array{int}>
     */
    public static function pozicijeSaPristupom(): array
    {
        return ['Admin' => [self::ADMIN], 'Call centar' => [self::CALL_CENTAR]];
    }

    /**
     * @return array<string, array{int}>
     */
    public static function pozicijeBezPristupa(): array
    {
        return ['Šef servisa' => [self::SEF_SERVISA], 'Serviser' => [self::SERVISER]];
    }

    #[Test]
    #[DataProvider('pozicijeSaPristupom')]
    public function admin_i_call_centar_otvaraju_stranicu(int $pozicijaId): void
    {
        $this->actingAs($this->korisnik($pozicijaId))
            ->get(route('tiket-vrste-kvara'))
            ->assertOk()
            ->assertSeeLivewire(TiketVrsteKvara::class);
    }

    #[Test]
    #[DataProvider('pozicijeBezPristupa')]
    public function ostale_pozicije_ne_mogu_da_otvore_stranicu(int $pozicijaId): void
    {
        $this->actingAs($this->korisnik($pozicijaId))
            ->get(route('tiket-vrste-kvara'))
            ->assertForbidden();
    }

    #[Test]
    public function dugme_vrste_kvara_na_stranici_tiketi_vidi_samo_pozicija_s_pristupom(): void
    {
        $this->pripremiStranicuTiketi([self::ADMIN, self::SERVISER]);
        $link = 'href="'.route('tiket-vrste-kvara').'"';

        $this->actingAs($this->korisnik(self::ADMIN))
            ->get(route('tiket'))
            ->assertOk()
            ->assertSee($link, false);

        $this->actingAs($this->korisnik(self::SERVISER))
            ->get(route('tiket'))
            ->assertOk()
            ->assertDontSee($link, false);
    }

    #[Test]
    public function lista_prikazuje_vrste_po_redosledu_s_brojem_tiketa(): void
    {
        $druga = $this->vrstaKvara('Touch ne radi', listOrder: 2);
        $prva = $this->vrstaKvara('Polomljen ekran', listOrder: 1);
        $this->tiketSaVrstom($druga);
        $this->tiketSaVrstom($druga);
        $this->actingAs($this->korisnik(self::ADMIN));

        $prikazane = Livewire::test(TiketVrsteKvara::class)->viewData('data')->items();

        $this->assertSame([$prva->id, $druga->id], array_map(fn (TiketOpisKvaraTip $vrsta) => $vrsta->id, $prikazane));
        $this->assertSame([0, 2], array_map(fn (TiketOpisKvaraTip $vrsta) => (int) $vrsta->tiketi_count, $prikazane));
    }

    #[Test]
    public function call_centar_dodaje_vrstu_kvara_na_kraj_liste(): void
    {
        $this->vrstaKvara('Polomljen ekran', listOrder: 7);
        $this->actingAs($this->korisnik(self::CALL_CENTAR));

        Livewire::test(TiketVrsteKvara::class)
            ->dispatch('newVrstaKvara')
            ->assertSet('newEditModalVisible', true)
            ->assertSet('list_order', 8)
            ->set('tok_naziv', '  Ne radi modem  ')
            ->set('tok_dodela_nadleznostiId', TiketOpisKvaraTip::NADLEZNOST_CALL_CENTAR)
            ->call('saveNewVrstaKvara')
            ->assertHasNoErrors()
            ->assertSet('newEditModalVisible', false);

        $this->assertDatabaseHas('tiket_opis_kvara_tips', [
            'tok_naziv' => 'Ne radi modem',
            'tok_dodela_nadleznostiId' => TiketOpisKvaraTip::NADLEZNOST_CALL_CENTAR,
            'list_order' => 8,
            'termnal_tipId' => TiketOpisKvaraTip::TERMINAL_TIP,
        ]);
    }

    #[Test]
    public function nova_vrsta_mora_imati_naziv_nadleznost_i_redosled(): void
    {
        $this->actingAs($this->korisnik(self::ADMIN));

        Livewire::test(TiketVrsteKvara::class)
            ->dispatch('newVrstaKvara')
            ->set('tok_naziv', '   ')
            ->set('list_order', null)
            ->call('saveNewVrstaKvara')
            ->assertHasErrors([
                'tok_naziv' => 'required',
                'tok_dodela_nadleznostiId' => 'required',
                'list_order' => 'required',
            ])
            ->assertSet('newEditModalVisible', true);

        $this->assertDatabaseCount('tiket_opis_kvara_tips', 0);
    }

    #[Test]
    public function naziv_mora_biti_jedinstven_a_nadleznost_iz_liste(): void
    {
        $this->vrstaKvara('Polomljen ekran');
        $this->actingAs($this->korisnik(self::ADMIN));

        Livewire::test(TiketVrsteKvara::class)
            ->dispatch('newVrstaKvara')
            ->set('tok_naziv', 'Polomljen ekran')
            ->set('tok_dodela_nadleznostiId', 3)
            ->set('list_order', 0)
            ->call('saveNewVrstaKvara')
            ->assertHasErrors(['tok_naziv' => 'unique', 'tok_dodela_nadleznostiId' => 'in', 'list_order' => 'min'])
            ->assertSee('Vrsta kvara s ovim nazivom već postoji.');

        $this->assertDatabaseCount('tiket_opis_kvara_tips', 1);
    }

    #[Test]
    public function admin_menja_vrstu_kvara(): void
    {
        $vrsta = $this->vrstaKvara('Polomljen ekran', listOrder: 2);
        $this->actingAs($this->korisnik(self::ADMIN));

        Livewire::test(TiketVrsteKvara::class)
            ->call('showUpdateModal', $vrsta->id)
            ->assertSet('is_edit', true)
            ->assertSet('tok_naziv', 'Polomljen ekran')
            ->assertSet('tok_dodela_nadleznostiId', TiketOpisKvaraTip::NADLEZNOST_SEF_SERVISA)
            ->set('tok_naziv', 'Polomljen ili izgreban ekran')
            ->set('tok_dodela_nadleznostiId', TiketOpisKvaraTip::NADLEZNOST_CALL_CENTAR)
            ->set('list_order', 5)
            ->call('updateVrstaKvara')
            ->assertHasNoErrors()
            ->assertSet('newEditModalVisible', false);

        $this->assertDatabaseHas('tiket_opis_kvara_tips', [
            'id' => $vrsta->id,
            'tok_naziv' => 'Polomljen ili izgreban ekran',
            'tok_dodela_nadleznostiId' => TiketOpisKvaraTip::NADLEZNOST_CALL_CENTAR,
            'list_order' => 5,
        ]);
    }

    #[Test]
    public function izmena_zadrzava_sopstveni_naziv_ali_ne_preuzima_tudji(): void
    {
        $vrsta = $this->vrstaKvara('Polomljen ekran', listOrder: 1);
        $this->vrstaKvara('Touch ne radi', listOrder: 2);
        $this->actingAs($this->korisnik(self::ADMIN));

        $komponenta = Livewire::test(TiketVrsteKvara::class)
            ->call('showUpdateModal', $vrsta->id)
            ->set('list_order', 3)
            ->call('updateVrstaKvara')
            ->assertHasNoErrors();

        $komponenta->call('showUpdateModal', $vrsta->id)
            ->set('tok_naziv', 'Touch ne radi')
            ->call('updateVrstaKvara')
            ->assertHasErrors(['tok_naziv' => 'unique']);

        $this->assertDatabaseHas('tiket_opis_kvara_tips', ['id' => $vrsta->id, 'tok_naziv' => 'Polomljen ekran', 'list_order' => 3]);
    }

    #[Test]
    public function brisanje_nekoriscene_vrste_brise_i_korake_za_servisera(): void
    {
        $vrsta = $this->vrstaKvara('Polomljen ekran');
        $druga = $this->vrstaKvara('Touch ne radi');
        foreach ([$vrsta, $vrsta, $druga] as $order => $kvar) {
            TiketOpisAkcijaIndex::forceCreate(['tiket_opis_kvaraId' => $kvar->id, 'tiket_kvar_akcijaId' => 1, 'akcija_order' => $order]);
        }
        $this->actingAs($this->korisnik(self::ADMIN));

        Livewire::test(TiketVrsteKvara::class)
            ->call('showDeleteModal', $vrsta->id)
            ->assertSet('broj_tiketa', 0)
            ->assertSet('broj_akcija', 2)
            ->call('deleteVrstaKvara')
            ->assertSet('deleteModalVisible', false);

        $this->assertDatabaseMissing('tiket_opis_kvara_tips', ['id' => $vrsta->id]);
        $this->assertDatabaseMissing('tiket_opis_akcija_indices', ['tiket_opis_kvaraId' => $vrsta->id]);
        $this->assertDatabaseHas('tiket_opis_akcija_indices', ['tiket_opis_kvaraId' => $druga->id]);
    }

    #[Test]
    public function vrsta_koju_koristi_tiket_se_ne_brise(): void
    {
        $vrsta = $this->vrstaKvara('Polomljen ekran');
        $this->tiketSaVrstom($vrsta);
        $this->actingAs($this->korisnik(self::ADMIN));

        Livewire::test(TiketVrsteKvara::class)
            ->call('showDeleteModal', $vrsta->id)
            ->assertSet('broj_tiketa', 1)
            ->assertSee('ne može biti obrisana')
            ->assertDontSee('wire:click="deleteVrstaKvara"', false)
            ->call('deleteVrstaKvara')
            ->assertSet('deleteModalVisible', true);

        $this->assertDatabaseHas('tiket_opis_kvara_tips', ['id' => $vrsta->id]);
    }

    /**
     * Broj tiketa se ponovo čita pri brisanju — tiket je mogao nastati dok je modal bio otvoren.
     */
    #[Test]
    public function tiket_otvoren_dok_je_modal_otvoren_sprecava_brisanje(): void
    {
        $vrsta = $this->vrstaKvara('Polomljen ekran');
        $this->actingAs($this->korisnik(self::ADMIN));

        $komponenta = Livewire::test(TiketVrsteKvara::class)
            ->call('showDeleteModal', $vrsta->id)
            ->assertSet('broj_tiketa', 0);

        $this->tiketSaVrstom($vrsta);

        $komponenta->call('deleteVrstaKvara')
            ->assertSet('broj_tiketa', 1)
            ->assertSet('deleteModalVisible', true);

        $this->assertDatabaseHas('tiket_opis_kvara_tips', ['id' => $vrsta->id]);
    }

    #[Test]
    public function komponenta_se_ne_moze_ucitati_bez_pristupa(): void
    {
        $this->actingAs($this->korisnik(self::SERVISER));

        Livewire::test(TiketVrsteKvara::class)->assertForbidden();
    }

    /**
     * @return array<string, array{string, list<mixed>}>
     */
    public static function akcije(): array
    {
        return [
            'nova vrsta (modal)' => ['newVrstaKvara', []],
            'sačuvaj novu' => ['saveNewVrstaKvara', []],
            'izmena (modal)' => ['showUpdateModal', ['__vrsta__']],
            'sačuvaj izmenu' => ['updateVrstaKvara', []],
            'brisanje (modal)' => ['showDeleteModal', ['__vrsta__']],
            'obriši' => ['deleteVrstaKvara', []],
        ];
    }

    /**
     * Livewire zahtevi ne prolaze kroz accessrole middleware, pa svaka akcija proverava pravo sama.
     * Stranicu otvara admin, a akciju šalje korisnik bez prava (npr. oduzeto pravo, druga sesija).
     *
     * @param  list<mixed>  $parametri
     */
    #[Test]
    #[DataProvider('akcije')]
    public function akcija_bez_pristupa_vraca_403_i_ne_menja_bazu(string $metoda, array $parametri): void
    {
        $vrsta = $this->vrstaKvara('Polomljen ekran');
        $parametri = array_map(fn (mixed $parametar) => $parametar === '__vrsta__' ? $vrsta->id : $parametar, $parametri);
        $this->actingAs($this->korisnik(self::ADMIN));

        $komponenta = Livewire::test(TiketVrsteKvara::class)
            ->call('showUpdateModal', $vrsta->id)
            ->set('tok_naziv', 'Promenjen naziv');

        $this->actingAs($this->korisnik(self::SERVISER));

        $komponenta->call($metoda, ...$parametri)->assertForbidden();

        $this->assertDatabaseHas('tiket_opis_kvara_tips', ['id' => $vrsta->id, 'tok_naziv' => 'Polomljen ekran']);
        $this->assertDatabaseCount('tiket_opis_kvara_tips', 1);
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
     * Stranica tiketi: pristup ruti, akcije "vidi" i "kreira" tiket i prioritet za filter u listi.
     *
     * @param  list<int>  $pozicije
     */
    private function pripremiStranicuTiketi(array $pozicije): void
    {
        $this->dodeliStranicu('tiket', $pozicije);

        $sve = TiketAkcijaVrednostTip::forceCreate(['akcija_vrednost_opis' => 'sve']);
        TiketAkcijaTip::forceCreate(['id' => 1, 'tiket_akcija' => 'vidi tiket']);
        TiketAkcijaTip::forceCreate(['id' => TiketAkcijaKorisnikPozicija::AKCIJA_KREIRA_TIKET, 'tiket_akcija' => 'kreira tiket']);

        foreach ($pozicije as $pozicijaId) {
            foreach ([1, TiketAkcijaKorisnikPozicija::AKCIJA_KREIRA_TIKET] as $akcijaId) {
                TiketAkcijaKorisnikPozicija::forceCreate([
                    'korisnik_pozicijaId' => $pozicijaId,
                    'tiket_akcijaId' => $akcijaId,
                    'tiket_akcijavrednostId' => $sve->id,
                ]);
            }
        }

        TiketPrioritetTip::forceCreate([
            'tp_naziv' => 'Normalan',
            'btn_collor' => 'green-600',
            'btn_hover_collor' => 'green-700',
            'tr_bg_collor' => 'green-50',
        ]);
    }

    private function korisnik(int $pozicijaId): User
    {
        $region = Region::create(['r_naziv' => 'Beograd']);
        $lokacija = Lokacija::factory()->create(['regionId' => $region->id]);

        return User::factory()->create(['pozicija_tipId' => $pozicijaId, 'lokacijaId' => $lokacija->id]);
    }

    private function vrstaKvara(string $naziv, int $listOrder = 1): TiketOpisKvaraTip
    {
        return TiketOpisKvaraTip::create([
            'termnal_tipId' => TiketOpisKvaraTip::TERMINAL_TIP,
            'tok_naziv' => $naziv,
            'tok_dodela_nadleznostiId' => TiketOpisKvaraTip::NADLEZNOST_SEF_SERVISA,
            'list_order' => $listOrder,
        ]);
    }

    private function tiketSaVrstom(TiketOpisKvaraTip $vrsta): Tiket
    {
        return Tiket::create([
            'tremina_lokacijalId' => 1,
            'tiket_statusId' => 1,
            'opis_kvaraId' => $vrsta->id,
            'opis' => 'Test kvar',
            'tiket_prioritetId' => 1,
            'br_komentara' => 0,
            'korisnik_zatvorio_id' => 0,
        ]);
    }
}
