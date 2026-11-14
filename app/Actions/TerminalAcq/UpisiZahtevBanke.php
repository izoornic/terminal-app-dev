<?php

namespace App\Actions\TerminalAcq;

use App\Models\Lokacija;
use App\Models\LokacijaKontaktOsoba;
use App\Models\Region;
use App\Models\TerminalAcq;
use App\Models\TerminalStatusTip;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Faza 3b: upis potvrđenih redova zahteva banke, sve u jednoj transakciji.
 *
 * Redovi dolaze iz ProcitajZahtevBanke (ponovo pročitani sa servera, ili jedan ručno unet
 * red kod ručne prekonfiguracije / instalacije); od korisnika se
 * uzimaju samo izbori: da li se red uvozi, lokacija (postojeća ili nova), region i SN.
 *
 * - instalacija: TID "Za instalaciju" na lokaciji; uz SN odmah "Dodela uređaja"
 * - prekonfiguracija: TID "Čeka prekonfiguraciju" sa vezom na stari TID i opcijama starog TID-a; stari TID i
 *   uređaj se ne menjaju (to radi "Izvrši prekonfiguraciju"); MID koji bi zamenio
 *   postojeći MID lokacije čeka u zahtev_acq_mid
 * - lokacija: postojeća dobija samo prazna polja; nova se pravi iz reda (isti PIB =
 *   podlokacija, is_duplicate kao u formi lokacije); kontakt samo ako ga lokacija nema
 */
class UpisiZahtevBanke
{
    private const LOKACIJA_TIP_KORISNIK = 3;

    /**
     * pib|adresa → id nove lokacije napravljene u ovom upisu
     *
     * @var array<string, int>
     */
    private array $noveLokacije = [];

    public function __construct(
        private DodajTerminalAcq $dodajTerminalAcq,
        private DodeliUredjajTerminalAcq $dodeliUredjaj,
        private OpcijeTerminalAcq $opcije,
    ) {}

    /**
     * @param  list<array<string, mixed>>  $redovi  iz ProcitajZahtevBanke::procitaj()
     * @param  array<int|string, array{izabran?: bool, lokacija_akcija?: string, lokacijaId?: int|string|null, regionId?: int|string|null, sn?: string|null}>  $izbori  po broju reda
     * @return array{tidova: int, lokacija: int, kontakata: int, upozorenja: list<string>}
     *
     * @throws RuntimeException "Red N: …" — ništa nije upisano
     */
    public function upisi(array $redovi, array $izbori, string $datumZahteva, ?string $izvorFajl, User $korisnik, string $izvor = TerminalAcq::IZVOR_ZAHTEV): array
    {
        $this->noveLokacije = [];
        $rezultat = ['tidova' => 0, 'lokacija' => 0, 'kontakata' => 0, 'upozorenja' => []];

        DB::transaction(function () use ($redovi, $izbori, $datumZahteva, $izvorFajl, $korisnik, $izvor, &$rezultat): void {
            foreach ($redovi as $red) {
                $izbor = $izbori[$red['red']] ?? [];
                if (! ($izbor['izabran'] ?? false)) {
                    continue;
                }

                try {
                    $this->upisiRed($red, $izbor, $datumZahteva, $izvorFajl, $korisnik, $izvor, $rezultat);
                } catch (RuntimeException $e) {
                    throw new RuntimeException("Red {$red['red']} ({$red['tid_sirovo']}): {$e->getMessage()}", 0, $e);
                }
            }
        });

        return $rezultat;
    }

