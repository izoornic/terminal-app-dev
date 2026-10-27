<?php

namespace App\Actions\TerminalAcq;

use App\Actions\Terminali\SelectedTerminalInfo;
use App\Models\Terminal;
use App\Models\TerminalAcq;
use App\Models\TerminalAcqHistory;
use App\Models\TerminalLokacija;
use App\Models\TerminalStatusTip;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * "Dodela uređaja": TID koji čeka instalaciju dobija uređaj.
 *
 * Ako uređaj nije na lokaciji TID-a, premešta se postojećim tokom za terminale
 * (TerminalLokacija::premestiTerminale, sa istorijom) sa statusom "Za instalaciju".
 * Posle toga TID nasleđuje status i lokaciju od uređaja (terminal_statusId i
 * lokacijaId postaju null). Premeštanje beleži prijavljenog korisnika (auth()).
 */
class DodeliUredjajTerminalAcq
{
    /**
     * @return list<string> upozorenja (dodela je izvršena)
     *
     * @throws RuntimeException kad TID ili uređaj ne zadovoljavaju pravila
     */
    public function izvrsi(TerminalAcq $acq, string $sn, User $korisnik): array
    {
        $zaInstalacijuId = TerminalStatusTip::idPoNazivu(TerminalStatusTip::ZA_INSTALACIJU);

        if ($acq->terminalId !== null || $acq->terminal_statusId !== $zaInstalacijuId || $acq->lokacijaId === null) {
            throw new RuntimeException("TID {$acq->acq_tid} ne čeka instalaciju.");
        }

        $terminal = Terminal::query()->where('sn', trim($sn))->first();
        if ($terminal === null) {
            throw new RuntimeException("Uređaj sa SN {$sn} ne postoji.");
        }

        $terminalLokacija = TerminalLokacija::query()->where('terminalId', $terminal->id)->first();
        if ($terminalLokacija === null) {
            throw new RuntimeException("Uređaj {$terminal->sn} nema red u terminal_lokacijas.");
        }

        $aktivni = TerminalAcq::query()->where('terminalId', $terminal->id)->value('acq_tid');
        if ($aktivni !== null) {
            throw new RuntimeException("Uređaj {$terminal->sn} je već pod aktivnim TID-om {$aktivni}.");
        }

        $upozorenja = [];
        if ($acq->trazeni_terminal_tipId !== null && $acq->trazeni_terminal_tipId !== $terminal->terminal_tipId) {
            $upozorenja[] = "Model uređaja {$terminal->sn} nije model iz zahteva banke.";
        }

        DB::transaction(function () use ($acq, $terminal, $terminalLokacija, $korisnik, $zaInstalacijuId): void {
            if ($terminalLokacija->lokacijaId !== $acq->lokacijaId) {
                if (SelectedTerminalInfo::terminalImaLicencu($terminalLokacija->id)) {
                    throw new RuntimeException("Uređaj {$terminal->sn} ima aktivnu licencu i ne može se premestiti.");
                }

                TerminalLokacija::premestiTerminale([$terminalLokacija->id], $acq->lokacijaId, now(), $zaInstalacijuId, null);
            }

            TerminalAcqHistory::createNewHistory($acq);

            $acq->terminalId = $terminal->id;
            $acq->terminal_statusId = null;
            $acq->lokacijaId = null;
            $acq->korisnikId = $korisnik->id;
            $acq->korisnikIme = $korisnik->name;
            $acq->save();
        });

        return $upozorenja;
    }
}
