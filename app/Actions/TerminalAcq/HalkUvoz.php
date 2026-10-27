<?php

namespace App\Actions\TerminalAcq;

use App\Models\Lokacija;
use App\Models\LokacijaKontaktOsoba;
use App\Models\Terminal;
use App\Models\TerminalAcq;
use App\Models\TerminalAcqHistory;
use App\Models\TerminalLokacija;
use App\Models\TerminalStatusTip;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Faza 3: jednokratan uvoz HALK glavne tabele u terminal_acq.
 *
 * Excel je dnevnik instalacija (red = događaj), ne registar TID-ova. Redovi se grupišu
 * po TID-u; hronološki poslednji red određuje trenutno stanje TID-a, a raniji redovi
 * i uređaj zatvorenog TID-a se upisuju u terminal_acq_histories ("TID je bio na
 * uređaju X"), da ostane trag pod kojim TID-ovima je uređaj bio.
 *
 * Postojeći TID-ovi i postojeće vrednosti lokacija se ne menjaju, pa je ponovno
 * pokretanje bezbedno: dodaje samo ono što nedostaje (npr. posle ispravke Excela).
 */
class HalkUvoz
{
    public const AKTIVAN = 'AKTIVAN';

    public const ZATVOREN = 'ZATVOREN';

    public const STARI_TID = 'STARI TID';

    public const ISTORIJA = 'ISTORIJA';

    public const POSTOJI = 'POSTOJI';

    public const PRESKOCEN = 'PRESKOČEN';

    private const AKTIVNI_STATUSI = ['Instaliran', 'Za instalaciju', 'Testni'];

    /**
     * Statusi iz Excela koje uvoz upisuje (Testni se uvozi kao Instaliran + testni = 1).
     */
    private const STATUSI = ['Instaliran', 'Za instalaciju', 'Deinstaliran', 'Prekonfigurisan', 'Opozvan'];

    private const LOKACIJA_TIP_KORISNIK = 3;

    /**
     * @var list<array{red: int|string, tid: ?string, sn: ?string, rezultat: string, opis: string}>
     */
    private array $izvestaj = [];

    /**
     * @var array<string, ?int>
     */
    private array $statusId = [];

    /**
     * SN → [terminalId, lokacijaId]
     *
     * @var Collection<string, array{terminalId: int, lokacijaId: ?int}>
     */
    private Collection $uredjaji;

    /**
     * @var Collection<int, Lokacija>
     */
    private Collection $lokacije;

    /**
     * PIB → id-jevi lokacija tipa "Korisnik terminala"
     *
     * @var array<string, list<int>>
     */
    private array $lokacijePoPib = [];

    public function __construct(private DodajTerminalAcq $dodajTerminalAcq) {}

    /**
     * @param  list<array<string, mixed>>  $redovi  redovi iz HalkExcelCitac
     * @return list<array{red: int|string, tid: ?string, sn: ?string, rezultat: string, opis: string}>
     */
    public function uvezi(array $redovi, User $korisnik, ?string $izvorFajl = null): array
    {
        $this->izvestaj = [];
        $redovi = $this->pripremi($redovi);
        $this->ucitajBazu($redovi);

        $grupe = [];
        foreach ($redovi as $red) {
            if ($red['tid'] === null) {
                $this->upisi($red, self::PRESKOCEN, 'TID prazan ili neispravan.');

                continue;
            }
            $grupe[$red['tid']][] = $red;
        }

        $konflikti = $this->konflikti($grupe);
        $kreirani = [];

        foreach ($grupe as $tid => $grupa) {
            usort($grupa, fn (array $a, array $b): int => [$a['datum'], $a['red']] <=> [$b['datum'], $b['red']]);
            $grupe[$tid] = $grupa;

            if (isset($konflikti[$tid])) {
                $this->upisiGrupu($grupa, self::PRESKOCEN, $konflikti[$tid]);

                continue;
            }

            if (TerminalAcq::query()->where('acq_tid', $tid)->exists()) {
                $this->upisiGrupu($grupa, self::POSTOJI, 'TID već postoji u terminal_acq; uvoz ga ne menja.');

                continue;
            }

            try {
                $kreirani[$tid] = DB::transaction(fn (): TerminalAcq => $this->uveziGrupu($grupa, $korisnik, $izvorFajl));
            } catch (RuntimeException $e) {
                $this->upisiGrupu($grupa, self::PRESKOCEN, $e->getMessage());
            }
        }

        foreach ($kreirani as $tid => $acq) {
            $poslednji = end($grupe[$tid]);
            if ($poslednji['stari_tid'] !== null) {
                $this->povezatiSaStarim($acq, $poslednji, $korisnik, $izvorFajl);
            }
        }

        usort($this->izvestaj, fn (array $a, array $b): int => (int) $a['red'] <=> (int) $b['red']);

        return $this->izvestaj;
    }

    /**
     * @param  list<array<string, mixed>>  $grupa  redovi jednog TID-a, hronološki
     */
    private function uveziGrupu(array $grupa, User $korisnik, ?string $izvorFajl): TerminalAcq
    {
        $poslednji = $grupa[count($grupa) - 1];
        $raniji = array_slice($grupa, 0, -1);
        $aktivan = in_array($poslednji['status'], self::AKTIVNI_STATUSI, true);

        $acq = $aktivan
            ? $this->kreirajAktivan($poslednji, $korisnik, $izvorFajl)
            : $this->kreirajZatvoren($poslednji, $this->statusId[$poslednji['status']], $korisnik, $izvorFajl);

        foreach ($raniji as $red) {
            $this->istorijskiRed($acq, $red, $korisnik);
            $this->upisi($red, self::ISTORIJA, 'Raniji red istog TID-a, upisan u istoriju.');
        }

        if (! $aktivan && $this->uredjaj($poslednji) !== null) {
            $this->istorijskiRed($acq, $poslednji, $korisnik);
        }

        return $acq;
    }

    /**
     * @param  array<string, mixed>  $red
     */
    private function kreirajAktivan(array $red, User $korisnik, ?string $izvorFajl): TerminalAcq
    {
        $uredjaj = $this->uredjaj($red);
        if ($uredjaj === null) {
            throw new RuntimeException($red['sn'] === null
                ? 'Aktivan TID bez ispravnog SN-a.'
                : 'Uređaj ne postoji ili nema red u terminal_lokacijas.');
        }

        $lokacija = $this->lokacije->get($uredjaj['lokacijaId']);
        $dopunaDozvoljena = $lokacija !== null
            && $lokacija->lokacija_tipId === self::LOKACIJA_TIP_KORISNIK
            && ($red['pib'] === null || in_array(HalkNormalizator::pib($lokacija->pib), [null, $red['pib']], true));

        $acq = $this->dodajTerminalAcq->saUredjajem(
            ['acq_tid' => $red['tid'], ...$this->podaciTid($red, $izvorFajl)],
            $uredjaj['terminalId'],
            $korisnik,
            $dopunaDozvoljena ? $red['dopuna_lokacije'] : []
        );

        $opis = 'Uređaj '.$red['sn'].'.';
        if ($dopunaDozvoljena) {
            $lokacija->refresh();
            if ($this->dodajKontakt($lokacija, $red)) {
                $opis .= ' Dodat kontakt.';
            }
        } elseif ($lokacija !== null) {
            $opis .= ' Lokacija nije dopunjena (nije kod korisnika ili se PIB razlikuje).';
        }

        $this->upisi($red, self::AKTIVAN, $opis);

        return $acq;
    }

    /**
     * @param  array<string, mixed>  $red
     */
    private function kreirajZatvoren(array $red, ?int $statusId, User $korisnik, ?string $izvorFajl, string $rezultat = self::ZATVOREN): TerminalAcq
    {
        $lokacijaId = $this->lokacijaPoPib($red['pib']);
        $podaci = $this->podaciTid($red, $izvorFajl);

        if ($rezultat === self::STARI_TID) {
            $podaci = [
                ...$podaci,
                'datum_zahteva' => null,
                'datum_instalacije' => null,
                'dodatne_opcije' => null,
                'napomena' => "Kreiran pri uvozu iz reda {$red['red']} ({$red['stari_tid']}/{$red['tid']}); stari TID nema svoj red u tabeli.",
            ];
        }

        $acq = TerminalAcq::create([
            ...$podaci,
            'acq_tid' => $rezultat === self::STARI_TID ? $red['stari_tid'] : $red['tid'],
            'terminalId' => null,
            'terminal_statusId' => $statusId,
            'lokacijaId' => $lokacijaId,
            'korisnikId' => $korisnik->id,
            'korisnikIme' => $korisnik->name,
        ]);

        $this->upisi(
            $red,
            $rezultat,
            ($rezultat === self::STARI_TID ? "Stari TID {$acq->acq_tid} kreiran kao Prekonfigurisan. " : "Status {$red['status']}. ")
                .($lokacijaId === null ? 'Bez lokacije (PIB nije jednoznačan).' : "Lokacija {$lokacijaId} po PIB-u.")
        );

        return $acq;
    }

    /**
     * PN_stari/PN_novi: novi TID dobija vezu na stari. Stari TID bez sopstvenog reda se
     * kreira kao Prekonfigurisan; postojeći stari TID se ne menja.
     *
     * @param  array<string, mixed>  $red  poslednji red novog TID-a
     */
    private function povezatiSaStarim(TerminalAcq $novi, array $red, User $korisnik, ?string $izvorFajl): void
    {
        DB::transaction(function () use ($novi, $red, $korisnik, $izvorFajl): void {
            $stari = TerminalAcq::query()->where('acq_tid', $red['stari_tid'])->first();

            if ($stari === null) {
                $stari = $this->kreirajZatvoren($red, $this->statusId['Prekonfigurisan'], $korisnik, $izvorFajl, self::STARI_TID);
                if ($this->uredjaj($red) !== null) {
                    $this->istorijskiRed($stari, $red, $korisnik);
                }
            } elseif ($stari->terminalId !== null) {
                $this->upisi($red, self::ISTORIJA, "Stari TID {$stari->acq_tid} je i dalje aktivan; zatvoriti ga ručno.");
            }

            $novi->prethodni_terminal_acqId = $stari->id;
            $novi->save();
        });
    }

    /**
     * "TID je bio na uređaju X": red istorije za raniji red Excela ili za uređaj
     * zatvorenog TID-a. Datum je datum instalacije reda.
     *
     * @param  array<string, mixed>  $red
     */
    private function istorijskiRed(TerminalAcq $acq, array $red, User $korisnik): void
    {
        $uredjaj = $this->uredjaj($red);
        $datum = $red['datum'] ?? CarbonImmutable::now();

        $history = new TerminalAcqHistory([
            ...$this->podaciTid($red, $acq->izvor_fajl),
            'terminal_acqId' => $acq->id,
            'acq_tid' => $acq->acq_tid,
            'terminalId' => $uredjaj['terminalId'] ?? null,
            'terminal_statusId' => $uredjaj !== null ? $this->statusId['Instaliran'] : $this->statusId[$red['status']] ?? null,
            'lokacijaId' => $this->lokacijaPoPib($red['pib']),
            'acq_mid' => $red['dopuna_lokacije']['acq_mid'],
            'korisnikId' => $korisnik->id,
            'korisnikIme' => $korisnik->name,
        ]);
        $history->created_at = $datum;
        $history->updated_at = $datum;
        $history->save();
    }

    /**
     * @param  array<string, mixed>  $red
     */
    private function dodajKontakt(Lokacija $lokacija, array $red): bool
    {
        if ($red['kontakt'] === null || LokacijaKontaktOsoba::query()->where('lokacijaId', $lokacija->id)->exists()) {
            return false;
        }

        LokacijaKontaktOsoba::create([
            'lokacijaId' => $lokacija->id,
            'name' => mb_substr($red['kontakt'], 0, 64),
            'tel' => $red['telefon'] !== null ? mb_substr($red['telefon'], 0, 32) : null,
        ]);

        return true;
    }

    /**
     * Normalizacija, efektivni datum i ključevi koje uvoz koristi.
     *
     * Red bez datuma instalacije i datuma zahteva dobija datum prethodnog reda u Excelu,
     * pa u hronološkom redosledu ostaje na mestu na kom je u tabeli.
     *
     * @param  list<array<string, mixed>>  $redovi
     * @return list<array<string, mixed>>
     */
    private function pripremi(array $redovi): array
    {
        $prethodniDatum = CarbonImmutable::create(2000, 1, 1);
        $pripremljeni = [];

        foreach ($redovi as $red) {
            $tid = HalkNormalizator::tid($red['acq_tid'] ?? null);
            $status = HalkNormalizator::statusReda($red)['status'];
            $datumInstalacije = HalkNormalizator::datum($red['datum_instalacije'] ?? null);
            $datumZahteva = HalkNormalizator::datum($red['datum_zahteva'] ?? null);
            $prethodniDatum = $datumInstalacije ?? $datumZahteva ?? $prethodniDatum;
            $preciznaLokacija = HalkNormalizator::tekst($red['precizna_lokacija'] ?? null);
            $plusKod = HalkNormalizator::jePlusKod($preciznaLokacija);

            $pripremljeni[] = [
                'red' => $red['red'],
                'tid' => $tid['tid'],
                'stari_tid' => $tid['stari_tid'],
                'testni' => $tid['testni'] || $status === 'Testni',
                'status' => $status === 'Testni' ? 'Instaliran' : $status,
                'sn' => HalkNormalizator::ispravanSn($red['sn'] ?? null),
                'datum' => $prethodniDatum,
                'datum_zahteva' => $datumZahteva,
                'datum_instalacije' => $datumInstalacije,
                'pib' => HalkNormalizator::pib($red['pib'] ?? null),
                'kontakt' => HalkNormalizator::tekst($red['kontakt'] ?? null),
                'telefon' => HalkNormalizator::telefonZaKontakt($red['telefon'] ?? null),
                'naziv_na_slipu' => HalkNormalizator::tekst($red['naziv_na_slipu'] ?? null),
                'komunikacija' => HalkNormalizator::komunikacija($red['komunikacija'] ?? null),
                'iccid' => HalkNormalizator::oznaka($red['iccid'] ?? null),
                'inventarni_broj' => HalkNormalizator::oznaka($red['inventarni_broj'] ?? null),
                'dodatne_opcije' => HalkNormalizator::tekst($red['dodatne_opcije'] ?? null),
                'napomena' => $plusKod ? null : $preciznaLokacija,
                'dopuna_lokacije' => [
                    'acq_mid' => HalkNormalizator::mid($red['acq_mid'] ?? null) ?? HalkNormalizator::mid($red['sifra_trgovca'] ?? null),
                    'mcc' => HalkNormalizator::mcc($red['mcc'] ?? null),
                    'ptt' => HalkNormalizator::ptt($red['ptt'] ?? null),
                    'pib' => HalkNormalizator::pib($red['pib'] ?? null),
                    'precizna_lokacija' => $plusKod ? $preciznaLokacija : null,
                ],
            ];
        }

        return $pripremljeni;
    }

    /**
     * @param  list<array<string, mixed>>  $redovi
     */
    private function ucitajBazu(array $redovi): void
    {
        foreach (self::STATUSI as $naziv) {
            $this->statusId[$naziv] = TerminalStatusTip::idPoNazivu($naziv);
        }
        if (in_array(null, $this->statusId, true)) {
            throw new RuntimeException('Nedostaju statusi u terminal_status_tips (pokrenuti migracije Faze 1).');
        }

        $terminali = Terminal::query()
            ->whereIn('sn', array_values(array_unique(array_filter(array_column($redovi, 'sn')))))
            ->pluck('id', 'sn')
            ->mapWithKeys(fn (int $id, string $sn): array => [strtoupper($sn) => $id]);
        $terminalLokacije = TerminalLokacija::query()->whereIn('terminalId', $terminali->values())->pluck('lokacijaId', 'terminalId');

        $this->uredjaji = $terminali
            ->filter(fn (int $terminalId): bool => $terminalLokacije->has($terminalId))
            ->map(fn (int $terminalId): array => ['terminalId' => $terminalId, 'lokacijaId' => $terminalLokacije->get($terminalId)]);

        $pibovi = array_values(array_unique(array_filter(array_column($redovi, 'pib'))));
        $this->lokacije = Lokacija::query()
            ->whereIn('id', $terminalLokacije->values()->unique())
            ->orWhere(fn ($upit) => $upit->where('lokacija_tipId', self::LOKACIJA_TIP_KORISNIK)->whereIn('pib', $pibovi))
            ->get()
            ->keyBy('id');

        $this->lokacijePoPib = [];
        foreach ($this->lokacije as $lokacija) {
            $pib = HalkNormalizator::pib($lokacija->pib);
            if ($pib !== null && $lokacija->lokacija_tipId === self::LOKACIJA_TIP_KORISNIK) {
                $this->lokacijePoPib[$pib][] = $lokacija->id;
            }
        }
    }

    /**
     * TID-ovi koji se ne uvoze dok se konflikt ne reši u Excelu: TID aktivan u više
     * redova, ili uređaj aktivan pod više TID-ova.
     *
     * @param  array<string, list<array<string, mixed>>>  $grupe
     * @return array<string, string> TID → razlog
     */
    private function konflikti(array $grupe): array
    {
        $konflikti = [];
        $aktivniTidPoSn = [];

        foreach ($grupe as $tid => $grupa) {
            $aktivni = array_filter($grupa, fn (array $red): bool => in_array($red['status'], self::AKTIVNI_STATUSI, true));
            if (count($aktivni) > 1) {
                $konflikti[$tid] = 'TID konflikt: aktivan u više redova ('.implode(', ', array_column($aktivni, 'red')).').';
            }
            foreach ($aktivni as $red) {
                if ($red['sn'] !== null) {
                    $aktivniTidPoSn[$red['sn']][$tid] = true;
                }
            }
        }

        foreach ($aktivniTidPoSn as $sn => $tidovi) {
            if (count($tidovi) > 1) {
                foreach (array_keys($tidovi) as $tid) {
                    $konflikti[$tid] ??= "TID konflikt: uređaj {$sn} je aktivan pod više TID-ova (".implode(', ', array_keys($tidovi)).').';
                }
            }
        }

        return $konflikti;
    }

    /**
     * @param  array<string, mixed>  $red
     * @return array<string, mixed>
     */
    private function podaciTid(array $red, ?string $izvorFajl): array
    {
        return [
            'naziv_na_slipu' => $red['naziv_na_slipu'] !== null ? mb_substr($red['naziv_na_slipu'], 0, 64) : null,
            'komunikacija' => $red['komunikacija'] !== null ? mb_substr($red['komunikacija'], 0, 16) : null,
            'iccid' => $red['iccid'] !== null ? mb_substr($red['iccid'], 0, 22) : null,
            'inventarni_broj' => $red['inventarni_broj'] !== null ? mb_substr($red['inventarni_broj'], 0, 24) : null,
            'dodatne_opcije' => $red['dodatne_opcije'],
            'testni' => $red['testni'],
            'napomena' => $red['napomena'],
            'datum_zahteva' => $red['datum_zahteva'],
            'datum_instalacije' => $red['datum_instalacije'],
            'izvor' => TerminalAcq::IZVOR_UVOZ,
            'izvor_fajl' => $izvorFajl,
        ];
    }

    /**
     * @param  array<string, mixed>  $red
     * @return array{terminalId: int, lokacijaId: ?int}|null
     */
    private function uredjaj(array $red): ?array
    {
        return $red['sn'] !== null ? $this->uredjaji->get($red['sn']) : null;
    }

    private function lokacijaPoPib(?string $pib): ?int
    {
        $lokacije = $pib !== null ? ($this->lokacijePoPib[$pib] ?? []) : [];

        return count($lokacije) === 1 ? $lokacije[0] : null;
    }

    /**
     * @param  list<array<string, mixed>>  $grupa
     */
    private function upisiGrupu(array $grupa, string $rezultat, string $opis): void
    {
        foreach ($grupa as $red) {
            $this->upisi($red, $rezultat, $opis);
        }
    }

    /**
     * @param  array<string, mixed>  $red
     */
    private function upisi(array $red, string $rezultat, string $opis): void
    {
        $this->izvestaj[] = [
            'red' => $red['red'],
            'tid' => $red['tid'],
            'sn' => $red['sn'],
            'rezultat' => $rezultat,
            'opis' => $opis,
        ];
    }
}
