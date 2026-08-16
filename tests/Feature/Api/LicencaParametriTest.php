<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Pokriva vraćanje parametara licence u `Api\LicenceController::show()`.
 *
 * Guard koji provjerava „da li licenca uopšte ima parametre" gledao je
 * `licenca_parametars.id` umjesto `licenca_parametars.licenca_tipId`, pa je
 * poredio dva nepovezana ID prostora. Prolazio je samo slučajno — kad je id
 * tipa licence slučajno postojao i kao id nekog reda u `licenca_parametars`.
 *
 * Zato su svi id-evi ovdje eksplicitni i kao u produkciji: tipovi 1 i 12,
 * parametri 1–3. Tip 12 tako nema podudaran id među parametrima i reprodukuje bag.
 *
 * Id-evi se ne smiju prepustiti auto-inkrementu: InnoDB ga ne resetuje pri
 * rollback-u koji radi RefreshDatabase, pa bi parametri kroz testove dobijali
 * sve veće id-eve i na kraju pogodili 12 — čime bi i stari guard slučajno prošao.
 */
class LicencaParametriTest extends TestCase
{
    use RefreshDatabase;

    private const SN = 'A26-12RB-1K13445';

    private const TIP_SA_POKLAPANJEM = 1;

    private const TIP_BEZ_POKLAPANJA = 12;

    private const DISTRIBUTER_ID = 7;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createLicencaTip(self::TIP_SA_POKLAPANJEM, 'esir');
        $this->createLicencaTip(self::TIP_BEZ_POKLAPANJA, 'Test licenca');

        $this->createParametar(1, self::TIP_SA_POKLAPANJEM, 'horeca');
        $this->createParametar(2, self::TIP_BEZ_POKLAPANJA, 'Param 1');
        $this->createParametar(3, self::TIP_BEZ_POKLAPANJA, 'Param 2');

        $this->createLicencaZaTerminal(self::TIP_SA_POKLAPANJEM, 'esir', 101, [1]);
        $this->createLicencaZaTerminal(self::TIP_BEZ_POKLAPANJA, 'Test licenca', 102, [2, 3]);
    }

    /**
     * Preduslov za reprodukciju: id tipa 12 ne smije postojati kao id parametra,
     * inače bi i stari (pogrešni) guard slučajno prošao.
     */
    #[Test]
    public function tip_licence_12_nema_podudaran_id_u_tabeli_parametara(): void
    {
        $this->assertFalse(
            DB::table('licenca_parametars')->where('id', self::TIP_BEZ_POKLAPANJA)->exists()
        );
    }

    #[Test]
    public function vraca_parametre_za_licencu_cijim_se_id_om_ne_poklapa_nijedan_parametar(): void
    {
        $licenca = $this->licencaIzOdgovora('Test licenca');

        $this->assertSame(['Param 1', 'Param 2'], $licenca['parametars']);
    }

    #[Test]
    public function i_dalje_vraca_parametre_za_licencu_sa_poklapajucim_id_om(): void
    {
        $licenca = $this->licencaIzOdgovora('esir');

        $this->assertSame(['horeca'], $licenca['parametars']);
    }

    #[Test]
    public function vraca_obje_licence_terminala(): void
    {
        $response = $this->getJson('/api/licenca/'.self::SN);

        $response->assertOk()->assertJsonPath('status', true);

        $this->assertSame(
            ['esir', 'Test licenca'],
            array_column($response->json('data'), 'licenca')
        );
    }

    #[Test]
    public function nepoznat_sn_vraca_status_false(): void
    {
        $this->getJson('/api/licenca/NEMA-OVAKVOG-SN')
            ->assertOk()
            ->assertJsonPath('status', false)
            ->assertJsonPath('data', []);
    }

    /**
     * @return array{licenca: string, parametars: list<string>}
     */
    private function licencaIzOdgovora(string $naziv): array
    {
        $response = $this->getJson('/api/licenca/'.self::SN);

        $response->assertOk();

        $licenca = collect($response->json('data'))->firstWhere('licenca', $naziv);

        $this->assertNotNull($licenca, "Licenca '{$naziv}' nije vraćena iz API-ja.");

        return $licenca;
    }

    private function createLicencaTip(int $id, string $naziv): void
    {
        DB::table('licenca_tips')->insert([
            'id' => $id,
            'licenca_naziv' => $naziv,
            'licenca_opis' => $naziv.' opis',
            'osnovna_licenca' => 0,
            'broj_parametara_licence' => 2,
        ]);
    }

    private function createParametar(int $id, int $licencaTipId, string $opis): void
    {
        DB::table('licenca_parametars')->insert([
            'id' => $id,
            'licenca_tipId' => $licencaTipId,
            'param_opis' => $opis,
        ]);
    }

    /**
     * Gradi lanac: licenca_distributer_cenas -> licence_za_terminals,
     * pa veže proslijeđene parametre na tu licencu terminala.
     *
     * @param  list<int>  $parametarIds
     */
    private function createLicencaZaTerminal(int $licencaTipId, string $naziv, int $terminalLokacijaId, array $parametarIds): void
    {
        $cenaId = DB::table('licenca_distributer_cenas')->insertGetId([
            'distributerId' => self::DISTRIBUTER_ID,
            'licenca_tipId' => $licencaTipId,
            'licenca_zeta_cena' => 100.00,
        ]);

        DB::table('licence_za_terminals')->insert([
            'terminal_lokacijaId' => $terminalLokacijaId,
            'distributerId' => self::DISTRIBUTER_ID,
            'licenca_distributer_cenaId' => $cenaId,
            'naziv_licence' => $naziv,
            'mesecId' => 1,
            'terminal_sn' => self::SN,
            'datum_pocetak' => '2026-08-01',
            'datum_kraj' => '2026-09-01',
            'datum_prekoracenja' => '2026-09-16',
            'licenca_poreklo' => 1,
            'signature' => 'test-signature',
        ]);

        foreach ($parametarIds as $parametarId) {
            DB::table('licenca_parametar_terminals')->insert([
                'terminal_lokacijaId' => $terminalLokacijaId,
                'distributerId' => self::DISTRIBUTER_ID,
                'licenca_distributer_cenaId' => $cenaId,
                'licenca_parametarId' => $parametarId,
            ]);
        }
    }
}
