<?php

// app/PartsInventory/Services/ReservationService.php
namespace App\PartsInventory\Services;

use App\Models\PartReservation;
use App\Models\PartMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ReservationService
{
    protected PartStockService $stockService;

    public function __construct(PartStockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Kreiraj rezervaciju
     */
    public function createReservation(
        int $partTypeId,
        int $locationId,
        int $quantity,
        int $userId,
        ?int $workOrderId = null,
        ?\DateTime $reservedUntil = null
    ): PartReservation {
        return DB::transaction(function () use ($partTypeId, $locationId, $quantity, $userId, $workOrderId, $reservedUntil) {
            // Rezerviši količinu u stock
            $this->stockService->reservePart($partTypeId, $locationId, $quantity);

            // Kreiraj reservation zapis
            $reservation = PartReservation::create([
                'part_type_id' => $partTypeId,
                'lokacija_id' => $locationId,
                'kolicina' => $quantity,
                'korisnik_id' => $userId,
                'status' => PartReservation::STATUS_AKTIVNA,
                'radni_nalog_id' => $workOrderId,
                'rezervisano_do' => $reservedUntil,
            ]);

            // Logiraj kretanje
            PartMovement::create([
                'part_type_id' => $partTypeId,
                'lokacija_id' => $locationId,
                'tip_kretanja' => PartMovement::TIP_REZERVACIJA,
                'kolicina' => $quantity,
                'korisnik_id' => $userId,
                'dokument' => "REZ-{$reservation->id}",
                'napomena' => $workOrderId ? "Radni nalog: {$workOrderId}" : null,
            ]);

            return $reservation->load('partType', 'location');
        });
    }

    /**
     * Iskoristi rezervaciju (preuzimanje)
     */
    public function fulfillReservation(int $reservationId, int $userId): PartReservation
    {
        return DB::transaction(function () use ($reservationId, $userId) {
            $reservation = PartReservation::lockForUpdate()->findOrFail($reservationId);

            if ($reservation->status !== PartReservation::STATUS_AKTIVNA) {
                throw new Exception("Rezervacija nije aktivna.");
            }

            // Izvrši iz stock-a
            $this->stockService->fulfillReservation(
                $reservation->part_type_id,
                $reservation->lokacija_id,
                $reservation->kolicina,
                $userId,
                "Rezervacija: {$reservation->id}"
            );

            // Označi kao iskorišćenu
            $reservation->update([
                'status' => PartReservation::STATUS_ISKORISCENA,
            ]);

            return $reservation;
        });
    }

    /**
     * Otkaži rezervaciju
     */
    public function cancelReservation(int $reservationId, int $userId): PartReservation
    {
        return DB::transaction(function () use ($reservationId, $userId) {
            $reservation = PartReservation::lockForUpdate()->findOrFail($reservationId);

            if ($reservation->status !== PartReservation::STATUS_AKTIVNA) {
                throw new Exception("Rezervacija nije aktivna.");
            }

            // Oslobodi iz stock-a
            $this->stockService->releaseReservation(
                $reservation->part_type_id,
                $reservation->lokacija_id,
                $reservation->kolicina
            );

            // Označi kao otkazanu
            $reservation->update([
                'status' => PartReservation::STATUS_OTKAZANA,
            ]);

            // Logiraj povrat
            PartMovement::create([
                'part_type_id' => $reservation->part_type_id,
                'lokacija_id' => $reservation->lokacija_id,
                'tip_kretanja' => PartMovement::TIP_POVRAT,
                'kolicina' => $reservation->kolicina,
                'korisnik_id' => $userId,
                'dokument' => "REZ-{$reservation->id}",
                'napomena' => "Otkazana rezervacija",
            ]);

            return $reservation;
        });
    }

    /**
     * Automatsko otkazivanje isteklih rezervacija
     */
    public function cancelExpiredReservations(): int
    {
        $expiredReservations = PartReservation::expired()->get();
        $count = 0;

        foreach ($expiredReservations as $reservation) {
            try {
                $this->cancelReservation($reservation->id, $reservation->korisnik_id);
                $count++;
            } catch (Exception $e) {
                // Log greške ali nastavi
                Log::error("Failed to cancel expired reservation {$reservation->id}: {$e->getMessage()}");
            }
        }

        return $count;
    }
}