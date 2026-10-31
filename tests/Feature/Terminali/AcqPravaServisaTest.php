<?php

namespace Tests\Feature\Terminali;

use App\Http\Livewire\TerminalAcq\AcqTerminali;
use App\Http\Livewire\TerminalAcq\DodajTerminal;
use App\Http\Livewire\TerminalAcq\RucnaPrekonfiguracija;
use App\Models\Lokacija;
use App\Models\PozicijaPrikazStranica;
use App\Models\Region;
use App\Models\Stranica;
use App\Models\Terminal;
use App\Models\TerminalAcq;
use App\Models\TerminalLokacija;
use App\Models\TerminalStatusTip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Šef servisa i serviser na stranici ACQ terminali: dodela uređaja, izvršenje prekonfiguracije
 * i deinstalacija. Dodaj terminal, ručna prekonfiguracija i opoziv traže pravo upravljanja
 * (acq-terminali-upravljanje), koje imaju samo Admin i Call centar.
 */
class AcqPravaServisaTest extends TestCase
{
    use RefreshDatabase;

    private const SEF_SERVISA = 3;

    private const SERVISER = 4;

    private const STATUS_MAGACIN = 1;

    private const STATUS_INSTALIRAN = 2;

    private const STATUS_ZA_INSTALACIJU = 8;

    private int $regionId;

    private Lokacija $magacin;

    private Lokacija $trgovac;

    protected function setUp(): void
    {
        parent::setUp();

        $this->regionId = Region::create(['r_naziv' => 'Beograd'])->id;
        TerminalStatusTip::forceCreate(['id' => self::STATUS_MAGACIN, 'ts_naziv' => 'Magacin']);
        TerminalStatusTip::forceCreate(['id' => self::STATUS_INSTALIRAN, 'ts_naziv' => 'Instaliran']);
        TerminalStatusTip::forceCreate(['id' => self::STATUS_ZA_INSTALACIJU, 'ts_naziv' => 'Za instalaciju']);
        $this->magacin = Lokacija::factory()->create(['regionId' => $this->regionId, 'lokacija_tipId' => 2, 'distributerId' => null]);
        $this->trgovac = Lokacija::factory()->create(['regionId' => $this->regionId, 'lokacija_tipId' => 3, 'distributerId' => null]);

        $this->dodeliStranicu(AcqTerminali::ROUTE_NAME, [1, 2, self::SEF_SERVISA, self::SERVISER]);
        $this->dodeliStranicu(AcqTerminali::PRAVO_UPRAVLJANJE, [1, 2]);
    }

    /**
     * @return array<string, array{int}>
     */
    public static function servis(): array
    {
        return ['Šef servisa' => [self::SEF_SERVISA], 'Serviser' => [self::SERVISER]];
    }

    #[Test]
    #[DataProvider('servis')]
    public function servis_otvara_stranicu_bez_dugmeta_dodaj_terminal(int $pozicijaId): void
    {
        $this->actingAs($this->korisnik($pozicijaId))
            ->get(route(AcqTerminali::ROUTE_NAME))
            ->assertOk()
            ->assertSeeLivewire(AcqTerminali::class)
            ->assertDontSeeLivewire(DodajTerminal::class)
            ->assertDontSeeLivewire(RucnaPrekonfiguracija::class)
            ->assertDontSee('Dodaj terminal');
    }

