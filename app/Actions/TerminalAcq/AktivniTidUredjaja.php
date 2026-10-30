<?php

namespace App\Actions\TerminalAcq;

use App\Models\TerminalAcq;
use App\Models\TerminalLokacija;

/**
 * Uređaj pod aktivnim TID-om se ne premešta sa stranica za terminale: TID i uređaj bi
 * otišli svaki na svoju stranu. Takav uređaj se premešta kroz "ACQ terminali"
 * (deinstalacija, prekonfiguracija).
 */
class AktivniTidUredjaja
{
    /**
     * @param  array<int, int|string>  $terminalLokacijaIds  id-jevi terminal_lokacijas redova (kao na stranici Terminali)
     * @return list<string> aktivni TID-ovi izabranih uređaja
     */
    public static function zaTerminalLokacije(array $terminalLokacijaIds): array
    {
        if ($terminalLokacijaIds === []) {
            return [];
        }

        return TerminalAcq::query()
            ->whereIn('terminalId', TerminalLokacija::query()->whereIn('id', $terminalLokacijaIds)->select('terminalId'))
            ->orderBy('acq_tid')
            ->pluck('acq_tid')
            ->all();
    }
}
