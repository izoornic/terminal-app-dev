<?php

// tests/Feature/PartsInventory/TransferServiceTest.php
namespace Tests\Feature\PartsInventory;

use Tests\TestCase;
use App\Models\User;
use App\Models\Lokacija;
use App\Models\PartType;
use App\Models\PartTransfer;
use App\Models\PartStock;
use App\Models\TerminalTip;
use App\PartsInventory\Services\TransferService;
use App\PartsInventory\Services\PartStockService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TransferServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $transferService;
    protected $stockService;
    protected $user;
    protected $sourceLocation;
    protected $destinationLocation;
    protected $partType;
    protected $terminalTip;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->stockService = app(PartStockService::class);
        $this->transferService = app(TransferService::class);
        $this->user = User::factory()->create();
        $this->sourceLocation = Lokacija::factory()->create(['lokacija_tipId' => 2]);
        $this->destinationLocation = Lokacija::factory()->create(['lokacija_tipId' => 1]);
        $this->terminalTip = TerminalTip::factory()->count(5)->create();
        $this->partType = PartType::factory()->create();
    }

    /** @test */
    public function it_can_create_transfer()
    {
        // Dodaj stock na source lokaciju
        $this->stockService->addStock(
            $this->partType->id,
            $this->sourceLocation->id,
            50,
            $this->user->id
        );

        $items = [
            [
                'part_type_id' => $this->partType->id,
                'kolicina' => 10,
            ],
        ];

        $transfer = $this->transferService->createTransfer(
            $this->sourceLocation->id,
            $this->destinationLocation->id,
            $items,
            $this->user->id
        );

        $this->assertInstanceOf(PartTransfer::class, $transfer);
        $this->assertEquals(PartTransfer::STATUS_PENDING, $transfer->status);
        $this->assertCount(1, $transfer->items);
    }

    /** @test */
    public function it_can_execute_transfer()
    {
        // Priprema
        $this->stockService->addStock(
            $this->partType->id,
            $this->sourceLocation->id,
            50,
            $this->user->id
        );

        $items = [
            [
                'part_type_id' => $this->partType->id,
                'kolicina' => 10,
            ],
        ];

        $transfer = $this->transferService->createTransfer(
            $this->sourceLocation->id,
            $this->destinationLocation->id,
            $items,
            $this->user->id
        );

        // Izvrši transfer
        $result = $this->transferService->executeTransfer($transfer->id, $this->user->id);

        $this->assertEquals(PartTransfer::STATUS_COMPLETED, $result->status);

        // Proveri stock levels
        $sourceStock = PartStock::where('part_type_id', $this->partType->id)
            ->where('lokacija_id', $this->sourceLocation->id)
            ->first();
        $this->assertEquals(40, $sourceStock->kolicina_ukupno);

        $destStock = PartStock::where('part_type_id', $this->partType->id)
            ->where('lokacija_id', $this->destinationLocation->id)
            ->first();
        $this->assertEquals(10, $destStock->kolicina_ukupno);

        // Proveri movements
        $this->assertDatabaseHas('part_movements', [
            'part_type_id' => $this->partType->id,
            'lokacija_id' => $this->sourceLocation->id,
            'tip_kretanja' => 'TRANSFER_OUT',
            'kolicina' => 10,
        ]);

        $this->assertDatabaseHas('part_movements', [
            'part_type_id' => $this->partType->id,
            'lokacija_id' => $this->destinationLocation->id,
            'tip_kretanja' => 'TRANSFER_IN',
            'kolicina' => 10,
        ]);
    }

    /** @test */
    public function it_prevents_transfer_with_insufficient_stock()
    {
        $this->stockService->addStock(
            $this->partType->id,
            $this->sourceLocation->id,
            5,
            $this->user->id
        );

        $items = [
            [
                'part_type_id' => $this->partType->id,
                'kolicina' => 10,
            ],
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Nedovoljna dostupna količina');

        $this->transferService->createTransfer(
            $this->sourceLocation->id,
            $this->destinationLocation->id,
            $items,
            $this->user->id
        );
    }
}