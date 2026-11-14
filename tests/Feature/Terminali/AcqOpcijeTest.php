<?php

namespace Tests\Feature\Terminali;

use App\Actions\TerminalAcq\OpcijeTerminalAcq;
use App\Http\Livewire\TerminalAcq\AcqOpcije;
use App\Http\Livewire\TerminalAcq\AcqTerminali;
use App\Models\Lokacija;
use App\Models\PozicijaPrikazStranica;
use App\Models\Region;
use App\Models\Stranica;
use App\Models\Terminal;
use App\Models\TerminalAcq;
use App\Models\TerminalAcqOpcija;
use App\Models\TerminalLokacija;
use App\Models\TerminalStatusTip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

/**
 * Opcije ACQ TID-a: šifarnik (stranica acq-opcije, samo Admin) i check polja u detalju
 * TID-a (svi sa pravom na stranicu, snima se odmah, zatvoren TID samo prikaz).
 */
class AcqOpcijeTest extends TestCase
{
    use RefreshDatabase;

    private const ADMIN = 1;

    private const SERVISER = 4;

    private const STATUS_INSTALIRAN = 2;

    private const STATUS_ZA_INSTALACIJU = 8;

    private Lokacija $trgovac;

    protected function setUp(): void
    {
        parent::setUp();

        TerminalStatusTip::forceCreate(['id' => self::STATUS_INSTALIRAN, 'ts_naziv' => 'Instaliran']);
        TerminalStatusTip::forceCreate(['id' => self::STATUS_ZA_INSTALACIJU, 'ts_naziv' => 'Za instalaciju']);
        $this->trgovac = Lokacija::factory()->create([
            'regionId' => Region::create(['r_naziv' => 'Beograd'])->id,
            'lokacija_tipId' => 3,
            'distributerId' => null,
        ]);

        $this->dodeliStranicu(AcqTerminali::ROUTE_NAME, [self::ADMIN, self::SERVISER]);
        $this->dodeliStranicu(AcqOpcije::ROUTE_NAME, [self::ADMIN]);
    }

    #[Test]
    public function migracija_ubacuje_pocetne_opcije(): void
    {
        $this->assertSame(
            ['CNP predautorizacija', 'Cashback', 'Ručni unos'],
            TerminalAcqOpcija::query()->poRedosledu()->pluck('naziv')->all()
        );
    }

    #[Test]
    public function admin_dodaje_menja_i_deaktivira_opciju(): void
    {
        $this->actingAs($this->korisnik(self::ADMIN));
        $this->get(route(AcqOpcije::ROUTE_NAME))->assertOk()->assertSeeLivewire(AcqOpcije::class);

        Livewire::test(AcqOpcije::class)
            ->dispatch('novaAcqOpcija')
            ->assertSet('redosled', 4)
            ->call('sacuvajNovu')
            ->assertHasErrors(['naziv' => 'required'])
            ->set('naziv', 'cashback')
            ->call('sacuvajNovu')
            ->assertHasErrors(['naziv' => 'unique'])
            ->set('naziv', '  Napojnica  ')
            ->set('opis', 'Tip na POS-u')
            ->call('sacuvajNovu')
            ->assertHasNoErrors()
            ->assertSet('newEditModalVisible', false)
            ->assertSee('Napojnica');

        $napojnica = TerminalAcqOpcija::where('naziv', 'Napojnica')->sole();
        $this->assertSame('Tip na POS-u', $napojnica->opis);
        $this->assertTrue($napojnica->aktivna);

        Livewire::test(AcqOpcije::class)
            ->call('showUpdateModal', $napojnica->id)
            ->assertSet('naziv', 'Napojnica')
            ->set('naziv', 'Napojnica (tip)')
            ->set('opis', '')
            ->call('sacuvajIzmene')
            ->assertHasNoErrors()
            ->call('promeniAktivnost', $napojnica->id);

        $napojnica->refresh();
        $this->assertSame('Napojnica (tip)', $napojnica->naziv);
        $this->assertNull($napojnica->opis);
        $this->assertFalse($napojnica->aktivna);
    }

    #[Test]
    public function sifarnik_vidi_samo_admin(): void
    {
        $this->actingAs($this->korisnik(self::SERVISER));

        $this->get(route(AcqOpcije::ROUTE_NAME))->assertForbidden();
        Livewire::test(AcqOpcije::class)->assertForbidden();
        $this->get(route(AcqTerminali::ROUTE_NAME))->assertOk()->assertDontSee(route(AcqOpcije::ROUTE_NAME));
    }