    /**
     * @param  array<string, mixed>  $red
     * @param  array<string, mixed>  $izbor
     * @param  array{tidova: int, lokacija: int, kontakata: int, upozorenja: list<string>}  $rezultat
     */
    private function upisiRed(array $red, array $izbor, string $datumZahteva, ?string $izvorFajl, User $korisnik, string $izvor, array &$rezultat): void
    {
        if ($red['greske'] !== []) {
            throw new RuntimeException($red['greske'][0]);
        }

        $prekonfiguracija = $red['vrsta'] === ProcitajZahtevBanke::PREKONFIGURACIJA;
        $stari = $prekonfiguracija ? $this->stariTid($red) : null;

        $lokacija = $this->lokacija($red, $izbor, $rezultat);
        $zahtevMid = null;
        $midLokacije = HalkNormalizator::mid($lokacija->acq_mid);
        if ($prekonfiguracija && $midLokacije !== null && $red['acq_mid'] !== null && $midLokacije !== $red['acq_mid']) {
            $zahtevMid = $red['acq_mid'];
        }

        $this->dodajTerminalAcq->dopuniLokaciju($lokacija->id, [
            'acq_mid' => $red['acq_mid'],
            'mcc' => $red['mcc'],
            'ptt' => $red['ptt'],
        ]);
        if ($this->dodajKontakt($lokacija->id, $red)) {
            $rezultat['kontakata']++;
        }

        $acq = $this->dodajTerminalAcq->bezUredjaja(
            [
                'acq_tid' => $red['tid'],
                'naziv_na_slipu' => $red['naziv_na_slipu'] !== null ? mb_substr($red['naziv_na_slipu'], 0, 64) : null,
                'komunikacija' => $red['komunikacija'],
                'dodatne_opcije' => $red['dodatne_opcije'],
                'testni' => $red['testni'],
                'datum_zahteva' => $datumZahteva,
                'prethodni_terminal_acqId' => $stari?->id,
                'zahtev_acq_mid' => $zahtevMid,
                'trazeni_terminal_tipId' => $red['trazeni_terminal_tipId'],
                'izvor' => $izvor,
                'izvor_fajl' => $izvorFajl,
            ],
            $lokacija->id,
            TerminalStatusTip::idPoNazivu($prekonfiguracija ? TerminalStatusTip::CEKA_PREKONFIGURACIJU : TerminalStatusTip::ZA_INSTALACIJU),
            $korisnik
        );
        $rezultat['tidova']++;

        if ($stari !== null) {
            $this->opcije->prenesi($stari, $acq, $korisnik);
        }

        $sn = trim((string) ($izbor['sn'] ?? ''));
        if ($sn !== '' && ! $prekonfiguracija) {
            foreach ($this->dodeliUredjaj->izvrsi($acq, $sn, $korisnik) as $upozorenje) {
                $rezultat['upozorenja'][] = "{$acq->acq_tid}: {$upozorenje}";
            }
        }
    }

    /**
     * Stari TID se proverava ponovo: mogao je da se promeni od pregleda.
     *
     * @param  array<string, mixed>  $red
     */
    private function stariTid(array $red): TerminalAcq
    {
        $stari = TerminalAcq::query()->lockForUpdate()->where('acq_tid', $red['stari_tid'])->first();

        if ($stari === null || $stari->terminalId === null) {
            throw new RuntimeException("Stari TID {$red['stari_tid']} ne postoji ili nema uređaj.");
        }

        if (TerminalAcq::query()->where('prethodni_terminal_acqId', $stari->id)->exists()) {
            throw new RuntimeException("Prekonfiguracija starog TID-a {$stari->acq_tid} je već zatražena.");
        }

        return $stari;
    }

    /**
     * @param  array<string, mixed>  $red
     * @param  array<string, mixed>  $izbor
     * @param  array{tidova: int, lokacija: int, kontakata: int, upozorenja: list<string>}  $rezultat
     */
    private function lokacija(array $red, array $izbor, array &$rezultat): Lokacija
    {
        $akcija = $izbor['lokacija_akcija'] ?? $red['lokacija_akcija'];

        if ($akcija === ProcitajZahtevBanke::LOKACIJA_POSTOJECA) {
            $lokacija = Lokacija::query()
                ->where('lokacija_tipId', self::LOKACIJA_TIP_KORISNIK)
                ->find((int) ($izbor['lokacijaId'] ?? $red['lokacijaId']));
            if ($lokacija === null) {
                throw new RuntimeException('Izaberite postojeću lokaciju korisnika terminala.');
            }

            return $lokacija;
        }

        $kljuc = $red['pib'].'|'.preg_replace('/[^a-z0-9]/', '', Str::lower(Str::ascii((string) $red['adresa'])));
        if (isset($this->noveLokacije[$kljuc])) {
            return Lokacija::findOrFail($this->noveLokacije[$kljuc]);
        }

        $regionId = (int) ($izbor['regionId'] ?? $red['regionId']);
        if (! Region::query()->whereKey($regionId)->exists()) {
            throw new RuntimeException('Izaberite region za novu lokaciju.');
        }

        $lokacija = Lokacija::create([
            'is_duplicate' => Lokacija::query()->where('pib', $red['pib'])->count(),
            'regionId' => $regionId,
            'lokacija_tipId' => self::LOKACIJA_TIP_KORISNIK,
            'l_naziv' => $red['naziv_trgovca'],
            'mesto' => $red['mesto'],
            'adresa' => $red['adresa'],
            'ptt' => $red['ptt'],
            'pib' => $red['pib'],
            'acq_mid' => $red['acq_mid'],
            'mcc' => $red['mcc'],
        ]);
        $this->noveLokacije[$kljuc] = $lokacija->id;
        $rezultat['lokacija']++;

        return $lokacija;
    }

    /**
     * @param  array<string, mixed>  $red
     */
    private function dodajKontakt(int $lokacijaId, array $red): bool
    {
        if ($red['kontakt'] === null || LokacijaKontaktOsoba::query()->where('lokacijaId', $lokacijaId)->exists()) {
            return false;
        }

        LokacijaKontaktOsoba::create([
            'lokacijaId' => $lokacijaId,
            'name' => mb_substr($red['kontakt'], 0, 64),
            'tel' => $red['telefon'] !== null ? mb_substr($red['telefon'], 0, 32) : null,
        ]);

        return true;
    }
}
