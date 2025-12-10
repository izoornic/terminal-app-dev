<?php

// app/PartsInventory/Services/InventoryService.php
namespace App\PartsInventory\Services;

use App\Models\PartInventoryAudit;
use App\Models\PartAuditItem;
use App\Models\PartStock;
use App\Models\PartMovement;
use Illuminate\Support\Facades\DB;
use Exception;

class InventoryService
{
    protected PartStockService $stockService;

    public function __construct(PartStockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Započni inventuru na lokaciji
     */
    public function startAudit(int $locationId, int $userId, ?string $napomena = null): PartInventoryAudit
    {
        return DB::transaction(function () use ($locationId, $userId, $napomena) {
            // Proveri da li već postoji aktivna inventura na toj lokaciji
            $existingAudit = PartInventoryAudit::where('lokacija_id', $locationId)
                ->where('status', PartInventoryAudit::STATUS_IN_PROGRESS)
                ->first();

            if ($existingAudit) {
                throw new Exception("Inventura je već u toku na ovoj lokaciji.");
            }

            // Kreiraj inventuru
            $audit = PartInventoryAudit::create([
                'lokacija_id' => $locationId,
                'korisnik_id' => $userId,
                'status' => PartInventoryAudit::STATUS_IN_PROGRESS,
                'started_at' => now(),
                'napomena' => $napomena,
            ]);

            // Automatski kreiraj stavke za sve delove na lokaciji
            $stocks = PartStock::where('lokacija_id', $locationId)->get();

            foreach ($stocks as $stock) {
                PartAuditItem::create([
                    'audit_id' => $audit->id,
                    'part_type_id' => $stock->part_type_id,
                    'expected_kolicina' => $stock->kolicina_ukupno,
                    'actual_kolicina' => 0, // Biće popunjeno tokom inventure
                ]);
            }

            return $audit->load('items.partType');
        });
    }

    /**
     * Ažuriraj stavku inventure
     */
    public function updateAuditItem(int $auditItemId, int $actualQuantity, ?string $napomena = null): PartAuditItem
    {
        $item = PartAuditItem::findOrFail($auditItemId);
        
        $item->update([
            'actual_kolicina' => $actualQuantity,
            'napomena' => $napomena,
        ]);

        return $item->fresh();
    }

    /**
     * Završi inventuru i uskladi stanje
     */
    public function completeAudit(int $auditId, int $userId, bool $reconcile = true): PartInventoryAudit
    {
        return DB::transaction(function () use ($auditId, $userId, $reconcile) {
            $audit = PartInventoryAudit::with('items.partType')->lockForUpdate()->findOrFail($auditId);

            if ($audit->status !== PartInventoryAudit::STATUS_IN_PROGRESS) {
                throw new Exception("Inventura nije u toku.");
            }

            // Ako je reconcile = true, ažuriraj stock na osnovu stvarnog stanja
            if ($reconcile) {
                foreach ($audit->items as $item) {
                    if ($item->hasDifference()) {
                        $this->reconcileItem($item, $userId);
                    }
                }
            }

            // Označi kao završenu
            $audit->update([
                'status' => PartInventoryAudit::STATUS_COMPLETED,
                'completed_at' => now(),
            ]);

            return $audit->fresh();
        });
    }

    /**
     * Uskladi pojedinačnu stavku
     */
    protected function reconcileItem(PartAuditItem $item, int $userId): void
    {
        $stock = PartStock::where('part_type_id', $item->part_type_id)
            ->where('lokacija_id', $item->audit->lokacija_id)
            ->lockForUpdate()
            ->first();

        if (!$stock) {
            return;
        }

        $razlika = $item->razlika;

        // Ažuriraj stock
        $stock->kolicina_ukupno = $item->actual_kolicina;
        $stock->save();

        // Logiraj kretanje
        $tipKretanja = $razlika > 0 ? PartMovement::TIP_ULAZ : PartMovement::TIP_IZLAZ;
        
        PartMovement::create([
            'part_type_id' => $item->part_type_id,
            'lokacija_id' => $item->audit->lokacija_id,
            'tip_kretanja' => $tipKretanja,
            'kolicina' => abs($razlika),
            'korisnik_id' => $userId,
            'dokument' => "INV-{$item->audit->id}",
            'napomena' => "Usklađivanje inventure: {$item->napomena}",
        ]);
    }
}