    #[Test]
    public function serviser_ukljucuje_i_iskljucuje_opciju_a_veza_pamti_poslednju_izmenu(): void
    {
        $serviser = $this->korisnik(self::SERVISER);
        $this->actingAs($serviser);
        $acq = $this->aktivanTid();
        $cashback = TerminalAcqOpcija::where('naziv', 'Cashback')->sole();

        $komponenta = Livewire::test(AcqTerminali::class)
            ->call('prikaziDetalj', $acq->id)
            ->assertSee('CNP predautorizacija')
            ->assertSee('Ručni unos')
            ->call('promeniOpciju', $cashback->id, true)
            ->assertSet('porukaAkcije', 'Opcija Cashback je uključena.')
            ->assertSee('Uključio '.$serviser->name);

        $this->assertSame(['Cashback'], $acq->opcije()->pluck('naziv')->all());

        $komponenta->call('promeniOpciju', $cashback->id, false)
            ->assertSet('porukaAkcije', 'Opcija Cashback je isključena.')
            ->assertSee('Isključio '.$serviser->name);

        $veza = $acq->opcijeVeze()->sole();
        $this->assertFalse((bool) $veza->pivot->ukljucena, 'isključena opcija ostaje red u vezi');
        $this->assertSame($serviser->id, $veza->pivot->korisnikId);
        $this->assertSame([], $acq->opcije()->pluck('naziv')->all());
    }

    #[Test]
    public function neaktivna_opcija_se_ne_nudi_a_postojeca_se_moze_iskljuciti(): void
    {
        $this->actingAs($this->korisnik(self::ADMIN));
        $acq = $this->aktivanTid();
        $drugi = TerminalAcq::factory()->bezUredjaja(self::STATUS_ZA_INSTALACIJU, $this->trgovac->id)->create(['acq_tid' => 'PN005306']);
        $rucniUnos = TerminalAcqOpcija::where('naziv', 'Ručni unos')->sole();
        $acq->opcijeVeze()->attach($rucniUnos->id, ['ukljucena' => true]);
        $rucniUnos->update(['aktivna' => false]);

        Livewire::test(AcqTerminali::class)
            ->call('prikaziDetalj', $drugi->id)
            ->assertDontSee('Ručni unos')
            ->call('promeniOpciju', $rucniUnos->id, true)
            ->assertSet('greskaAkcije', 'Opcija Ručni unos nije aktivna.');
        $this->assertSame(0, $drugi->opcijeVeze()->count());

        Livewire::test(AcqTerminali::class)
            ->call('prikaziDetalj', $acq->id)
            ->assertSee('Ručni unos')
            ->assertSee('(neaktivna)')
            ->call('promeniOpciju', $rucniUnos->id, false)
            ->assertSet('greskaAkcije', null);
        $this->assertSame([], $acq->opcije()->pluck('naziv')->all());
    }

    #[Test]
    public function zatvoren_tid_ne_menja_opcije(): void
    {
        $this->actingAs($this->korisnik(self::ADMIN));
        $zatvoren = TerminalAcq::factory()
            ->bezUredjaja(TerminalStatusTip::idPoNazivu(TerminalStatusTip::DEINSTALIRAN), $this->trgovac->id)
            ->create(['acq_tid' => 'PN001817']);
        $cashback = TerminalAcqOpcija::where('naziv', 'Cashback')->sole();

        Livewire::test(AcqTerminali::class)
            ->call('prikaziDetalj', $zatvoren->id)
            ->assertSee('TID je zatvoren; samo pregled')
            ->call('promeniOpciju', $cashback->id, true)
            ->assertSet('greskaAkcije', 'TID PN001817 je zatvoren; opcije se ne menjaju.');

        $this->assertSame(0, $zatvoren->opcijeVeze()->count());

        $this->expectException(RuntimeException::class);
        (new OpcijeTerminalAcq)->postavi($zatvoren, $cashback->id, true, auth()->user());
    }

    #[Test]
    public function tid_koji_ceka_moze_da_menja_opcije(): void
    {
        $admin = $this->korisnik(self::ADMIN);
        $ceka = TerminalAcq::factory()->bezUredjaja(self::STATUS_ZA_INSTALACIJU, $this->trgovac->id)->create(['acq_tid' => 'PN005306']);
        $cekaPrekonfiguraciju = TerminalAcq::factory()
            ->bezUredjaja(TerminalStatusTip::idPoNazivu(TerminalStatusTip::CEKA_PREKONFIGURACIJU), $this->trgovac->id)
            ->create(['acq_tid' => 'PN005313']);
        $opcija = TerminalAcqOpcija::where('naziv', 'CNP predautorizacija')->sole();

        foreach ([$ceka, $cekaPrekonfiguraciju] as $acq) {
            $this->assertFalse(OpcijeTerminalAcq::jeZatvoren($acq));
            (new OpcijeTerminalAcq)->postavi($acq, $opcija->id, true, $admin);
            $this->assertSame(['CNP predautorizacija'], $acq->opcije()->pluck('naziv')->all());
        }
    }

    private function aktivanTid(): TerminalAcq
    {
        $terminal = Terminal::create(['sn' => 'A26-12RB-1K14121', 'terminal_tipId' => 1]);
        TerminalLokacija::create([
            'terminalId' => $terminal->id,
            'lokacijaId' => $this->trgovac->id,
            'terminal_statusId' => self::STATUS_INSTALIRAN,
            'korisnikId' => 1,
            'korisnikIme' => 'Test',
        ]);

        return TerminalAcq::factory()->saUredjajem($terminal->id)->create(['acq_tid' => 'PN004096']);
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

    private function korisnik(int $pozicijaId): User
    {
        return User::factory()->create(['pozicija_tipId' => $pozicijaId, 'lokacijaId' => $this->trgovac->id]);
    }
}
