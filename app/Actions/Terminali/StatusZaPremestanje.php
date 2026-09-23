<?php

namespace App\Actions\Terminali;

use App\Models\TerminalLokacija;

/**
 * Status terminala koji se nudi u modalu za premeštanje više izabranih terminala.
 */
class StatusZaPremestanje
{
    /**
     * Status "Instaliran" iz terminal_status_tips.
     */
    public const PODRAZUMEVANI_STATUS = 2;

    /**
     * Status se uzima sa prvog izabranog terminal_lokacijas reda, a ako tog reda
     * nema (ili nije izabran nijedan terminal) vraća "Instaliran".
     *
     * @param  array<int, int|string>  $izabraniTerminali  id-evi terminal_lokacijas redova (tlid sa liste)
     */
    public static function premaPrvomIzabranom(array $izabraniTerminali): int
    {
        $prviIzabrani = reset($izabraniTerminali);

        if ($prviIzabrani === false) {
            return self::PODRAZUMEVANI_STATUS;
        }

        return (int) (TerminalLokacija::where('id', $prviIzabrani)->first()?->terminal_statusId
            ?? self::PODRAZUMEVANI_STATUS);
    }
}
