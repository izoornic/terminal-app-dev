<?php

// app/Observers/PartMovementObserver.php
namespace App\Observers;

use App\Models\PartMovement;
use Illuminate\Support\Facades\Log;

class PartMovementObserver
{
    /**
     * Handle after create event.
     * Sync je već urađen u Service layeru, ovde samo logujemo
     */
    public function created(PartMovement $movement): void
    {
        Log::info('PartMovement created', [
            'id' => $movement->id,
            'part_type_id' => $movement->part_type_id,
            'tip_kretanja' => $movement->tip_kretanja,
            'kolicina' => $movement->kolicina,
            'lokacija_id' => $movement->lokacija_id,
        ]);

        // Opciono: emit event za notifikacije
        // event(new PartMovementCreated($movement));
    }
}