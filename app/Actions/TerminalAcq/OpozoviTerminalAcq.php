<?php

namespace App\Actions\TerminalAcq;

use App\Models\TerminalAcq;
use App\Models\TerminalAcqHistory;
use App\Models\TerminalStatusTip;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Opoziv: banka je odustala od TID-a koji još čeka uređaj ili prekonfiguraciju.
 * TID dobija status Opozvan i zadržava lokaciju; stari TID kod prekonfiguracije ostaje aktivan.
 */
class OpozoviTerminalAcq
{
    /**
     * @throws RuntimeException kad TID ne čeka uređaj ni prekonfiguraciju
     */
    public function izvrsi(TerminalAcq $acq, User $korisnik): void
    {
        $dozvoljeni = [
            TerminalStatusTip::idPoNazivu(TerminalStatusTip::ZA_INSTALACIJU),
            TerminalStatusTip::idPoNazivu(TerminalStatusTip::CEKA_PREKONFIGURACIJU),
        ];

        if ($acq->terminalId !== null || ! in_array($acq->terminal_statusId, $dozvoljeni, true)) {
            throw new RuntimeException("TID {$acq->acq_tid} se može opozvati samo dok čeka uređaj ili prekonfiguraciju.");
        }

        DB::transaction(function () use ($acq, $korisnik): void {
            TerminalAcqHistory::createNewHistory($acq);

            $acq->terminal_statusId = TerminalStatusTip::idPoNazivu(TerminalStatusTip::OPOZVAN);
            $acq->korisnikId = $korisnik->id;
            $acq->korisnikIme = $korisnik->name;
            $acq->save();
        });
    }
}
