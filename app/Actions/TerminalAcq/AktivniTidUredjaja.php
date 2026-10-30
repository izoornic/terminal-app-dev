<?php

namespace App\Actions\TerminalAcq;

use App\Models\TerminalAcq;
use App\Models\TerminalLokacija;
use Illuminate\Support\Collection;

/**
 * Uređaj pod aktivnim TID-om se ne premešta sa stranica za terminale: TID i uređaj bi
 * otišli svaki na svoju stranu. Takav uređaj se premešta kroz "ACQ terminali"
 * (deinstalacija, prekonfiguracija).
 */
class AktivniTidUredjaja
{
    /**
     * Aktivni TID-ovi za uređaje u listi (stranice Terminali, Licence, Distributer boje
     * redove sa TID-om): jedan upit za celu stranu.
     *
     * @param  iterable<int|null>  $terminalIds  terminals.id
     * @return Collection<int, string> terminalId => acq_tid
     */
    public static function poUredjaju(iterable $terminalIds): Collection
    {
        $terminalIds = collect($terminalIds)->filter()->values();
        if ($terminalIds->isEmpty()) {
            return collect();
        }

        return TerminalAcq::query()->whereIn('terminalId', $terminalIds)->pluck('acq_tid', 'terminalId');
    }

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
