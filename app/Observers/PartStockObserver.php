<?php

// app/Observers/PartStockObserver.php
namespace App\Observers;

use App\Models\PartStock;
use Illuminate\Support\Facades\Log;

class PartStockObserver
{
    /**
     * Handle before update event.
     */
    public function updating(PartStock $stock): void
    {
        // Log promene stanja
        if ($stock->isDirty('kolicina_ukupno') || $stock->isDirty('kolicina_rezervisana')) {
            Log::info('PartStock updating', [
                'id' => $stock->id,
                'part_type_id' => $stock->part_type_id,
                'lokacija_id' => $stock->lokacija_id,
                'old_ukupno' => $stock->getOriginal('kolicina_ukupno'),
                'new_ukupno' => $stock->kolicina_ukupno,
                'old_rezervisano' => $stock->getOriginal('kolicina_rezervisana'),
                'new_rezervisano' => $stock->kolicina_rezervisana,
            ]);
        }
    }

    /**
     * Handle after update event.
     */
    public function updated(PartStock $stock): void
    {
        // Proveri da li je zaliha ispod minimuma
        if ($stock->kolicina_dostupna <= $stock->partType->min_kolicina) {
            // Opciono: trigger notifikaciju
            // event(new LowStockAlert($stock));
            
            Log::warning('Low stock alert', [
                'part_type' => $stock->partType->naziv,
                'location' => $stock->location->naziv,
                'dostupno' => $stock->kolicina_dostupna,
                'minimum' => $stock->partType->min_kolicina,
            ]);
        }
    }
}