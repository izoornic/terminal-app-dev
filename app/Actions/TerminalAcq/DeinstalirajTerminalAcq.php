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
 * Deinstalacija: u istom koraku TID se zatvara (Deinstaliran, sa poslednjom lokacijom
 * uređaja), a uređaj se postojećim tokom premešta u izabrani servisni centar ili magacin
 * sa izabranim statusom uređaja.
 */
class DeinstalirajTerminalAcq
{
    /**
     * Tipovi lokacija u koje uređaj može da ode: Servisni centar (1) i Magacin (2).
     *
     * @var list<int>
     */
    public const CILJNI_TIPOVI_LOKACIJE = [1, 2];

    /**
     * @throws RuntimeException kad TID nema uređaj, ili ciljna lokacija / status nisu dozvoljeni
     */
    public function izvrsi(TerminalAcq $acq, int $lokacijaId, int $statusUredjajaId, User $korisnik): void
    {
        if ($acq->terminalId === null) {
            throw new RuntimeException("TID {$acq->acq_tid} nema uređaj.");
        }

        $ciljna = Lokacija::query()->whereIn('lokacija_tipId', self::CILJNI_TIPOVI_LOKACIJE)->find($lokacijaId);
        if ($ciljna === null) {
            throw new RuntimeException('Izaberite servisni centar ili magacin u koji ide uređaj.');
        }

        if (! array_key_exists($statusUredjajaId, TerminalStatusTip::tipoviList())) {
            throw new RuntimeException('Izaberite status uređaja.');
        }

        $terminalLokacija = TerminalLokacija::query()->where('terminalId', $acq->terminalId)->first();
        if ($terminalLokacija === null) {
            throw new RuntimeException('Uređaj nema red u terminal_lokacijas.');
        }

        DB::transaction(function () use ($acq, $ciljna, $statusUredjajaId, $terminalLokacija, $korisnik): void {
            TerminalAcqHistory::createNewHistory($acq);

            $poslednjaLokacijaId = $terminalLokacija->lokacijaId;

            if (SelectedTerminalInfo::terminalImaLicencu($terminalLokacija->id)) {
                throw new RuntimeException('Uređaj ima aktivnu licencu i ne može se premestiti.');
            }
            TerminalLokacija::premestiTerminale([$terminalLokacija->id], $ciljna->id, now(), $statusUredjajaId, null);

            $acq->terminalId = null;
            $acq->terminal_statusId = TerminalStatusTip::idPoNazivu(TerminalStatusTip::DEINSTALIRAN);
            $acq->lokacijaId = $poslednjaLokacijaId;
            $acq->korisnikId = $korisnik->id;
            $acq->korisnikIme = $korisnik->name;
            $acq->save();
        });
    }
}
