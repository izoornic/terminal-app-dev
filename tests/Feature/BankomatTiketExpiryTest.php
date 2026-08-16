<?php

namespace Tests\Feature;

use App\Actions\Bankomati\BankomatTiketReadActions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Pokriva izračun `is_time_expired` u BankomatTiketReadActions::read().
 *
 * `bankomat_tiket_prioritet_tips.time_frame` je MySQL TIME kolona u kojoj se rok
 * zapisuje kao broj sati (npr. '120:00:00' = 5 dana). Upit ga mora provući kroz
 * TIME_TO_SEC() — bez toga MySQL TIME pretvara u cifre HHMMSS, pa bi '120:00:00'
 * bilo protumačeno kao 1200000 sekundi (~13 dana, 21h) umjesto 5 dana.
 */
class BankomatTiketExpiryTest extends TestCase
{
    use RefreshDatabase;

    private int $prioritetId;

    private int $lokacijaId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->prioritetId = $this->createPrioritet('120:00:00');
        $this->lokacijaId = $this->createBankomatLokacija();
    }

    #[Test]
    public function tiket_unutar_roka_od_5_dana_nije_istekao(): void
    {
        $tiketId = $this->createTiket(now()->subDays(3));

        $this->assertSame(0, $this->readIsTimeExpired($tiketId));
    }

    #[Test]
    public function tiket_stariji_od_roka_od_5_dana_je_istekao(): void
    {
        $tiketId = $this->createTiket(now()->subDays(6));

        $this->assertSame(1, $this->readIsTimeExpired($tiketId));
    }

    #[Test]
    public function prioritet_bez_roka_nikad_ne_istekne(): void
    {
        $this->prioritetId = $this->createPrioritet(null);
        $tiketId = $this->createTiket(now()->subDays(365));

        $this->assertSame(0, $this->readIsTimeExpired($tiketId));
    }

    /**
     * Rok od 2 sata mora isteći poslije 3 sata — s bagom (20000 sekundi = 5h 33min)
     * ovaj tiket bi još bio u roku.
     */
    #[Test]
    public function kratak_rok_od_2_sata_istekne_poslije_3_sata(): void
    {
        $this->prioritetId = $this->createPrioritet('02:00:00');
        $tiketId = $this->createTiket(now()->subHours(3));

        $this->assertSame(1, $this->readIsTimeExpired($tiketId));
    }

    private function readIsTimeExpired(int $tiketId): int
    {
        $tiket = BankomatTiketReadActions::read([])->get()->firstWhere('id', $tiketId);

        $this->assertNotNull($tiket, 'Tiket nije vraćen iz read() upita.');

        return (int) $tiket->is_time_expired;
    }

    private function createPrioritet(?string $timeFrame): int
    {
        return DB::table('bankomat_tiket_prioritet_tips')->insertGetId([
            'btpt_naziv' => 'Test prioritet',
            'btpt_opis' => 'Test',
            'time_frame' => $timeFrame,
            'btn_collor' => 'bg-red-600',
            'btn_hover_collor' => 'bg-red-700',
            'tr_bg_collor' => 'bg-red-50',
        ]);
    }

    /**
     * Gradi minimalan lanac redova koji read() spaja join-ovima:
     * region -> blokacija -> bankomat_lokacija, product_tip -> bankomat_tip -> bankomat.
     */
    private function createBankomatLokacija(): int
    {
        $regionId = DB::table('bankomat_regions')->insertGetId([
            'r_naziv' => 'Test region',
        ]);

        $blokacijaId = DB::table('blokacijas')->insertGetId([
            'bl_naziv' => 'Test lokacija',
            'bl_mesto' => 'Beograd',
            'bankomat_region_id' => $regionId,
        ]);

        $productTipId = DB::table('bankomat_product_tips')->insertGetId([
            'bp_tip_naziv' => 'Test produkt',
        ]);

        $bankomatTipId = DB::table('bankomat_tips')->insertGetId([
            'model' => 'Test model',
            'bankomat_produkt_tip_id' => $productTipId,
        ]);

        $bankomatId = DB::table('bankomats')->insertGetId([
            'b_sn' => 'SN-TEST-1',
            'b_terminal_id' => 'TID-1',
            'bankomat_tip_id' => $bankomatTipId,
        ]);

        return DB::table('bankomat_lokacijas')->insertGetId([
            'bankomat_id' => $bankomatId,
            'blokacija_id' => $blokacijaId,
        ]);
    }

    private function createTiket(\DateTimeInterface $createdAt): int
    {
        return DB::table('bankomat_tikets')->insertGetId([
            'bankomat_lokacija_id' => $this->lokacijaId,
            'bankomat_tiket_prioritet_id' => $this->prioritetId,
            'opis' => 'Test kvar',
            'status' => 'Otvoren',
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }
}
