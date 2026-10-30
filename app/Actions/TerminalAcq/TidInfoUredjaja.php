<?php

namespace App\Actions\TerminalAcq;

use App\Models\TerminalAcq;
use App\Models\TerminalLokacija;

/**
 * Podaci aktivnog ACQ TID-a uređaja za prikaz uz podatke o terminalu
 * (komponenta "Terminal info", stranica tiketa).
 */
class TidInfoUredjaja
{
    /**
     * @return array{acq_tid: string, naziv_na_slipu: ?string, komunikacija: ?string, testni: bool, datum_instalacije: ?string, acq_mid: ?string, mcc: ?string, naslednik: ?string}|null
     */
    public static function zaTerminalLokaciju(int|string|null $terminalLokacijaId): ?array
    {
        $terminalId = $terminalLokacijaId ? TerminalLokacija::query()->whereKey($terminalLokacijaId)->value('terminalId') : null;
        $acq = $terminalId !== null
            ? TerminalAcq::query()->with('naslednik')->where('terminalId', $terminalId)->first()
            : null;
        if ($acq === null) {
            return null;
        }

        $lokacija = $acq->efektivnaLokacija();

        return [
            'acq_tid' => $acq->acq_tid,
            'naziv_na_slipu' => $acq->naziv_na_slipu,
            'komunikacija' => $acq->komunikacija,
            'testni' => $acq->testni,
            'datum_instalacije' => $acq->datum_instalacije?->format('d.m.Y'),
            'acq_mid' => $lokacija?->acq_mid,
            'mcc' => $lokacija?->mcc,
            'naslednik' => $acq->naslednik?->acq_tid,
        ];
    }
}
