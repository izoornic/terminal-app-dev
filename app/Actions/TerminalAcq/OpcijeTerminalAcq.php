<?php

namespace App\Actions\TerminalAcq;

use App\Models\TerminalAcq;
use App\Models\TerminalAcqOpcija;
use App\Models\TerminalStatusTip;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Opcije ACQ TID-a (šifarnik na stranici acq-opcije): uključivanje i isključivanje sa
 * detalja TID-a i prenos na novi TID kod prekonfiguracije. Veza pamti ko je i kada
 * poslednji menjao opciju; zatvoren TID se ne menja.
 */
class OpcijeTerminalAcq
{
    /**
     * @throws RuntimeException kad je TID zatvoren ili se uključuje neaktivna opcija
     */
    public function postavi(TerminalAcq $acq, int $opcijaId, bool $ukljucena, User $korisnik): TerminalAcqOpcija
    {
        if (self::jeZatvoren($acq)) {
            throw new RuntimeException("TID {$acq->acq_tid} je zatvoren; opcije se ne menjaju.");
        }

        $opcija = TerminalAcqOpcija::query()->findOrFail($opcijaId);
        if ($ukljucena && ! $opcija->aktivna) {
            throw new RuntimeException("Opcija {$opcija->naziv} nije aktivna.");
        }

        $acq->opcijeVeze()->syncWithoutDetaching([$opcija->id => ['ukljucena' => $ukljucena, 'korisnikId' => $korisnik->id]]);

        return $opcija;
    }

    /**
     * Novi TID iz zahteva za prekonfiguraciju preuzima uključene opcije starog TID-a.
     */
    public function prenesi(TerminalAcq $stari, TerminalAcq $novi, User $korisnik): void
    {
        $opcije = $stari->opcije()->pluck('terminal_acq_opcijas.id');
        if ($opcije->isEmpty()) {
            return;
        }

        DB::transaction(fn () => $novi->opcijeVeze()->syncWithoutDetaching(
            $opcije->mapWithKeys(fn (int $id): array => [$id => ['ukljucena' => true, 'korisnikId' => $korisnik->id]])->all()
        ));
    }

    /**
     * Zatvoren je TID bez uređaja koji ne čeka ni uređaj ni prekonfiguraciju (Deinstaliran, Prekonfigurisan, Opozvan).
     */
    public static function jeZatvoren(TerminalAcq $acq): bool
    {
        return $acq->terminalId === null && ! in_array($acq->terminal_statusId, [
            TerminalStatusTip::idPoNazivu(TerminalStatusTip::ZA_INSTALACIJU),
            TerminalStatusTip::idPoNazivu(TerminalStatusTip::CEKA_PREKONFIGURACIJU),
        ], true);
    }
}
