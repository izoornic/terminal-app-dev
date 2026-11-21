<?php

namespace App\Actions\Licence;

use App\Http\Helpers;
use App\Models\LicencaNaplata;
use App\Models\LicencaParametarTerminal;
use App\Models\LicenceZaTerminal;
use Illuminate\Support\Facades\DB;

/**
 * Brisanje svih licenci terminala (stranica distributera, "Obriši licence"): parametri
 * licence, zatvaranje aktivne naplate i red u licence_za_terminals, sve u jednoj transakciji.
 *
 * Servisna licenca (licenca_poreklo = 2) i licenca bez aktivne naplate nemaju red u
 * licenca_naplatas, pa se za njih briše samo licenca i njeni parametri.
 */
class ObrisiLicenceTerminala
{
    /**
     * @return int broj obrisanih licenci
     */
    public function izvrsi(int $terminalLokacijaId): int
    {
        return DB::transaction(function () use ($terminalLokacijaId): int {
            $licence = LicenceZaTerminal::query()->where('terminal_lokacijaId', $terminalLokacijaId)->get();

            foreach ($licence as $licenca) {
                $kljuc = [
                    'terminal_lokacijaId' => $licenca->terminal_lokacijaId,
                    'distributerId' => $licenca->distributerId,
                    'licenca_distributer_cenaId' => $licenca->licenca_distributer_cenaId,
                ];

                LicencaParametarTerminal::deleteParametars($kljuc);
                $this->zatvoriNaplatu($kljuc);
                $licenca->delete();
            }

            return $licence->count();
        });
    }

    /**
     * @param  array{terminal_lokacijaId: int, distributerId: int, licenca_distributer_cenaId: int}  $kljuc
     */
    private function zatvoriNaplatu(array $kljuc): void
    {
        $naplata = LicencaNaplata::query()->where($kljuc)->where('aktivna', 1)->first();
        if ($naplata === null) {
            return;
        }

        $naplata->zaduzeno ??= 0;
        $naplata->datum_zaduzenja ??= Helpers::datumKalendarNow();
        $naplata->razduzeno ??= 0;
        $naplata->datum_razduzenja ??= Helpers::datumKalendarNow();
        $naplata->aktivna = 0;
        $naplata->save();
    }
}