    #[Test]
    #[DataProvider('servis')]
    public function servis_dodeljuje_uredjaj_i_deinstalira_ali_ne_opoziva_i_ne_prekonfigurise_rucno(int $pozicijaId): void
    {
        $this->actingAs($this->korisnik($pozicijaId));
        $uredjaj = $this->uredjaj('A26-12RB-SLOBODAN', $this->magacin->id, self::STATUS_MAGACIN);
        $ceka = TerminalAcq::factory()->bezUredjaja(self::STATUS_ZA_INSTALACIJU, $this->trgovac->id)->create(['acq_tid' => 'PN005306']);

        Livewire::test(AcqTerminali::class)
            ->call('prikaziDetalj', $ceka->id)
            ->assertSee('Dodeli uređaj')
            ->assertDontSee('Opozovi')
            ->set('dodelaSn', 'A26-12RB-SLOBODAN')
            ->call('dodeliUredjaj')
            ->assertSet('porukaAkcije', 'Uređaj je dodeljen TID-u.')
            ->assertSee('Deinstaliraj')
            ->assertDontSee('Prekonfiguriši')
            ->set('deinstalacijaLokacijaId', $this->magacin->id)
            ->set('deinstalacijaStatusId', self::STATUS_MAGACIN)
            ->call('deinstaliraj')
            ->assertSet('porukaAkcije', 'TID je deinstaliran, a uređaj premešten.');

        $this->assertSame($this->magacin->id, $uredjaj->fresh()->lokacijaId);
    }

    #[Test]
    #[DataProvider('servis')]
    public function servis_izvrsava_prekonfiguraciju(int $pozicijaId): void
    {
        $this->actingAs($this->korisnik($pozicijaId));
        $uredjaj = $this->uredjaj('A26-12RB-1K14121', $this->trgovac->id, self::STATUS_INSTALIRAN);
        $stari = TerminalAcq::factory()->saUredjajem($uredjaj->terminalId)->create(['acq_tid' => 'PN004096']);
        $novi = TerminalAcq::factory()
            ->bezUredjaja(TerminalStatusTip::idPoNazivu(TerminalStatusTip::CEKA_PREKONFIGURACIJU), $this->trgovac->id)
            ->create(['acq_tid' => 'PN005313', 'prethodni_terminal_acqId' => $stari->id]);

        Livewire::test(AcqTerminali::class)
            ->call('prikaziDetalj', $novi->id)
            ->assertSee('Izvrši prekonfiguraciju')
            ->assertDontSee('Opozovi')
            ->call('izvrsiPrekonfiguraciju')
            ->assertSet('greskaAkcije', null);

        $this->assertSame($uredjaj->terminalId, $novi->fresh()->terminalId);
    }

    #[Test]
    #[DataProvider('servis')]
    public function servis_ne_moze_akcije_upravljanja_ni_direktnim_pozivom(int $pozicijaId): void
    {
        $this->actingAs($this->korisnik($pozicijaId));
        $uredjaj = $this->uredjaj('A26-12RB-1K14121', $this->trgovac->id, self::STATUS_INSTALIRAN);
        $aktivan = TerminalAcq::factory()->saUredjajem($uredjaj->terminalId)->create(['acq_tid' => 'PN004096']);
        $ceka = TerminalAcq::factory()->bezUredjaja(self::STATUS_ZA_INSTALACIJU, $this->trgovac->id)->create(['acq_tid' => 'PN005306']);

        Livewire::test(AcqTerminali::class)->call('prikaziDetalj', $ceka->id)->call('opozovi')->assertForbidden();
        Livewire::test(AcqTerminali::class)->call('prikaziDetalj', $aktivan->id)->call('otvoriPrekonfiguraciju')->assertForbidden();
        Livewire::test(DodajTerminal::class)->assertOk()->call('otvori')->assertForbidden();
        Livewire::test(DodajTerminal::class)->set('acq_tid', 'PN009999')->set('sn', 'A26-12RB-1K14121')->call('sacuvaj')->assertForbidden();
        Livewire::test(RucnaPrekonfiguracija::class)->assertOk()->call('otvori', $aktivan->id)->assertForbidden();
        Livewire::test(RucnaPrekonfiguracija::class)->call('sacuvaj')->assertForbidden();

        $this->assertSame(self::STATUS_ZA_INSTALACIJU, $ceka->fresh()->terminal_statusId);
        $this->assertFalse(TerminalAcq::where('acq_tid', 'PN009999')->exists());
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

    private function korisnik(int $pozicijaId): User
    {
        return User::factory()->create(['pozicija_tipId' => $pozicijaId, 'lokacijaId' => $this->trgovac->id]);
    }
}
