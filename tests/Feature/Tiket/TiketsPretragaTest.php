<?php

namespace Tests\Feature\Tiket;

use App\Http\Livewire\Tikets;
use App\Models\Lokacija;
use App\Models\Region;
use App\Models\Terminal;
use App\Models\TerminalLokacija;
use App\Models\Tiket;
use App\Models\TiketAkcijaKorisnikPozicija;
use App\Models\TiketAkcijaTip;
use App\Models\TiketAkcijaVrednostTip;
use App\Models\TiketOpisKvaraTip;
use App\Models\TiketPrioritetTip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Pokriva filtere na listi tiketa (Livewire Tikets) nakon uvođenja tipiziranih public polja.
 *
 * Opcija "---" u <select wire:model.live="searchRegion"> šalje prazan string. Kod `int` polja
 * Livewire tada radi unset() i komponenta puca (PropertyNotFoundException), zato je polje `?int`.
 */
class TiketsPretragaTest extends TestCase
{
    use RefreshDatabase;

    private int $opisKvaraId;

    private int $prioritetId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dodeliAdminuPravoSve();

        $this->opisKvaraId = TiketOpisKvaraTip::forceCreate([
            'termnal_tipId' => 1,
            'tok_naziv' => 'Ne radi štampač',
            'tok_dodela_nadleznostiId' => 1,
            'list_order' => 1,
        ])->id;

