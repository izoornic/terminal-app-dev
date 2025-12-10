<?php

// app/PartsInventory/Services/TransferService.php
namespace App\PartsInventory\Services;

use App\Models\PartTransfer;
use App\Models\PartTransferItem;
use App\Models\PartMovement;
use App\Models\PartStock;
use Illuminate\Support\Facades\DB;
use Exception;

class TransferService
{
    protected PartStockService $stockService;

    public function __construct(PartStockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Kreiraj novi transfer (sa više stavki)
     */
    public function createTransfer(
        int $sourceLocationId,
        int $destinationLocationId,
        array $items, // [{part_type_id, kolicina}, ...]
        int $userId,
        ?string $napomena = null
    ): PartTransfer {
        return DB::transaction(function () use ($sourceLocationId, $destinationLocationId, $items, $userId, $napomena) {
            // Validacija
            if ($sourceLocationId === $destinationLocationId) {
                throw new Exception("Source i destination lokacije ne mogu biti iste.");
            }

            if (empty($items)) {
                throw new Exception("Transfer mora imati bar jednu stavku.");
            }

            // Proveri dostupnost svih delova
            foreach ($items as $item) {
                $stock = PartStock::where('part_type_id', $item['part_type_id'])
                    ->where('lokacija_id', $sourceLocationId)
                    ->first();

                if (!$stock || $stock->kolicina_dostupna < $item['kolicina']) {
                    $partType = \App\Models\PartType::find($item['part_type_id']);
                    throw new Exception("Nedovoljna dostupna količina za deo: {$partType->naziv}");
                }
            }

            // Kreiraj transfer dokument
            $transfer = PartTransfer::create([
                'transfer_broj' => PartTransfer::generateTransferNumber(),
                'source_lokacija_id' => $sourceLocationId,
                'destination_lokacija_id' => $destinationLocationId,
                'status' => PartTransfer::STATUS_PENDING,
                'kreirao_korisnik_id' => $userId,
                'napomena' => $napomena,
            ]);

            // Dodaj stavke
            foreach ($items as $item) {
                PartTransferItem::create([
                    'transfer_id' => $transfer->id,
                    'part_type_id' => $item['part_type_id'],
                    'kolicina' => $item['kolicina'],
                ]);
            }

            return $transfer->load('items.partType');
        });
    }

    /**
     * Izvrši transfer (kompletan proces)
     */
    public function executeTransfer(int $transferId, int $userId): PartTransfer
    {
        return DB::transaction(function () use ($transferId, $userId) {
            $transfer = PartTransfer::with('items.partType')->lockForUpdate()->findOrFail($transferId);

            if ($transfer->status !== PartTransfer::STATUS_PENDING) {
                throw new Exception("Transfer nije u PENDING statusu i ne može biti izvršen.");
            }

            // Za svaku stavku transfera
            foreach ($transfer->items as $item) {
                $this->transferItem(
                    $item->part_type_id,
                    $transfer->source_lokacija_id,
                    $transfer->destination_lokacija_id,
                    $item->kolicina,
                    $userId,
                    $transfer->transfer_broj
                );
            }

            // Ažuriraj status transfera
            $transfer->update([
                'status' => PartTransfer::STATUS_COMPLETED,
                'odobrio_korisnik_id' => $userId,
                'completed_at' => now(),
            ]);

            return $transfer->fresh();
        });
    }

    /**
     * Transferuj jedan deo između dve lokacije
     */
    protected function transferItem(
        int $partTypeId,
        int $sourceLocationId,
        int $destinationLocationId,
        int $quantity,
        int $userId,
        string $dokument
    ): void {
        // Lock oba stock zapisa
        $sourceStock = PartStock::where('part_type_id', $partTypeId)
            ->where('lokacija_id', $sourceLocationId)
            ->lockForUpdate()
            ->first();

        if (!$sourceStock || $sourceStock->kolicina_dostupna < $quantity) {
            throw new Exception("Nedovoljna količina na source lokaciji.");
        }

        // Umanji source
        $sourceStock->kolicina_ukupno -= $quantity;
        $sourceStock->save();

        // Logiraj TRANSFER_OUT
        PartMovement::create([
            'part_type_id' => $partTypeId,
            'lokacija_id' => $sourceLocationId,
            'tip_kretanja' => PartMovement::TIP_TRANSFER_OUT,
            'kolicina' => $quantity,
            'povezana_lokacija_id' => $destinationLocationId,
            'korisnik_id' => $userId,
            'dokument' => $dokument,
        ]);

        // Povećaj destination
        $destinationStock = PartStock::firstOrCreate(
            [
                'part_type_id' => $partTypeId,
                'lokacija_id' => $destinationLocationId,
            ],
            [
                'kolicina_ukupno' => 0,
                'kolicina_rezervisana' => 0,
            ]
        );

        $destinationStock->kolicina_ukupno += $quantity;
        $destinationStock->save();

        // Logiraj TRANSFER_IN
        PartMovement::create([
            'part_type_id' => $partTypeId,
            'lokacija_id' => $destinationLocationId,
            'tip_kretanja' => PartMovement::TIP_TRANSFER_IN,
            'kolicina' => $quantity,
            'povezana_lokacija_id' => $sourceLocationId,
            'korisnik_id' => $userId,
            'dokument' => $dokument,
        ]);
    }

    /**
     * Otkaži transfer
     */
    public function cancelTransfer(int $transferId, int $userId): PartTransfer
    {
        return DB::transaction(function () use ($transferId, $userId) {
            $transfer = PartTransfer::lockForUpdate()->findOrFail($transferId);

            if (!$transfer->canBeCancelled()) {
                throw new Exception("Transfer ne može biti otkazan.");
            }

            $transfer->update([
                'status' => PartTransfer::STATUS_CANCELLED,
                'odobrio_korisnik_id' => $userId,
            ]);

            return $transfer;
        });
    }

    /**
     * Dobij pending transfere za lokaciju
     */
    public function getPendingTransfersForLocation(int $locationId)
    {
        return PartTransfer::with(['items.partType', 'sourceLocation', 'destinationLocation', 'creator'])
            ->where(function ($q) use ($locationId) {
                $q->where('source_lokacija_id', $locationId)
                    ->orWhere('destination_lokacija_id', $locationId);
            })
            ->where('status', PartTransfer::STATUS_PENDING)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}