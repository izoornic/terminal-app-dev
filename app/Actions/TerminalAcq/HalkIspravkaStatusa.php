<?php

namespace App\Actions\TerminalAcq;

use App\Models\Lokacija;
use App\Models\Terminal;
use App\Models\TerminalLokacija;
use App\Models\TerminalLokacijaHistory;
use App\Models\TerminalStatusTip;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * Masovna ispravka statusa uređaja "Za instalaciju" → "Instaliran" za uređaje koje banka
 * vodi kao instalirane, a koji su u aplikaciji kod korisnika (lokacija tipa 3).
 *
 * Status je ostao "Za instalaciju" jer posle instalacije nije ažuriran (halk:provera,
 * kategorija 4). Iz sigurnosti se preskaču uređaji sa drugim PIB-om ili TID konfliktom.
 */
class HalkIspravkaStatusa
{
    private const LOKACIJA_TIP_KORISNIK = 3;

    /**
     * @param  list<array<string, mixed>>  $redovi  redovi iz HalkExcelCitac
     * @return array{ispravka: list<array<string, mixed>>, preskoceno: list<array<string, mixed>>}
     */
    public function kandidati(array $redovi): array
    {
        $zaInstalacijuId = TerminalStatusTip::idPoNazivu(TerminalStatusTip::ZA_INSTALACIJU);
        $instalirani = [];
        $aktivniTidPoSn = [];

        foreach ($redovi as $red) {
            $status = HalkNormalizator::statusReda($red)['status'];
            $sn = HalkNormalizator::ispravanSn($red['sn'] ?? null);
            $tid = HalkNormalizator::tid($red['acq_tid'] ?? null)['tid'];

            if ($sn !== null && $tid !== null && in_array($status, ['Instaliran', 'Za instalaciju', 'Testni'], true)) {
                $aktivniTidPoSn[$sn][$tid] = true;
            }

            if ($status === 'Instaliran' && $sn !== null) {
                $instalirani[] = [
                    'red' => $red['red'],
                    'tid' => $tid,
                    'sn' => $sn,
                    'pib_excel' => HalkNormalizator::pib($red['pib'] ?? null),
                    'datum_instalacije' => HalkNormalizator::datum($red['datum_instalacije'] ?? null),
                ];
            }
        }

        $terminali = Terminal::query()
            ->whereIn('sn', array_column($instalirani, 'sn'))
            ->get()
            ->keyBy(fn (Terminal $terminal): string => strtoupper($terminal->sn));
        $terminalLokacije = TerminalLokacija::query()
            ->whereIn('terminalId', $terminali->pluck('id'))
            ->where('terminal_statusId', $zaInstalacijuId)
            ->get()
            ->keyBy('terminalId');
        $lokacije = Lokacija::query()
            ->whereIn('id', $terminalLokacije->pluck('lokacijaId'))
            ->where('lokacija_tipId', self::LOKACIJA_TIP_KORISNIK)
            ->get()
            ->keyBy('id');

        $rezultat = ['ispravka' => [], 'preskoceno' => []];
        $vecIzabrani = [];

        foreach ($instalirani as $kandidat) {
            $terminalLokacija = $terminalLokacije->get($terminali->get($kandidat['sn'])?->id);
            $lokacija = $terminalLokacija !== null ? $lokacije->get($terminalLokacija->lokacijaId) : null;
            if ($lokacija === null) {
                continue;
            }

            $kandidat['terminal_lokacijaId'] = $terminalLokacija->id;
            $kandidat['lokacijaId'] = $lokacija->id;
            $kandidat['lokacija'] = $lokacija->l_naziv;
            $kandidat['pib_aplikacija'] = HalkNormalizator::pib($lokacija->pib);

            $razlog = match (true) {
                count($aktivniTidPoSn[$kandidat['sn']] ?? []) > 1 => 'Uređaj je aktivan pod više TID-ova u Excelu.',
                isset($vecIzabrani[$kandidat['sn']]) => 'Isti uređaj je već u listi iz drugog reda.',
                $kandidat['pib_aplikacija'] === null => 'Lokacija u aplikaciji nema PIB.',
                $kandidat['pib_excel'] !== $kandidat['pib_aplikacija'] => 'PIB se razlikuje od lokacije u aplikaciji (rešava halk:uvoz).',
                default => null,
            };

            if ($razlog !== null) {
                $rezultat['preskoceno'][] = $kandidat + ['razlog' => $razlog];

                continue;
            }

            $vecIzabrani[$kandidat['sn']] = true;
            $rezultat['ispravka'][] = $kandidat + ['razlog' => null];
        }

        return $rezultat;
    }

    /**
     * Upisuje ispravke: istorija pa izmena, svaki uređaj u svojoj transakciji.
     * Uređaj koji u međuvremenu više nije "Za instalaciju" se preskače (ponovno pokretanje ne menja ništa).
     *
     * Datum promene je datum instalacije iz Excela, osim kad je stariji od poslednje
     * izmene uređaja; tada je to trenutak ispravke.
     *
     * @param  list<array<string, mixed>>  $ispravke  'ispravka' iz kandidati()
     * @return int broj ispravljenih uređaja
     */
    public function primeni(array $ispravke, User $korisnik): int
    {
        $zaInstalacijuId = TerminalStatusTip::idPoNazivu(TerminalStatusTip::ZA_INSTALACIJU);
        $instaliranId = TerminalStatusTip::idPoNazivu('Instaliran');
        $ispravljeno = 0;

        foreach ($ispravke as $ispravka) {
            DB::transaction(function () use ($ispravka, $korisnik, $zaInstalacijuId, $instaliranId, &$ispravljeno): void {
                $terminalLokacija = TerminalLokacija::query()->lockForUpdate()->find($ispravka['terminal_lokacijaId']);
                if ($terminalLokacija === null || $terminalLokacija->terminal_statusId !== $zaInstalacijuId) {
                    return;
                }

                TerminalLokacijaHistory::createNewHistory($terminalLokacija->id);

                $terminalLokacija->terminal_statusId = $instaliranId;
                $terminalLokacija->korisnikId = $korisnik->id;
                $terminalLokacija->korisnikIme = $korisnik->name;
                $terminalLokacija->updated_at = self::datumPromene($ispravka['datum_instalacije'], $terminalLokacija->updated_at);
                $terminalLokacija->save();

                $ispravljeno++;
            });
        }

        return $ispravljeno;
    }

    public static function datumPromene(?CarbonImmutable $datumInstalacije, mixed $poslednjaIzmena): CarbonImmutable
    {
        $sada = CarbonImmutable::now();

        if ($datumInstalacije === null
            || ($poslednjaIzmena !== null && $datumInstalacije->lt(CarbonImmutable::parse($poslednjaIzmena)->startOfDay()))) {
            return $sada;
        }

        return $datumInstalacije->gt($sada) ? $sada : $datumInstalacije;
    }
}
