<?php

namespace Tests\Feature\Terminali;

use App\Http\Livewire\Distributer\DistLokacije;
use App\Http\Livewire\Lokacijes;
use App\Models\DistributerUserIndex;
use App\Models\Lokacija;
use App\Models\LokacijaTip;
use App\Models\Region;
use App\Models\TerminalStatusTip;
use App\Models\TerminalTip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Podaci banke (ACQ) u formi lokacije: na stranici Lokacije se menjaju, kod Distributera
 * se samo prikazuju (partneri ne menjaju MID trgovaca banke).
 */
class LokacijaAcqPoljaTest extends TestCase
{
    use RefreshDatabase;

    private const DIST = 11;

    private Lokacija $trgovac;

    protected function setUp(): void
    {
        parent::setUp();

        $regionId = Region::create(['r_naziv' => 'Beograd'])->id;
        foreach ([1 => 'Servisni centar', 2 => 'Magacin', 3 => 'Korisnik terminala', 4 => 'Distributer'] as $id => $naziv) {
            LokacijaTip::forceCreate(['id' => $id, 'lt_naziv' => $naziv]);
        }
        TerminalStatusTip::forceCreate(['id' => 2, 'ts_naziv' => 'Instaliran']);
        TerminalTip::forceCreate(['id' => 1, 'model' => 'CS10', 'proizvodjac' => 'Ciontek']);

        $this->trgovac = Lokacija::factory()->create([
            'regionId' => $regionId,
            'lokacija_tipId' => 3,
            'pib' => '113735946',
            'email' => null,
            'distributerId' => self::DIST,
            'acq_mid' => '000000000004388',
            'mcc' => '5812',
            'ptt' => null,
            'precizna_lokacija' => null,
        ]);

        $korisnik = User::factory()->create(['pozicija_tipId' => 1, 'lokacijaId' => $this->trgovac->id]);
        DistributerUserIndex::create(['userId' => $korisnik->id, 'licenca_distributer_tipsId' => self::DIST]);
        $this->actingAs($korisnik);
    }

    #[Test]
    public function lokacije_menjaju_podatke_banke(): void
    {
        Livewire::test(Lokacijes::class)
            ->call('updateShowModal', $this->trgovac->id)
            ->assertSet('acq_mid', '000000000004388')
            ->assertSet('mcc', '5812')
            ->assertSee('Podaci banke (ACQ)')
            ->set('acq_mid', '4845')
            ->set('ptt', '11000')
            ->set('precizna_lokacija', ' VGC9+45 Mrčajevci ')
            ->call('update')
            ->assertHasNoErrors();

        $this->trgovac->refresh();
        $this->assertSame('000000000004845', $this->trgovac->acq_mid, 'MID se čuva sa vodećim nulama');
        $this->assertSame('5812', $this->trgovac->mcc);
        $this->assertSame('11000', $this->trgovac->ptt);
        $this->assertSame('VGC9+45 Mrčajevci', $this->trgovac->precizna_lokacija);
    }

    #[Test]
    public function lokacije_odbijaju_neispravne_podatke_banke(): void
    {
        Livewire::test(Lokacijes::class)
            ->call('updateShowModal', $this->trgovac->id)
            ->set('acq_mid', 'MID-1')
            ->set('mcc', '58120')
            ->set('ptt', '110')
            ->call('update')
            ->assertHasErrors(['acq_mid' => 'regex', 'mcc' => 'regex', 'ptt' => 'regex']);

        $this->assertSame('000000000004388', $this->trgovac->fresh()->acq_mid);
    }

    #[Test]
    public function distributer_vidi_podatke_banke_ali_ih_ne_menja(): void
    {
        Livewire::test(DistLokacije::class)
            ->call('updateShowModal', $this->trgovac->id)
            ->assertSee('Podaci banke (ACQ)')
            ->assertSee('000000000004388')
            ->assertDontSeeHtml('wire:model="acq_mid"')
            ->call('update');

        $this->assertSame('000000000004388', $this->trgovac->fresh()->acq_mid);
        $this->assertSame('5812', $this->trgovac->fresh()->mcc);
    }
}
