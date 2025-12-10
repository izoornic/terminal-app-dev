<?php

// app/PartsInventory/Services/PartStockService.php
namespace App\PartsInventory\Services;

use App\Models\PartStock;
use App\Models\PartMovement;
use App\Models\PartType;
use Illuminate\Support\Facades\DB;
use Exception;

class PartStockService
{
    /**
     * Dodaj zalihu na određenu lokaciju (ULAZ)
     */
    public function addStock(
        int $partTypeId,
        int $locationId,
        int $quantity,
        int $userId,
        ?string $dokument = null,
        ?string $napomena = null
    ): PartStock {
        return DB::transaction(function () use ($partTypeId, $locationId, $quantity, $userId, $dokument, $napomena) {
            // Pronađi ili kreiraj stock zapis
            $stock = PartStock::firstOrCreate(
                [
                    'part_type_id' => $partTypeId,
                    'lokacija_id' => $locationId,
                ],
                [
                    'kolicina_ukupno' => 0,
                    'kolicina_rezervisana' => 0,
                ]
            );

            // Ažuriraj količinu
            $stock->kolicina_ukupno += $quantity;
            $stock->save();

            // Logiraj kretanje
            PartMovement::create([
                'part_type_id' => $partTypeId,
                'lokacija_id' => $locationId,
                'tip_kretanja' => PartMovement::TIP_ULAZ,
                'kolicina' => $quantity,
                'korisnik_id' => $userId,
                'dokument' => $dokument,
                'napomena' => $napomena,
            ]);

            return $stock->fresh();
        });
    }

    /**
     * Umanji zalihu sa lokacije (IZLAZ)
     */
    public function removeStock(
        int $partTypeId,
        int $locationId,
        int $quantity,
        int $userId,
        ?string $dokument = null,
        ?string $napomena = null
    ): PartStock {
        return DB::transaction(function () use ($partTypeId, $locationId, $quantity, $userId, $dokument, $napomena) {
            $stock = PartStock::where('part_type_id', $partTypeId)
                ->where('lokacija_id', $locationId)
                ->lockForUpdate()
                ->first();

            if (!$stock) {
                throw new Exception("Deo nije pronađen na ovoj lokaciji.");
            }

            if ($stock->kolicina_dostupna < $quantity) {
                throw new Exception("Nedovoljna dostupna količina. Dostupno: {$stock->kolicina_dostupna}, Potrebno: {$quantity}");
            }

            // Umanji ukupnu količinu
            $stock->kolicina_ukupno -= $quantity;
            $stock->save();

            // Logiraj kretanje
            PartMovement::create([
                'part_type_id' => $partTypeId,
                'lokacija_id' => $locationId,
                'tip_kretanja' => PartMovement::TIP_IZLAZ,
                'kolicina' => $quantity,
                'korisnik_id' => $userId,
                'dokument' => $dokument,
                'napomena' => $napomena,
            ]);

            return $stock->fresh();
        });
    }

    /**
     * Rezerviši deo
     */
    public function reservePart(
        int $partTypeId,
        int $locationId,
        int $quantity
    ): PartStock {
        return DB::transaction(function () use ($partTypeId, $locationId, $quantity) {
            $stock = PartStock::where('part_type_id', $partTypeId)
                ->where('lokacija_id', $locationId)
                ->lockForUpdate()
                ->first();

            if (!$stock || $stock->kolicina_dostupna < $quantity) {
                throw new Exception("Nedovoljna dostupna količina za rezervaciju.");
            }

            $stock->kolicina_rezervisana += $quantity;
            $stock->save();

            return $stock->fresh();
        });
    }

    /**
     * Oslobodi rezervaciju
     */
    public function releaseReservation(
        int $partTypeId,
        int $locationId,
        int $quantity
    ): PartStock {
        return DB::transaction(function () use ($partTypeId, $locationId, $quantity) {
            $stock = PartStock::where('part_type_id', $partTypeId)
                ->where('lokacija_id', $locationId)
                ->lockForUpdate()
                ->first();

            if (!$stock) {
                throw new Exception("Deo nije pronađen na ovoj lokaciji.");
            }

            $stock->kolicina_rezervisana -= $quantity;
            
            // Osiguraj da rezervisana količina ne može biti negativna
            if ($stock->kolicina_rezervisana < 0) {
                $stock->kolicina_rezervisana = 0;
            }
            
            $stock->save();

            return $stock->fresh();
        });
    }

    /**
     * Izvrši rezervaciju (preuzimanje nakon rezervacije)
     */
    public function fulfillReservation(
        int $partTypeId,
        int $locationId,
        int $quantity,
        int $userId,
        ?string $napomena = null
    ): PartStock {
        return DB::transaction(function () use ($partTypeId, $locationId, $quantity, $userId, $napomena) {
            $stock = PartStock::where('part_type_id', $partTypeId)
                ->where('lokacija_id', $locationId)
                ->lockForUpdate()
                ->first();

            if (!$stock) {
                throw new Exception("Deo nije pronađen na ovoj lokaciji.");
            }

            // Umanji i ukupno i rezervisano
            $stock->kolicina_ukupno -= $quantity;
            $stock->kolicina_rezervisana -= $quantity;
            
            // Osiguraj validnost
            if ($stock->kolicina_rezervisana < 0) {
                $stock->kolicina_rezervisana = 0;
            }
            
            $stock->save();

            // Logiraj kao IZLAZ
            PartMovement::create([
                'part_type_id' => $partTypeId,
                'lokacija_id' => $locationId,
                'tip_kretanja' => PartMovement::TIP_IZLAZ,
                'kolicina' => $quantity,
                'korisnik_id' => $userId,
                'napomena' => $napomena . ' (iz rezervacije)',
            ]);

            return $stock->fresh();
        });
    }

    /**
     * Proveri stanje zaliha na lokaciji
     */
    public function getStockLevel(int $partTypeId, int $locationId): array
    {
        $stock = PartStock::where('part_type_id', $partTypeId)
            ->where('lokacija_id', $locationId)
            ->first();

        if (!$stock) {
            return [
                'ukupno' => 0,
                'rezervisano' => 0,
                'dostupno' => 0,
            ];
        }

        return [
            'ukupno' => $stock->kolicina_ukupno,
            'rezervisano' => $stock->kolicina_rezervisana,
            'dostupno' => $stock->kolicina_dostupna,
        ];
    }

    /**
     * Dobij delove sa niskim stanjem
     */
    public function getLowStockParts(?int $locationId = null)
    {
        $query = PartStock::with(['partType', 'location'])
            ->whereHas('partType', function ($q) {
                $q->whereRaw('part_stocks.kolicina_dostupna <= part_types.min_kolicina');
            });

        if ($locationId) {
            $query->where('lokacija_id', $locationId);
        }

        return $query->get();
    }
}