<?php

namespace Tests\Feature\Tiket;

use App\Http\Livewire\Komponente\AddNewItemButton;
use App\Http\Livewire\Tikets;
use App\Models\Lokacija;
use App\Models\PozicijaPrikazStranica;
use App\Models\Region;
use App\Models\Stranica;
use App\Models\Terminal;
use App\Models\TerminalLokacija;
use App\Models\TiketAkcijaKorisnikPozicija;
use App\Models\TiketAkcijaTip;
use App\Models\TiketAkcijaVrednostTip;
use App\Models\TiketOpisKvaraTip;
use App\Models\TiketPrioritetTip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Dugme "Novi tiket" u headeru stranice /tiket vidi, a modal otvara i tiket kreira, samo pozicija
 * čija je akcija "kreira tiket" (id 2) "sve" ili "region" — isti uslov kao dugme u livewire/tiket.blade.php.
 */
class TiketNoviTiketDugmeTest extends TestCase
{
    use RefreshDatabase;

    private const ADMIN = 1;

    private const SEF_SERVISA = 3;

    private const SERVISER = 4;

    /**
     * @var array<string, int>
     */
    private array $vrednostIds = [];

    private int $prioritetId;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['sve', 'region', 'dodeljen', 'ne'] as $opis) {
            $this->vrednostIds[$opis] = TiketAkcijaVrednostTip::forceCreate(['akcija_vrednost_opis' => $opis])->id;
        }

        TiketAkcijaTip::forceCreate(['id' => 1, 'tiket_akcija' => 'vidi tiket']);
        TiketAkcijaTip::forceCreate(['id' => TiketAkcijaKorisnikPozicija::AKCIJA_KREIRA_TIKET, 'tiket_akcija' => 'kreira tiket']);

        $this->dodeliAkcije(self::ADMIN, vidi: 'sve', kreira: 'sve');
        $this->dodeliAkcije(self::SEF_SERVISA, vidi: 'region', kreira: 'region');
        $this->dodeliAkcije(self::SERVISER, vidi: 'dodeljen', kreira: 'ne');

        $this->prioritetId = TiketPrioritetTip::forceCreate([
            'tp_naziv' => 'Normalan',
            'btn_collor' => 'green-600',
            'btn_hover_collor' => 'green-700',
            'tr_bg_collor' => 'green-50',
        ])->id;
    }

    #[Test]
    public function pozicije_sa_sve_ili_region_mogu_da_kreiraju_tiket(): void
    {
        $this->assertTrue(TiketAkcijaKorisnikPozicija::daliPozicijaMozeKreiratiTiket(self::ADMIN));
        $this->assertTrue(TiketAkcijaKorisnikPozicija::daliPozicijaMozeKreiratiTiket(self::SEF_SERVISA));
    }

    #[Test]
    public function pozicija_sa_ne_bez_prava_ili_bez_pozicije_ne_moze_da_kreira_tiket(): void
    {
        $this->assertFalse(TiketAkcijaKorisnikPozicija::daliPozicijaMozeKreiratiTiket(self::SERVISER));
        $this->assertFalse(TiketAkcijaKorisnikPozicija::daliPozicijaMozeKreiratiTiket(99));
        $this->assertFalse(TiketAkcijaKorisnikPozicija::daliPozicijaMozeKreiratiTiket(null));
    }

    /**
     * Pravo za "vidi tiket" ne sme da otključa dugme — gleda se samo akcija "kreira tiket".
     */
    #[Test]
    public function vrednost_druge_akcije_ne_utice_na_kreiranje(): void
    {
        $pozicija = 7;
        $this->dodeliAkcije($pozicija, vidi: 'sve', kreira: 'ne');

        $this->assertFalse(TiketAkcijaKorisnikPozicija::daliPozicijaMozeKreiratiTiket($pozicija));
    }

    #[Test]
    public function stranica_tiket_prikazuje_dugme_novi_tiket_adminu(): void
    {
        $this->actingAs($this->korisnikSaPristupomStranici(self::ADMIN))
            ->get(route('tiket'))
            ->assertOk()
            ->assertSeeLivewire(AddNewItemButton::class);
    }

    #[Test]
    public function stranica_tiket_ne_prikazuje_dugme_novi_tiket_serviseru(): void
    {
        $this->actingAs($this->korisnikSaPristupomStranici(self::SERVISER))
            ->get(route('tiket'))
            ->assertOk()
            ->assertDontSeeLivewire(AddNewItemButton::class);
    }

    #[Test]
    public function admin_otvara_modal_za_novi_tiket(): void
    {
        $this->actingAs($this->korisnik(self::ADMIN));

        Livewire::test(Tikets::class)
            ->call('newTiketShowModal')
            ->assertOk()
            ->assertSet('modalNewTiketVisible', true);
    }

    #[Test]
    public function sef_servisa_otvara_modal_preko_eventa_iz_headera(): void
    {
        $this->actingAs($this->korisnik(self::SEF_SERVISA));

        Livewire::test(Tikets::class)
            ->dispatch('newTiketEvent')
            ->assertOk()
            ->assertSet('modalNewTiketVisible', true);
    }

    #[Test]
    public function serviser_ne_moze_da_otvori_modal_ni_direktnim_pozivom(): void
    {
        $this->actingAs($this->korisnik(self::SERVISER));

        Livewire::test(Tikets::class)
            ->call('newTiketShowModal')
            ->assertForbidden();
    }

    #[Test]
    public function serviser_ne_moze_da_otvori_modal_preko_eventa(): void
    {
        $this->actingAs($this->korisnik(self::SERVISER));

        Livewire::test(Tikets::class)
            ->dispatch('newTiketEvent')
            ->assertForbidden();
    }

    /**
     * Izmenjen $tiketAkcija na klijentu ne sme da otključa modal — svojstvo je #[Locked].
     */
    #[Test]
    public function serviser_ne_moze_da_zaobidje_proveru_menjanjem_tiket_akcije(): void
    {
        $this->actingAs($this->korisnik(self::SERVISER));

        $komponenta = Livewire::test(Tikets::class);

        $this->expectException(CannotUpdateLockedPropertyException::class);

        $komponenta->set('tiketAkcija.'.TiketAkcijaKorisnikPozicija::AKCIJA_KREIRA_TIKET, 'sve');
    }

    #[Test]
    public function admin_kreira_tiket(): void
    {
        Mail::fake();
        $admin = $this->korisnik(self::ADMIN);
        $terminalLokacijaId = $this->terminalLokacijaId($admin);
        $this->actingAs($admin);

        Livewire::test(Tikets::class)
            ->call('newTiketShowModal')
            ->set('prioritetTiketa', $this->prioritetId)
            ->set('newTerminalLokacijaId', $terminalLokacijaId)
            ->set('opisKvaraList', $this->opisKvaraId())
            ->set('opisKvataTxt', 'Ne radi štampač')
            ->call('create')
            ->assertOk()
            ->assertHasNoErrors()
            ->assertSet('modalNewTiketVisible', false);

        $this->assertDatabaseHas('tikets', [
            'tremina_lokacijalId' => $terminalLokacijaId,
            'korisnik_prijavaId' => $admin->id,
            'opis' => 'Ne radi štampač',
        ]);
    }

    /**
     * @return array<string, array{string, list<mixed>}>
     */
    public static function metodeZaKreiranje(): array
    {
        return [
            'create' => ['create', []],
            'createCallCentar bez dodele' => ['createCallCentar', [false]],
            'createCallCentar sa dodelom' => ['createCallCentar', [true]],
            'createCallCentarClosedTiket' => ['createCallCentarClosedTiket', []],
        ];
    }

    /**
     * Serviser otvara modal s klijenta ($set) i šalje validne podatke,
     * pa 403 dolazi od provere prava, a ne od validacije.
     *
     * @param  list<mixed>  $parametri
     */
    #[Test]
    #[DataProvider('metodeZaKreiranje')]
    public function serviser_ne_moze_da_kreira_tiket_direktnim_pozivom(string $metoda, array $parametri): void
    {
        Mail::fake();
        $serviser = $this->korisnik(self::SERVISER);
        $terminalLokacijaId = $this->terminalLokacijaId($serviser);
        $this->actingAs($serviser);

        Livewire::test(Tikets::class)
            ->set('modalNewTiketVisible', true)
            ->set('newTerminalLokacijaId', $terminalLokacijaId)
            ->set('opisKvaraList', $this->opisKvaraId())
            ->call($metoda, ...$parametri)
            ->assertForbidden();

        $this->assertDatabaseCount('tikets', 0);
        Mail::assertNothingSent();
    }

    private function dodeliAkcije(int $pozicijaId, string $vidi, string $kreira): void
    {
        foreach ([1 => $vidi, TiketAkcijaKorisnikPozicija::AKCIJA_KREIRA_TIKET => $kreira] as $akcijaId => $vrednost) {
            TiketAkcijaKorisnikPozicija::forceCreate([
                'korisnik_pozicijaId' => $pozicijaId,
                'tiket_akcijaId' => $akcijaId,
                'tiket_akcijavrednostId' => $this->vrednostIds[$vrednost],
            ]);
        }
    }

    /**
     * Middleware accessrole pušta na rutu samo ako postoji red u pozicija_prikaz_stranicas.
     */
    private function korisnikSaPristupomStranici(int $pozicijaId): User
    {
        $stranica = Stranica::firstOrCreate(['route_name' => 'tiket'], ['naziv' => 'Tiketi', 'menu_order' => 1, 'sub_menu_order' => 0]);
        PozicijaPrikazStranica::create(['pozicija_tipId' => $pozicijaId, 'stranicaId' => $stranica->id]);

        return $this->korisnik($pozicijaId);
    }

    private function opisKvaraId(): int
    {
        return TiketOpisKvaraTip::forceCreate([
            'termnal_tipId' => 1,
            'tok_naziv' => 'Ne radi štampač',
            'tok_dodela_nadleznostiId' => 1,
            'list_order' => 1,
        ])->id;
    }

    /**
     * Terminal na lokaciji u regionu korisnika (MailToUser traži region tiketa).
     */
    private function terminalLokacijaId(User $user): int
    {
        $terminal = Terminal::create(['sn' => fake()->unique()->numerify('SN########'), 'terminal_tipId' => 1]);

        return TerminalLokacija::create([
            'terminalId' => $terminal->id,
            'lokacijaId' => $user->lokacijaId,
            'terminal_statusId' => 1,
            'korisnikId' => $user->id,
            'korisnikIme' => $user->name,
        ])->id;
    }

    /**
     * Tikets::mount() traži region preko lokacije korisnika.
     */
    private function korisnik(int $pozicijaId): User
    {
        $region = Region::create(['r_naziv' => 'Beograd']);
        $lokacija = Lokacija::factory()->create(['regionId' => $region->id]);

        return User::factory()->create(['pozicija_tipId' => $pozicijaId, 'lokacijaId' => $lokacija->id]);
    }
}
