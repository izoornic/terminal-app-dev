<?php

namespace Tests\Feature\Terminali;

use App\Http\Livewire\TerminalAcq\AcqKomentari;
use App\Http\Livewire\TerminalAcq\AcqTerminali;
use App\Models\Lokacija;
use App\Models\PozicijaPrikazStranica;
use App\Models\Region;
use App\Models\Stranica;
use App\Models\Terminal;
use App\Models\TerminalAcq;
use App\Models\TerminalAcqComment;
use App\Models\TerminalComment;
use App\Models\TerminalLokacija;
use App\Models\TerminalStatusTip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Komentari ACQ TID-a: svi sa pravom na stranicu dodaju, briše autor ili Admin/Call centar
 * (meko), a komentari uređaja se uz njih prikazuju samo za čitanje.
 */
class AcqKomentariTest extends TestCase
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
        $this->dodeliStranicu(AcqTerminali::PRAVO_UPRAVLJANJE, [self::ADMIN]);
    }

    #[Test]
    public function serviser_dodaje_komentar_a_brojac_se_azurira(): void
    {
        $serviser = $this->korisnik(self::SERVISER);
        $this->actingAs($serviser);
        $acq = $this->tidBezUredjaja();

        Livewire::test(AcqKomentari::class, ['terminalAcqId' => $acq->id])
            ->assertSee('Nema komentara.')
            ->set('noviKomentar', "Trgovac traži instalaciju posle 15h.\nPozvati pre dolaska.")
            ->call('posalji')
            ->assertHasNoErrors()
            ->assertDispatched('acqKomentarPromenjen')
            ->assertSet('noviKomentar', '')
            ->assertSee('Trgovac traži instalaciju posle 15h.')
            ->assertSee($serviser->name);

        $acq->refresh();
        $this->assertSame(1, $acq->br_komentara);
        $this->assertSame($serviser->id, $acq->last_comment_userId);
        $this->assertNotNull($acq->last_comment_at);
        $this->assertSame($serviser->id, TerminalAcqComment::sole()->userId);
    }

    #[Test]
    public function prazan_i_prekratak_komentar_se_odbija(): void
    {
        $this->actingAs($this->korisnik(self::SERVISER));
        $acq = $this->tidBezUredjaja();

        Livewire::test(AcqKomentari::class, ['terminalAcqId' => $acq->id])
            ->call('posalji')
            ->assertHasErrors(['noviKomentar' => 'required'])
            ->set('noviKomentar', 'ok')
            ->call('posalji')
            ->assertHasErrors(['noviKomentar' => 'min']);

        $this->assertSame(0, TerminalAcqComment::count());
    }

    #[Test]
    public function autor_brise_svoj_komentar_meko(): void
    {
        $serviser = $this->korisnik(self::SERVISER);
        $this->actingAs($serviser);
        $acq = $this->tidBezUredjaja();
        $komentar = $this->komentar($acq, $serviser, 'Pogrešan unos');

        Livewire::test(AcqKomentari::class, ['terminalAcqId' => $acq->id])
            ->call('obrisi', $komentar->id)
            ->assertSet('greska', null)
            ->assertDispatched('acqKomentarPromenjen')
            ->assertDontSee('Pogrešan unos');

        $komentar->refresh();
        $this->assertFalse($komentar->is_active);
        $this->assertNotNull($komentar->deleted_at);
        $this->assertSame(0, $acq->fresh()->br_komentara);
    }

    #[Test]
    public function serviser_ne_brise_tudji_komentar_a_admin_brise(): void
    {
        $autor = $this->korisnik(self::SERVISER);
        $acq = $this->tidBezUredjaja();
        $komentar = $this->komentar($acq, $autor, 'Komentar drugog servisera');

        $this->actingAs($this->korisnik(self::SERVISER));
        Livewire::test(AcqKomentari::class, ['terminalAcqId' => $acq->id])
            ->assertDontSee('Obriši komentar')
            ->call('obrisi', $komentar->id)
            ->assertSet('greska', 'Komentar može da obriše samo autor, Admin ili Call centar.')
            ->assertNotDispatched('acqKomentarPromenjen');
        $this->assertTrue($komentar->fresh()->is_active);

        $this->actingAs($this->korisnik(self::ADMIN));
        Livewire::test(AcqKomentari::class, ['terminalAcqId' => $acq->id])
            ->assertSee('Obriši komentar')
            ->call('obrisi', $komentar->id)
            ->assertSet('greska', null);
        $this->assertFalse($komentar->fresh()->is_active);
    }

    #[Test]
    public function komentari_uredjaja_se_prikazuju_samo_za_citanje(): void
    {
        $admin = $this->korisnik(self::ADMIN);
        $this->actingAs($admin);
        $uredjaj = $this->uredjaj('A26-12RB-1K14121');
        $acq = TerminalAcq::factory()->saUredjajem($uredjaj->terminalId)->create(['acq_tid' => 'PN004096']);
        TerminalComment::create(['terminal_lokacijaId' => $uredjaj->id, 'userId' => $admin->id, 'comment' => 'Zamenjen štampač']);

        Livewire::test(AcqKomentari::class, ['terminalAcqId' => $acq->id])
            ->assertSee('Komentari uređaja A26-12RB-1K14121')
            ->assertSeeLivewire('komponente.prikazkomentara');

        Livewire::test(AcqKomentari::class, ['terminalAcqId' => $this->tidBezUredjaja()->id])
            ->assertDontSee('Komentari uređaja');
    }

    #[Test]
    public function lista_prikazuje_broj_komentara_i_otvara_modal(): void
    {
        $admin = $this->korisnik(self::ADMIN);
        $this->actingAs($admin);
        $uredjaj = $this->uredjaj('A26-12RB-1K14121');
        $uredjaj->update(['br_komentara' => 2]);
        $acq = TerminalAcq::factory()->saUredjajem($uredjaj->terminalId)->create(['acq_tid' => 'PN004096']);
        $this->komentar($acq, $admin, 'Prvi');
        $acq->update(['br_komentara' => 1]);

        Livewire::test(AcqTerminali::class)
            ->assertSee('Komentari TID-a (uređaj ima 2)')
            ->assertSeeHtml('<span class="text-xs text-gray-400"> +2</span>')
            ->call('komentariShowModal', $acq->id)
            ->assertSet('modalKomentariVisible', true)
            ->assertSet('komentariTidId', $acq->id)
            ->assertSeeLivewire(AcqKomentari::class);
    }

    #[Test]
    public function bez_prava_na_stranicu_nema_komentara(): void
    {
        $this->actingAs($this->korisnik(7));
        $acq = $this->tidBezUredjaja();

        Livewire::test(AcqKomentari::class, ['terminalAcqId' => $acq->id])->assertForbidden();
    }

    private function tidBezUredjaja(): TerminalAcq
    {
        return TerminalAcq::factory()->bezUredjaja(self::STATUS_ZA_INSTALACIJU, $this->trgovac->id)->create(['acq_tid' => 'PN00'.fake()->unique()->numerify('####')]);
    }

    private function komentar(TerminalAcq $acq, User $autor, string $tekst): TerminalAcqComment
    {
        return TerminalAcqComment::create(['terminal_acqId' => $acq->id, 'userId' => $autor->id, 'comment' => $tekst]);
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

    private function uredjaj(string $sn): TerminalLokacija
    {
        $terminal = Terminal::create(['sn' => $sn, 'terminal_tipId' => 1]);

        return TerminalLokacija::create([
            'terminalId' => $terminal->id,
            'lokacijaId' => $this->trgovac->id,
            'terminal_statusId' => self::STATUS_INSTALIRAN,
            'korisnikId' => 1,
            'korisnikIme' => 'Test',
        ]);
    }

    private function korisnik(int $pozicijaId): User
    {
        return User::factory()->create(['pozicija_tipId' => $pozicijaId, 'lokacijaId' => $this->trgovac->id]);
    }
}
