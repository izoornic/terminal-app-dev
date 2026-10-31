<?php

namespace Tests\Feature;

use App\Models\PozicijaPrikazStranica;
use App\Models\PozicijaTip;
use App\Models\Stranica;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigacijaMeniTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Stranica u bazi cija ruta ne postoji u kodu (npr. test baza sinhronizovana pre deploy-a)
     * ne sme da obori stranicu ni u desktop ni u mobilnom meniju.
     */
    public function test_meni_preskace_stranicu_cija_ruta_ne_postoji(): void
    {
        $pozicija = PozicijaTip::forceCreate(['naziv' => 'Admin']);
        $this->dodeliStranicu('dashboard', 'Postojeca stranica', $pozicija->id);
        $this->dodeliStranicu('ruta-koja-ne-postoji', 'Nepostojeca stranica', $pozicija->id);

        $this->actingAs(User::factory()->create(['pozicija_tipId' => $pozicija->id]));

        $response = $this->get(route('profile.show'))
            ->assertOk()
            ->assertDontSee('Nepostojeca stranica');

        $this->assertSame(2, substr_count($response->getContent(), 'Postojeca stranica'));
    }

    /**
     * Stranice sa istim menu_order uvek idu istim redom (po id-u), a ne kako baza vrati.
     */
    public function test_isti_menu_order_se_sortira_po_id(): void
    {
        $pozicija = PozicijaTip::forceCreate(['naziv' => 'Admin']);
        $this->dodeliStranicu('tiket', 'Tiketi', $pozicija->id, 6);
        $this->dodeliStranicu('terminal', 'Terminali', $pozicija->id, 4);
        $this->dodeliStranicu('lokacije', 'Lokacije', $pozicija->id, 4);
        $korisnik = User::factory()->create(['pozicija_tipId' => $pozicija->id]);

        $this->assertSame(['terminal', 'lokacije', 'tiket'], array_keys(User::userPozicijeList($korisnik->id)));
    }

    private function dodeliStranicu(string $routeName, string $naziv, int $pozicijaId, int $menuOrder = 1): void
    {
        $stranica = Stranica::forceCreate(['naziv' => $naziv, 'route_name' => $routeName, 'menu_order' => $menuOrder, 'show_in_meni' => 1]);
        PozicijaPrikazStranica::create(['pozicija_tipId' => $pozicijaId, 'stranicaId' => $stranica->id]);
    }
}
