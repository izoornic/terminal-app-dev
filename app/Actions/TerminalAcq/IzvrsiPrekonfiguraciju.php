<?php

namespace App\Actions\TerminalAcq;

use App\Actions\Terminali\SelectedTerminalInfo;
use App\Models\Lokacija;
use App\Models\TerminalAcq;
use App\Models\TerminalAcqHistory;
use App\Models\TerminalLokacija;
use App\Models\TerminalStatusTip;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * "Izvrši prekonfiguraciju": tehničar je prekonfigurisao uređaj starog TID-a na novi TID.
 *
 * U jednoj transakciji: istorija oba TID-a; uređaj se premešta na lokaciju novog TID-a
 * ako je druga (status uređaja se ne menja, jer uređaj fizički ostaje na mestu); MID iz
 * zahteva (zahtev_acq_mid) zamenjuje MID lokacije; stari TID se zatvara kao
 * Prekonfigurisan sa svojom poslednjom lokacijom, a novi dobija uređaj.
 */
class IzvrsiPrekonfiguraciju
{
    /**
     * @return list<string> obaveštenja (npr. zamenjen MID)
     *
     * @throws RuntimeException kad novi TID ne čeka prekonfiguraciju ili stari TID nema uređaj
     */
    public function izvrsi(TerminalAcq $novi, User $korisnik): array
    {
        $cekaId = TerminalStatusTip::idPoNazivu(TerminalStatusTip::CEKA_PREKONFIGURACIJU);

        if ($novi->terminalId !== null || $novi->terminal_statusId !== $cekaId || $novi->lokacijaId === null) {
            throw new RuntimeException("TID {$novi->acq_tid} ne čeka prekonfiguraciju.");
        }

        $stari = $novi->prethodni;
        if ($stari === null || $stari->terminalId === null) {
            throw new RuntimeException('Stari TID '.($stari?->acq_tid ?? '').' nema uređaj; prekonfiguracija ne može da se izvrši.');
        }

        $terminalLokacija = TerminalLokacija::query()->where('terminalId', $stari->terminalId)->first();
        if ($terminalLokacija === null) {
            throw new RuntimeException('Uređaj starog TID-a nema red u terminal_lokacijas.');
        }

        $obavestenja = [];

        DB::transaction(function () use ($novi, $stari, $terminalLokacija, $korisnik, &$obavestenja): void {
            TerminalAcqHistory::createNewHistory($stari);
            TerminalAcqHistory::createNewHistory($novi);

            $staraLokacijaId = $terminalLokacija->lokacijaId;
            if ($staraLokacijaId !== $novi->lokacijaId) {
                if (SelectedTerminalInfo::terminalImaLicencu($terminalLokacija->id)) {
                    throw new RuntimeException('Uređaj ima aktivnu licencu i ne može se premestiti.');
                }

                TerminalLokacija::premestiTerminale([$terminalLokacija->id], $novi->lokacijaId, now(), $terminalLokacija->terminal_statusId, null);
            }

            if ($novi->zahtev_acq_mid !== null) {
                $lokacija = Lokacija::findOrFail($novi->lokacijaId);
                $obavestenja[] = 'MID lokacije zamenjen: '.($lokacija->acq_mid ?? '—')." → {$novi->zahtev_acq_mid}.";
                $lokacija->acq_mid = $novi->zahtev_acq_mid;
                $lokacija->save();
            }

            $stari->terminalId = null;
            $stari->terminal_statusId = TerminalStatusTip::idPoNazivu(TerminalStatusTip::PREKONFIGURISAN);
            $stari->lokacijaId = $staraLokacijaId;
            $stari->korisnikId = $korisnik->id;
            $stari->korisnikIme = $korisnik->name;
            $stari->save();

            $novi->terminalId = $terminalLokacija->terminalId;
            $novi->terminal_statusId = null;
            $novi->lokacijaId = null;
            $novi->zahtev_acq_mid = null;
            $novi->korisnikId = $korisnik->id;
            $novi->korisnikIme = $korisnik->name;
            $novi->save();
        });

        return $obavestenja;
    }
}