        $this->prioritetId = TiketPrioritetTip::forceCreate([
            'tp_naziv' => 'Normalan',
            'btn_collor' => 'green-600',
            'btn_hover_collor' => 'green-700',
            'tr_bg_collor' => 'green-50',
        ])->id;
    }

    #[Test]
    public function vracanje_regiona_na_prazno_ne_rusi_listu_i_prikazuje_sve_tikete(): void
    {
        $beograd = Region::create(['r_naziv' => 'Beograd']);
        $nis = Region::create(['r_naziv' => 'Niš']);
        $tiketBeograd = $this->tiketURegionu($beograd->id);
        $tiketNis = $this->tiketURegionu($nis->id);

        $this->actingAs($this->admin($beograd->id));

        $komponenta = Livewire::test(Tikets::class)
            ->assertSet('searchRegion', null);
        $this->assertTiketi([$tiketBeograd->id, $tiketNis->id], $komponenta);

        $komponenta->set('searchRegion', (string) $nis->id)
            ->assertSet('searchRegion', $nis->id);
        $this->assertTiketi([$tiketNis->id], $komponenta);

        $komponenta->set('searchRegion', '')
            ->assertOk()
            ->assertSet('searchRegion', null);
        $this->assertTiketi([$tiketBeograd->id, $tiketNis->id], $komponenta);
    }

    #[Test]
    public function pretraga_po_nazivu_lokacije_i_mestu_se_vraca_na_sve_kad_se_polje_obrise(): void
    {
        $region = Region::create(['r_naziv' => 'Beograd']);
        $tiketApoteka = $this->tiketURegionu($region->id, ['l_naziv' => 'Apoteka Zdravlje', 'mesto' => 'Zemun']);
        $tiketMarket = $this->tiketURegionu($region->id, ['l_naziv' => 'Market Centar', 'mesto' => 'Borča']);

        $this->actingAs($this->admin($region->id));

        $komponenta = Livewire::test(Tikets::class)
            ->set('searchLokacijaNaziv', 'Apoteka');
        $this->assertTiketi([$tiketApoteka->id], $komponenta);

        $komponenta->set('searchLokacijaNaziv', '')
            ->set('searchMesto', 'Borča');
        $this->assertTiketi([$tiketMarket->id], $komponenta);

        $komponenta->set('searchMesto', '')
            ->assertOk();
        $this->assertTiketi([$tiketApoteka->id, $tiketMarket->id], $komponenta);
    }

    #[Test]
    public function pretraga_po_imenu_dodeljenog_korisnika(): void
    {
        $region = Region::create(['r_naziv' => 'Beograd']);
        $petar = $this->serviser('Petar Petrović');
        $marko = $this->serviser('Marko Marković');
        $tiketPetar = $this->tiketURegionu($region->id, dodeljenId: $petar->id);
        $tiketMarko = $this->tiketURegionu($region->id, dodeljenId: $marko->id);
        $nedodeljen = $this->tiketURegionu($region->id);

        $this->actingAs($this->admin($region->id));

        $komponenta = Livewire::test(Tikets::class)
            ->set('searchDodeljenIme', 'petar');
        $this->assertTiketi([$tiketPetar->id], $komponenta);

        $komponenta->set('searchDodeljenIme', 'Marković');
        $this->assertTiketi([$tiketMarko->id], $komponenta);

        $komponenta->set('searchDodeljenIme', 'Nepostojeći');
        $this->assertTiketi([], $komponenta);

        $komponenta->set('searchDodeljenIme', '')
            ->assertOk();
        $this->assertTiketi([$tiketPetar->id, $tiketMarko->id, $nedodeljen->id], $komponenta);
    }

    /**
     * Bez resetPage() bi korisnik ostao na 2. strani liste koja sada ima samo jednu stranu.
     */
    #[Test]
    public function pretraga_po_dodeljenom_vraca_listu_na_prvu_stranu(): void
    {
        config(['global.paginate' => 1]);
        $region = Region::create(['r_naziv' => 'Beograd']);
        $tiketPetar = $this->tiketURegionu($region->id, dodeljenId: $this->serviser('Petar Petrović')->id);
        $this->tiketURegionu($region->id, dodeljenId: $this->serviser('Marko Marković')->id);

        $this->actingAs($this->admin($region->id));

        Livewire::test(Tikets::class)
            ->call('gotoPage', 2, 'tik')
            ->set('searchDodeljenIme', 'Petar')
            ->assertSet('paginators.tik', 1)
            ->tap(fn (Testable $komponenta) => $this->assertTiketi([$tiketPetar->id], $komponenta));
    }

    /**
     * @param  list<int>  $ocekivaniIds
     */
    private function assertTiketi(array $ocekivaniIds, Testable $komponenta): void
    {
        $prikazani = collect($komponenta->viewData('data')->items())->pluck('tikid')->map(fn ($id) => (int) $id)->sort()->values()->all();
        sort($ocekivaniIds);

        $this->assertSame($ocekivaniIds, $prikazani);
    }

    /**
     * Admin (pozicija 1) vidi sve tikete: akcije 1 (vidi), 2 (kreira), 3 (dodeljuje) => "sve".
     *
     * Komponenta čita akcije po id-u ($tiketAkcija[1]), a auto-increment se ne vraća
     * rollback-om između testova, pa id mora biti zadat eksplicitno.
     */
    private function dodeliAdminuPravoSve(): void
    {
        $sve = TiketAkcijaVrednostTip::forceCreate(['akcija_vrednost_opis' => 'sve']);

        foreach ([1 => 'vidi tiket', 2 => 'kreira tiket', 3 => 'dodeljuje tiket'] as $akcijaId => $akcija) {
            TiketAkcijaKorisnikPozicija::forceCreate([
                'korisnik_pozicijaId' => 1,
                'tiket_akcijaId' => TiketAkcijaTip::forceCreate(['id' => $akcijaId, 'tiket_akcija' => $akcija])->id,
                'tiket_akcijavrednostId' => $sve->id,
            ]);
        }
    }

    private function admin(int $regionId): User
    {
        $lokacija = Lokacija::factory()->create(['regionId' => $regionId]);

        return User::factory()->create(['pozicija_tipId' => 1, 'lokacijaId' => $lokacija->id]);
    }

    private function serviser(string $ime): User
    {
        return User::factory()->create(['name' => $ime, 'pozicija_tipId' => 4, 'lokacijaId' => 1]);
    }

    /**
     * Gradi lanac koji read() spaja: tiket -> terminal_lokacija -> lokacija -> region, terminal.
     *
     * @param  array<string, mixed>  $lokacijaAtributi
     */
    private function tiketURegionu(int $regionId, array $lokacijaAtributi = [], ?int $dodeljenId = null): Tiket
    {
        $lokacija = Lokacija::factory()->create(array_merge(['regionId' => $regionId], $lokacijaAtributi));
        $terminal = Terminal::create(['sn' => fake()->unique()->numerify('SN########'), 'terminal_tipId' => 1]);

        $terminalLokacija = TerminalLokacija::create([
            'terminalId' => $terminal->id,
            'lokacijaId' => $lokacija->id,
            'terminal_statusId' => 1,
            'korisnikId' => 1,
            'korisnikIme' => 'Test',
        ]);

        return Tiket::create([
            'tremina_lokacijalId' => $terminalLokacija->id,
            'tiket_statusId' => 1,
            'opis_kvaraId' => $this->opisKvaraId,
            'korisnik_dodeljenId' => $dodeljenId,
            'opis' => 'Test kvar',
            'tiket_prioritetId' => $this->prioritetId,
            'br_komentara' => 0,
            'korisnik_zatvorio_id' => 0,
        ]);
    }
}
