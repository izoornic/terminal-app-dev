<?php

// tests/Feature/PartsInventory/ReservationServiceTest.php
namespace Tests\Feature\PartsInventory;

use Tests\TestCase;
use App\Models\User;
use App\Models\Lokacija;
use App\Models\PartType;
use App\Models\PartReservation;
use App\Models\PartStock;
use App\PartsInventory\Services\ReservationService;
use App\PartsInventory\Services\PartStockService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReservationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $reservationService;
    protected $stockService;
    protected $user;
    protected $location;
    protected $partType;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->stockService = app(PartStockService::class);
        $this->reservationService = app(ReservationService::class);
        $this->user = User::factory()->create();
        $this->location = Lokacija::factory()->create(['lokacija_tipId' => 1]);
        $this->partType = PartType::factory()->create();
    }

    /** @test */
    public function it_can_create_reservation()
    {
        $this->stockService->addStock(
            $this->partType->id,
            $this->location->id,
            20,
            $this->user->id
        );

        $reservation = $this->reservationService->createReservation(
            $this->partType->id,
            $this->location->id,
            5,
            $this->user->id
        );

        $this->assertInstanceOf(PartReservation::class, $reservation);
        $this->assertEquals(PartReservation::STATUS_AKTIVNA, $reservation->status);

        // Proveri da li je stock ažuriran
        $stock = PartStock::where('part_type_id', $this->partType->id)
            ->where('lokacija_id', $this->location->id)
            ->first();
        
        $this->assertEquals(5, $stock->kolicina_rezervisana);
        $this->assertEquals(15, $stock->kolicina_dostupna);
    }

    /** @test */
    public function it_can_fulfill_reservation()
    {
        $this->stockService->addStock(
            $this->partType->id,
            $this->location->id,
            20,
            $this->user->id
        );

        $reservation = $this->reservationService->createReservation(
            $this->partType->id,
            $this->location->id,
            5,
            $this->user->id
        );

        // Izvrši rezervaciju
        $result = $this->reservationService->fulfillReservation($reservation->id, $this->user->id);

        $this->assertEquals(PartReservation::STATUS_ISKORISCENA, $result->status);

        // Proveri stock
        $stock = PartStock::where('part_type_id', $this->partType->id)
            ->where('lokacija_id', $this->location->id)
            ->first();
        
        $this->assertEquals(15, $stock->kolicina_ukupno);
        $this->assertEquals(0, $stock->kolicina_rezervisana);
    }

    /** @test */
    public function it_can_cancel_reservation()
    {
        $this->stockService->addStock(
            $this->partType->id,
            $this->location->id,
            20,
            $this->user->id
        );

        $reservation = $this->reservationService->createReservation(
            $this->partType->id,
            $this->location->id,
            5,
            $this->user->id
        );

        // Otkaži rezervaciju
        $result = $this->reservationService->cancelReservation($reservation->id, $this->user->id);

        $this->assertEquals(PartReservation::STATUS_OTKAZANA, $result->status);

        // Proveri da li je rezervisana količina oslobođena
        $stock = PartStock::where('part_type_id', $this->partType->id)
            ->where('lokacija_id', $this->location->id)
            ->first();
        
        $this->assertEquals(0, $stock->kolicina_rezervisana);
        $this->assertEquals(20, $stock->kolicina_dostupna);
    }
}