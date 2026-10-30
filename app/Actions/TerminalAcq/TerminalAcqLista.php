<?php

namespace App\Actions\TerminalAcq;

use App\Models\TerminalAcq;
use App\Models\TerminalStatusTip;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

/**
 * Upit za listu HALK TID-ova: efektivni status i lokacija (od uređaja dok je pod TID-om,
 * inače sopstveni), podaci trgovca sa te lokacije i SN uređaja.
 */
class TerminalAcqLista
{
    public const FILTER_CEKA_UREDJAJ = 'ceka_uredjaj';

    public const FILTER_CEKA_PREKONFIGURACIJU = 'ceka_prekonfiguraciju';

    public const FILTER_AKTIVNI = 'aktivni';

    public const FILTER_ZATVORENI = 'zatvoreni';

    private const EFEKTIVNI_STATUS = 'COALESCE(terminal_lokacijas.terminal_statusId, terminal_acq.terminal_statusId)';

    private const EFEKTIVNA_LOKACIJA = 'COALESCE(terminal_lokacijas.lokacijaId, terminal_acq.lokacijaId)';

    /**
     * @return array<string, string>
     */
    public static function brziFilteri(): array
    {
        return [
            self::FILTER_AKTIVNI => 'Aktivni (sa uređajem)',
            self::FILTER_CEKA_UREDJAJ => 'Čeka uređaj',
            self::FILTER_CEKA_PREKONFIGURACIJU => 'Čeka prekonfiguraciju',
            self::FILTER_ZATVORENI => 'Zatvoreni',
        ];
    }

    /**
     * Samo statusi koje TID-ovi stvarno imaju (efektivni status), sa brojem TID-ova,
     * za filter na stranici. Redosled kao u šifarniku.
     *
     * @return array<int, string> statusId => "naziv (broj)"
     */
    public function statusiUpotrebljeni(): array
    {
        $brojPoStatusu = TerminalAcq::query()
            ->leftJoin('terminal_lokacijas', 'terminal_lokacijas.terminalId', '=', 'terminal_acq.terminalId')
            ->selectRaw(self::EFEKTIVNI_STATUS.' as statusId, COUNT(*) as broj')
            ->groupByRaw(self::EFEKTIVNI_STATUS)
            ->pluck('broj', 'statusId');

        $statusi = [];
        foreach (TerminalStatusTip::tidStatusList() as $id => $naziv) {
            if ($brojPoStatusu->has($id)) {
                $statusi[$id] = "{$naziv} ({$brojPoStatusu->get($id)})";
            }
        }

        return $statusi;
    }

    /**
     * @param  array{tid?: string, sn?: string, trgovac?: string, pib?: string, mid?: string, statusId?: int|string|null, brzi?: string|null, testni?: bool|string|null}  $filteri
     * @return Builder<TerminalAcq>
     */
    public function upit(array $filteri = []): Builder
    {
        $upit = TerminalAcq::query()
            ->leftJoin('terminal_lokacijas', 'terminal_lokacijas.terminalId', '=', 'terminal_acq.terminalId')
            ->leftJoin('lokacijas', fn (JoinClause $join) => $join->on('lokacijas.id', '=', DB::raw(self::EFEKTIVNA_LOKACIJA)))
            ->leftJoin('terminals', 'terminals.id', '=', 'terminal_acq.terminalId')
            ->select('terminal_acq.*')
            ->selectRaw(self::EFEKTIVNI_STATUS.' as efektivni_statusId')
            ->selectRaw(self::EFEKTIVNA_LOKACIJA.' as efektivna_lokacijaId')
            ->addSelect([
                'lokacijas.l_naziv as lokacija_naziv',
                'lokacijas.mesto as lokacija_mesto',
                'lokacijas.pib as lokacija_pib',
                'lokacijas.acq_mid as lokacija_acq_mid',
                'terminals.sn as sn',
            ]);

        $this->tekst($upit, 'terminal_acq.acq_tid', $filteri['tid'] ?? '');
        $this->tekst($upit, 'terminals.sn', $filteri['sn'] ?? '');
        $this->tekst($upit, 'lokacijas.pib', $filteri['pib'] ?? '');
        $this->tekst($upit, 'lokacijas.acq_mid', $filteri['mid'] ?? '');

        $trgovac = trim((string) ($filteri['trgovac'] ?? ''));
        if ($trgovac !== '') {
            $upit->where(fn (Builder $uslov) => $uslov
                ->where('lokacijas.l_naziv', 'like', '%'.$trgovac.'%')
                ->orWhere('terminal_acq.naziv_na_slipu', 'like', '%'.$trgovac.'%'));
        }

        if (($filteri['statusId'] ?? null) !== null && $filteri['statusId'] !== '') {
            $upit->whereRaw(self::EFEKTIVNI_STATUS.' = ?', [(int) $filteri['statusId']]);
        }

        if (($filteri['testni'] ?? null) !== null && $filteri['testni'] !== '') {
            $upit->where('terminal_acq.testni', (bool) $filteri['testni']);
        }

        match ($filteri['brzi'] ?? null) {
            self::FILTER_AKTIVNI => $upit->whereNotNull('terminal_acq.terminalId'),
            self::FILTER_CEKA_UREDJAJ => $upit->whereNull('terminal_acq.terminalId')
                ->where('terminal_acq.terminal_statusId', TerminalStatusTip::idPoNazivu(TerminalStatusTip::ZA_INSTALACIJU)),
            self::FILTER_CEKA_PREKONFIGURACIJU => $upit->whereNull('terminal_acq.terminalId')
                ->where('terminal_acq.terminal_statusId', TerminalStatusTip::idPoNazivu(TerminalStatusTip::CEKA_PREKONFIGURACIJU)),
            self::FILTER_ZATVORENI => $upit->whereNull('terminal_acq.terminalId')
                ->whereIn('terminal_acq.terminal_statusId', TerminalStatusTip::query()->where('ts_za_uredjaj', false)
                    ->where('ts_naziv', '!=', TerminalStatusTip::CEKA_PREKONFIGURACIJU)->pluck('id')),
            default => null,
        };

        return $upit->orderByDesc('terminal_acq.acq_tid');
    }

    /**
     * @param  Builder<TerminalAcq>  $upit
     */
    private function tekst(Builder $upit, string $kolona, mixed $vrednost): void
    {
        $vrednost = trim((string) $vrednost);
        if ($vrednost !== '') {
            $upit->where($kolona, 'like', '%'.$vrednost.'%');
        }
    }
}
