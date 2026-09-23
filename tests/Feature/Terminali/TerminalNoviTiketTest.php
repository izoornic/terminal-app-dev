<?php

namespace Tests\Feature\Terminali;

use App\Http\Livewire\Terminal;
use App\Models\Lokacija;
use App\Models\LokacijaTip;
use App\Models\Region;
use App\Models\Terminal as TerminalModel;
use App\Models\TerminalLokacija;
use App\Models\TerminalStatusTip;
use App\Models\TerminalVendor;
use App\Models\TiketAkcijaKorisnikPozicija;
use App\Models\TiketAkcijaTip;
use App\Models\TiketAkcijaVrednostTip;
use App\Models\TiketOpisKvaraTip;
use App\Models\TiketPrioritetTip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Novi tiket sa stranice terminala: kolonu "Tiket" vidi, a tiket otvara, samo pozicija čija je
 * akcija "kreira tiket" "sve" ili "region" (isto pravo kao na stranici tiketa).
 */
class TerminalNoviTiketTest extends TestCase
{
    use RefreshDatabase;

    private const ADMIN = 1;

    private const SERVISER = 4;

    /**
     * Terminal::newTiketShowModal() postavlja prioritet 4.
     */
    private const PODRAZUMEVANI_PRIORITET = 4;

    private const DUGME_NOVI_TIKET = 'wire:click="newTiketShowModal(';

    private int $opisKvaraId;

    protected function setUp(): void
    {
        parent::setUp();

        $sve = TiketAkcijaVrednostTip::forceCreate(['akcija_vrednost_opis' => 'sve']);
        $dodeljen = TiketAkcijaVrednostTip::forceCreate(['akcija_vrednost_opis' => 'dodeljen']);
        $ne = TiketAkcijaVrednostTip::forceCreate(['akcija_vrednost_opis' => 'ne']);

        TiketAkcijaTip::forceCreate(['id' => 1, 'tiket_akcija' => 'vidi tiket']);
        TiketAkcijaTip::forceCreate(['id' => TiketAkcijaKorisnikPozicija::AKCIJA_KREIRA_TIKET, 'tiket_akcija' => 'kreira tiket']);

        foreach ([self::ADMIN => [$sve, $sve], self::SERVISER => [$dodeljen, $ne]] as $pozicijaId => [$vidi, $kreira]) {
            foreach ([1 => $vidi, TiketAkcijaKorisnikPozicija::AKCIJA_KREIRA_TIKET => $kreira] as $akcijaId => $vrednost) {
                TiketAkcijaKorisnikPozicija::forceCreate([
                    'korisnik_pozicijaId' => $pozicijaId,
                    'tiket_akcijaId' => $akcijaId,
                    'tiket_akcijavrednostId' => $vrednost->id,
                ]);
            }
        }

        TiketPrioritetTip::forceCreate([
            'id' => self::PODRAZUMEVANI_PRIORITET,
            'tp_naziv' => 'Normalan',
            'btn_collor' => 'green-600',
            'btn_hover_collor' => 'green-700',
            'tr_bg_collor' => 'green-50',
        ]);

        $this->opisKvaraId = TiketOpisKvaraTip::forceCreate([
            'termnal_tipId' => 1,
            'tok_naziv' => 'Ne radi štampač',
            'tok_dodela_nadleznostiId' => 1,
            'list_order' => 1,
        ])->id;

        LokacijaTip::forceCreate(['lt_naziv' => 'Kupac']);
        TerminalStatusTip::forceCreate(['ts_naziv' => 'Instaliran']);
        TerminalVendor::forceCreate(['name' => 'Ingenico']);
    }

    #[Test]
    public function admin_vidi_dugme_i_kreira_tiket_sa_stranice_terminala(): void
    {
        Mail::fake();
        $admin = $this->korisnik(self::ADMIN);
        $terminalLokacijaId = $this->terminalLokacijaId($admin);
        $this->actingAs($admin);

        Livewire::test(Terminal::class)
            ->assertSeeHtml(self::DUGME_NOVI_TIKET.$terminalLokacijaId.')')
            ->call('newTiketShowModal', $terminalLokacijaId)
            ->assertSet('newTiketVisible', true)
            ->set('opisKvaraList', $this->opisKvaraId)
            ->set('opisKvataTxt', 'Ne radi štampač')
            ->call('createTiket')
            ->assertOk()
            ->assertHasNoErrors()
            ->assertSet('newTiketVisible', false);

        $this->assertDatabaseHas('tikets', [
            'tremina_lokacijalId' => $terminalLokacijaId,
            'korisnik_prijavaId' => $admin->id,
            'opis' => 'Ne radi štampač',
        ]);
    }

    #[Test]
    public function serviser_ne_vidi_dugme_novi_tiket(): void
    {
        $serviser = $this->korisnik(self::SERVISER);
        $terminalLokacijaId = $this->terminalLokacijaId($serviser);
        $this->actingAs($serviser);

        Livewire::test(Terminal::class)
            ->assertOk()
            ->assertSeeHtml('wire:key="terminal-'.$terminalLokacijaId.'"')
            ->assertDontSeeHtml(self::DUGME_NOVI_TIKET);
    }

    #[Test]
    public function serviser_ne_moze_da_otvori_modal_za_novi_tiket(): void
    {
        $serviser = $this->korisnik(self::SERVISER);
        $terminalLokacijaId = $this->terminalLokacijaId($serviser);
        $this->actingAs($serviser);

        Livewire::test(Terminal::class)
            ->call('newTiketShowModal', $terminalLokacijaId)
            ->assertForbidden();
    }

    /**
     * @return array<string, array{string, list<mixed>}>
     */
    public static function metodeZaKreiranje(): array
    {
        return [
            'createTiket' => ['createTiket', []],
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

        Livewire::test(Terminal::class)
            ->set('newTiketVisible', true)
            ->set('modelId', $terminalLokacijaId)
            ->set('prioritetTiketa', self::PODRAZUMEVANI_PRIORITET)
            ->set('opisKvaraList', $this->opisKvaraId)
            ->call($metoda, ...$parametri)
            ->assertForbidden();

        $this->assertDatabaseCount('tikets', 0);
        Mail::assertNothingSent();
    }

    /**
     * Terminal::mount() traži region preko lokacije korisnika.
     */
    private function korisnik(int $pozicijaId): User
    {
        $region = Region::create(['r_naziv' => 'Beograd']);
        $lokacija = Lokacija::factory()->create(['regionId' => $region->id]);

        return User::factory()->create(['pozicija_tipId' => $pozicijaId, 'lokacijaId' => $lokacija->id]);
    }

    private function terminalLokacijaId(User $user): int
    {
        $terminal = TerminalModel::create(['sn' => fake()->unique()->numerify('SN########'), 'terminal_tipId' => 1]);

        return TerminalLokacija::create([
            'terminalId' => $terminal->id,
            'lokacijaId' => $user->lokacijaId,
            'terminal_statusId' => 1,
            'korisnikId' => $user->id,
            'korisnikIme' => $user->name,
        ])->id;
    }
}
