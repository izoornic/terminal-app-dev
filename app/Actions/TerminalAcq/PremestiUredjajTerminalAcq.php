<?php

namespace App\Actions\TerminalAcq;

use App\Actions\Terminali\SelectedTerminalInfo;
use App\Models\Lokacija;
use App\Models\TerminalAcq;
use App\Models\TerminalAcqHistory;
use App\Models\TerminalLokacija;
use App\Models\TerminalLokacijaHistory;
use App\Models\TerminalStatusTip;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Premeštanje uređaja pod aktivnim TID-om na drugu lokaciju korisnika terminala: TID
 * ostaje isti i nasleđuje novu lokaciju (ACQ terminali → "Premesti uređaj", halk:uvoz).
 * U servis ili magacin uređaj ide samo kroz deinstalaciju, da ne ostane TID u magacinu.
 */
class PremestiUredjajTerminalAcq
{
    public const LOKACIJA_TIP_KORISNIK = 3;

    /**
     * @throws RuntimeException kad TID nema uređaj, ili ciljna lokacija / status nisu dozvoljeni
     */
    public function izvrsi(TerminalAcq $acq, int $lokacijaId, int $statusUredjajaId, User $korisnik): void
    {
        if ($acq->terminalId === null) {
            throw new RuntimeException("TID {$acq->acq_tid} nema uređaj.");
        }

        $ciljna = Lokacija::query()->where('lokacija_tipId', self::LOKACIJA_TIP_KORISNIK)->find($lokacijaId);
        if ($ciljna === null) {
            throw new RuntimeException('Izaberite lokaciju korisnika terminala.');
        }

        if (! array_key_exists($statusUredjajaId, TerminalStatusTip::tipoviList())) {
            throw new RuntimeException('Izaberite status uređaja.');
        }

        $terminalLokacija = TerminalLokacija::query()->where('terminalId', $acq->terminalId)->first();
        if ($terminalLokacija === null) {
            throw new RuntimeException('Uređaj nema red u terminal_lokacijas.');
        }

        if ($terminalLokacija->lokacijaId === $ciljna->id) {
            throw new RuntimeException('Uređaj je već na toj lokaciji.');
        }

        DB::transaction(function () use ($acq, $ciljna, $statusUredjajaId, $terminalLokacija, $korisnik): void {
            TerminalAcqHistory::createNewHistory($acq);
            $this->premesti($terminalLokacija, $ciljna->id, $statusUredjajaId, $korisnik, now());

            $acq->korisnikId = $korisnik->id;
            $acq->korisnikIme = $korisnik->name;
            $acq->save();
        });
    }

    /**
     * Upozorenja pre potvrde: ciljna lokacija je druga firma (PIB) ili ima drugi MID.
     *
     * @return list<string>
     */
    public static function upozorenja(TerminalAcq $acq, Lokacija $ciljna): array
    {
        $trenutna = $acq->efektivnaLokacija();
        $upozorenja = [];

        $stariPib = HalkNormalizator::pib($trenutna?->pib);
        $noviPib = HalkNormalizator::pib($ciljna->pib);
        if ($stariPib !== null && $noviPib !== null && $stariPib !== $noviPib) {
            $upozorenja[] = "Ciljna lokacija ima drugi PIB ({$stariPib} → {$noviPib}). Za drugu firmu banka obično izdaje novi TID (prekonfiguracija).";
        }

        $stariMid = HalkNormalizator::mid($trenutna?->acq_mid);
        $noviMid = HalkNormalizator::mid($ciljna->acq_mid);
        if ($stariMid !== null && $noviMid !== null && $stariMid !== $noviMid) {
            $upozorenja[] = "Ciljna lokacija ima drugi MID ({$stariMid} → {$noviMid}).";
        }

        return $upozorenja;
    }

    /**
     * Premeštanje uređaja sa istorijom, kao TerminalLokacija::premestiTerminale, ali sa
     * autorom iz parametra (radi i iz komande, bez prijavljenog korisnika). Distributer se ne menja.
     *
     * @throws RuntimeException kad uređaj ima licencu
     */
    public function premesti(TerminalLokacija $terminalLokacija, int $lokacijaId, int $statusId, User $korisnik, CarbonInterface $datum): void
    {
        if (SelectedTerminalInfo::terminalImaLicencu($terminalLokacija->id)) {
            throw new RuntimeException('Uređaj ima aktivnu licencu i ne može se premestiti.');
        }

        TerminalLokacijaHistory::createNewHistory($terminalLokacija->id);

        $terminalLokacija->terminal_campagin_id = null;
        $terminalLokacija->terminal_statusId = $statusId;
        $terminalLokacija->lokacijaId = $lokacijaId;
        $terminalLokacija->korisnikId = $korisnik->id;
        $terminalLokacija->korisnikIme = $korisnik->name;
        $terminalLokacija->updated_at = $datum;
        $terminalLokacija->save();
    }
}
