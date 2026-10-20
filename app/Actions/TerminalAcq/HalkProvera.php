<?php

namespace App\Actions\TerminalAcq;

use App\Models\Lokacija;
use App\Models\LokacijaKontaktOsoba;
use App\Models\Terminal;
use App\Models\TerminalLokacija;
use App\Models\TerminalStatusTip;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Faza 2: poređenje HALK glavne tabele sa bazom, pre jednokratnog uvoza.
 *
 * Samo čita. Vraća nalaze po kategorijama; red bez ijednog nalaza je "OK".
 * Provera se ponavlja dok ne ostanu samo OK redovi i odobreni izuzeci.
 */
class HalkProvera
{
    public const OK = 1;

    public const LOKACIJA_BEZ_PIB = 2;

    public const PIB_RAZLICIT = 3;

    public const STATUS_SE_NE_SLAZE = 4;

    public const TID_KONFLIKT = 5;

    public const NEISPRAVAN_TID = 6;

    public const BEZ_UREDJAJA = 7;

    public const LOKACIJA_VISE_MID = 8;

    public const MID_RAZLICIT = 9;

    public const KONTAKT_RAZLICIT = 10;

    public const PRECIZNA_LOKACIJA = 11;

    public const TELEFON_PREDUGACAK = 12;

    public const STATUS_PRAZAN = 13;

    /**
     * @var array<int, string>
     */
    public const KATEGORIJE = [
        self::OK => 'OK',
        self::LOKACIJA_BEZ_PIB => 'Lokacija bez PIB-a',
        self::PIB_RAZLICIT => 'PIB se razlikuje',
        self::STATUS_SE_NE_SLAZE => 'Status se ne slaže',
        self::TID_KONFLIKT => 'TID konflikt',
        self::NEISPRAVAN_TID => 'Neispravan / testni TID',
        self::BEZ_UREDJAJA => 'Bez ispravnog uređaja',
        self::LOKACIJA_VISE_MID => 'Lokacija sa više MID-ova / MCC-ova',
        self::MID_RAZLICIT => 'ACQ MID ≠ MID/šifra trgovca',
        self::KONTAKT_RAZLICIT => 'Kontakt se razlikuje',
        self::PRECIZNA_LOKACIJA => 'Precizna lokacija',
        self::TELEFON_PREDUGACAK => 'Telefon duži od 32 znaka',
        self::STATUS_PRAZAN => 'Status prazan u Excelu',
    ];

    private const AKTIVNI_STATUSI = ['Instaliran', 'Za instalaciju', 'Testni'];

    private const ZATVORENI_STATUSI = ['Deinstaliran', 'Prekonfigurisan', 'Opozvan'];

    /**
     * Statusi uređaja u aplikaciji koji znače da je uređaj instaliran kod korisnika.
     */
    private const INSTALIRAN_U_APLIKACIJI = ['Instaliran', 'Instaliran zamenski'];

    private const LOKACIJA_TIP_KORISNIK = 3;

    private const TELEFON_MAX = 32;

    /**
     * @var list<array<string, mixed>>
     */
    private array $nalazi = [];

    /**
     * @param  list<array<string, mixed>>  $redovi  redovi iz HalkExcelCitac
     * @return list<array{red: int|string, kategorija: int, naziv: string, tid: ?string, sn: ?string, lokacijaId: ?int, excel: ?string, aplikacija: ?string, opis: string}>
     */
    public function proveri(array $redovi): array
    {
        $this->nalazi = [];
        $redovi = array_map(fn (array $red): array => $this->pripremi($red), $redovi);

        $terminali = Terminal::query()
            ->whereIn('sn', array_values(array_filter(array_column($redovi, 'sn'))))
            ->get()
            ->keyBy(fn (Terminal $terminal): string => strtoupper($terminal->sn));
        $terminalLokacije = TerminalLokacija::query()
            ->whereIn('terminalId', $terminali->pluck('id'))
            ->get()
            ->keyBy('terminalId');
        $lokacije = Lokacija::query()->whereIn('id', $terminalLokacije->pluck('lokacijaId'))->get()->keyBy('id');
        $kontakti = LokacijaKontaktOsoba::query()->whereIn('lokacijaId', $lokacije->keys())->get()->groupBy('lokacijaId');
        $statusi = TerminalStatusTip::tidStatusList();

        foreach ($redovi as &$red) {
            $terminal = $red['sn'] !== null ? $terminali->get($red['sn']) : null;
            $terminalLokacija = $terminal !== null ? $terminalLokacije->get($terminal->id) : null;
            $red['lokacija'] = $terminalLokacija !== null ? $lokacije->get($terminalLokacija->lokacijaId) : null;
            $red['status_aplikacija'] = $terminalLokacija !== null ? ($statusi[$terminalLokacija->terminal_statusId] ?? null) : null;

            $this->proveriTid($red);
            $this->proveriUredjaj($red, $terminal, $terminalLokacija);
            $this->proveriPib($red);
            $this->proveriMid($red);
            $this->proveriKontakt($red, $kontakti->get($red['lokacija']?->id, collect()));
            $this->proveriPreciznuLokaciju($red);
        }
        unset($red);

        $this->proveriStatus($redovi);
        $this->proveriDuplikate($redovi);
        $this->proveriLokacijeSaViseMid($redovi);

        $redoviSaNalazom = array_flip(array_column($this->nalazi, 'red'));
        foreach ($redovi as $red) {
            if (! isset($redoviSaNalazom[$red['red']])) {
                $this->nalaz($red, self::OK, 'Bez nalaza.');
            }
        }

        usort($this->nalazi, fn (array $a, array $b): int => [$a['kategorija'], (int) $a['red']] <=> [$b['kategorija'], (int) $b['red']]);

        return $this->nalazi;
    }

    /**
     * @param  array<string, mixed>  $red
     * @return array<string, mixed>
     */
    private function pripremi(array $red): array
    {
        $tid = HalkNormalizator::tid($red['acq_tid'] ?? null);
        $snTekst = HalkNormalizator::sn($red['sn'] ?? null);
        $statusReda = HalkNormalizator::statusReda($red);
        $status = $statusReda['status'];
        $statusIzveden = $statusReda['izveden'] ? $status : null;

        return [
            'red' => $red['red'],
            'tid_sirovo' => HalkNormalizator::tekst($red['acq_tid'] ?? null),
            'tid' => $tid['tid'],
            'stari_tid' => $tid['stari_tid'],
            'testni' => $tid['testni'] || $status === 'Testni',
            'tid_ispravan' => $tid['ispravan'],
            'sn_sirovo' => $snTekst,
            'sn' => HalkNormalizator::ispravanSn($snTekst),
            'status' => $status,
            'status_izveden' => $statusIzveden,
            'acq_mid' => HalkNormalizator::mid($red['acq_mid'] ?? null),
            'acq_mid_sirovo' => HalkNormalizator::tekst($red['acq_mid'] ?? null),
            'sifra_trgovca' => HalkNormalizator::mid($red['sifra_trgovca'] ?? null),
            'mcc' => HalkNormalizator::mcc($red['mcc'] ?? null),
            'pib' => HalkNormalizator::pib($red['pib'] ?? null),
            'naziv_trgovca' => HalkNormalizator::tekst($red['naziv_trgovca'] ?? null),
            'kontakt' => HalkNormalizator::tekst($red['kontakt'] ?? null),
            'telefon' => HalkNormalizator::telefon($red['telefon'] ?? null),
            'precizna_lokacija' => HalkNormalizator::tekst($red['precizna_lokacija'] ?? null),
        ];
    }

    /**
     * @param  array<string, mixed>  $red
     */
    private function proveriTid(array $red): void
    {
        if (! $red['tid_ispravan']) {
            $this->nalaz($red, self::NEISPRAVAN_TID, 'TID prazan ili neispravan; red se uvozi samo ako se TID ispravi u Excelu.', $red['tid_sirovo']);

            return;
        }

        if ($red['testni']) {
            $this->nalaz($red, self::NEISPRAVAN_TID, 'Testni TID: sufiks se skida, a postavlja se testni = 1.', $red['tid_sirovo'], $red['tid']);
        }

        if ($red['status_izveden'] !== null) {
            $this->nalaz($red, self::STATUS_PRAZAN, 'Status je prazan; izveden po pravilu Excel formule (datum instalacije → Instaliran) ili iz napomene u koloni SN.', null, $red['status_izveden']);
        }
    }

    /**
     * @param  array<string, mixed>  $red
     */
    private function proveriUredjaj(array $red, ?Terminal $terminal, ?TerminalLokacija $terminalLokacija): void
    {
        if ($red['sn'] === null) {
            $this->nalaz($red, self::BEZ_UREDJAJA, 'Kolona SN je prazna ili sadrži napomenu; TID se uvozi bez uređaja sa završnim statusom, ili se red preskače.', $red['sn_sirovo']);

            return;
        }

        if ($terminal === null) {
            $this->nalaz($red, self::BEZ_UREDJAJA, 'SN ne postoji u tabeli terminals.', $red['sn']);

            return;
        }

        if ($terminalLokacija === null) {
            $this->nalaz($red, self::BEZ_UREDJAJA, 'Uređaj nema red u terminal_lokacijas.', $red['sn']);
        }
    }

    /**
     * PIB se poredi samo za instalirane redove čiji je uređaj kod korisnika: samo tada je
     * lokacija uređaja lokacija tog trgovca. Uređaj u servisu ili magacinu prijavljuje
     * kategorija "Status se ne slaže".
     *
     * @param  array<string, mixed>  $red
     */
    private function proveriPib(array $red): void
    {
        $lokacija = $red['lokacija'];
        if ($red['status'] !== 'Instaliran' || $lokacija?->lokacija_tipId !== self::LOKACIJA_TIP_KORISNIK) {
            return;
        }

        $pibAplikacija = HalkNormalizator::pib($lokacija->pib);

        if ($pibAplikacija === null) {
            $this->nalaz($red, self::LOKACIJA_BEZ_PIB, 'Predlog: upisati PIB iz Excela na lokaciju (uz potvrdu).', $red['pib'], null);

            return;
        }

        if ($red['pib'] !== $pibAplikacija) {
            $this->nalaz(
                $red,
                self::PIB_RAZLICIT,
                sprintf(
                    'Kontrolna cifra: Excel %s, aplikacija %s.',
                    HalkNormalizator::pibIspravan($red['pib']) ? 'ispravna' : 'NEISPRAVNA',
                    HalkNormalizator::pibIspravan($pibAplikacija) ? 'ispravna' : 'NEISPRAVNA'
                ),
                $red['pib'],
                $pibAplikacija
            );
        }
    }

    /**
     * @param  array<string, mixed>  $red
     */
    private function proveriMid(array $red): void
    {
        if ($red['acq_mid'] !== null && $red['sifra_trgovca'] !== null && $red['acq_mid'] !== $red['sifra_trgovca']) {
            $this->nalaz($red, self::MID_RAZLICIT, 'ACQ Merchant ID i MID/šifra trgovca se razlikuju i posle normalizacije.', $red['acq_mid'], $red['sifra_trgovca']);
        }
    }

    /**
     * @param  array<string, mixed>  $red
     * @param  Collection<int, LokacijaKontaktOsoba>  $kontakti
     */
    private function proveriKontakt(array $red, Collection $kontakti): void
    {
        if ($red['telefon'] !== null && mb_strlen($red['telefon']) > self::TELEFON_MAX) {
            $this->nalaz($red, self::TELEFON_PREDUGACAK, 'Skratiti u Excelu pre uvoza.', $red['telefon']);
        }

        if ($red['status'] !== 'Instaliran' || $red['lokacija'] === null || $kontakti->isEmpty()) {
            return;
        }

        $telefonExcel = $this->uporediveCifre($red['telefon']);
        $imeExcel = $this->uporedivoIme($red['kontakt']);

        $poklopljen = $telefonExcel !== null
            ? $kontakti->contains(function (LokacijaKontaktOsoba $kontakt) use ($telefonExcel): bool {
                $telefon = $this->uporediveCifre($kontakt->tel);

                return $telefon !== null && (str_contains($telefonExcel, $telefon) || str_contains($telefon, $telefonExcel));
            })
            : $imeExcel === null || $kontakti->contains(fn (LokacijaKontaktOsoba $kontakt): bool => $this->uporedivoIme($kontakt->name) === $imeExcel);

        if (! $poklopljen) {
            $this->nalaz(
                $red,
                self::KONTAKT_RAZLICIT,
                'Telefon iz Excela nema nijedan kontakt lokacije. Ponuditi: zadrži / zameni / dodaj kao novi kontakt.',
                trim(($red['kontakt'] ?? '').' '.($red['telefon'] ?? '')),
                $kontakti->map(fn (LokacijaKontaktOsoba $kontakt): string => trim($kontakt->name.' '.$kontakt->tel))->implode(' | ')
            );
        }
    }

    /**
     * @param  array<string, mixed>  $red
     */
    private function proveriPreciznuLokaciju(array $red): void
    {
        $vrednost = $red['precizna_lokacija'];
        if ($vrednost === null) {
            return;
        }

        $predlog = match (true) {
            HalkNormalizator::jePlusKod($vrednost) => 'lokacijas.precizna_lokacija (plus kod)',
            (bool) preg_match('/PREKONFIG|VIP|INSTAL|PN\d{6}|TID/i', $vrednost) => 'terminal_acq.napomena',
            default => 'proveriti: adresa → lokacijas.precizna_lokacija, inače terminal_acq.napomena',
        };

        $this->nalaz($red, self::PRECIZNA_LOKACIJA, 'Predlog: '.$predlog.'.', $vrednost);
    }

    /**
     * @param  list<array<string, mixed>>  $redovi
     */
    private function proveriStatus(array $redovi): void
    {
        $aktivniSn = [];
        foreach ($redovi as $red) {
            if ($red['sn'] !== null && in_array($red['status'], self::AKTIVNI_STATUSI, true)) {
                $aktivniSn[$red['sn']] = true;
            }
        }

        foreach ($redovi as $red) {
            if ($red['status_aplikacija'] === null) {
                continue;
            }

            $kodKorisnika = $red['lokacija']?->lokacija_tipId === self::LOKACIJA_TIP_KORISNIK;
            $instaliranUAplikaciji = in_array($red['status_aplikacija'], self::INSTALIRAN_U_APLIKACIJI, true);

            if ($red['status'] === 'Instaliran' && ! ($kodKorisnika && $instaliranUAplikaciji)) {
                $this->nalaz(
                    $red,
                    self::STATUS_SE_NE_SLAZE,
                    $kodKorisnika
                        ? 'U Excelu instaliran; uređaj je kod korisnika, ali status u aplikaciji nije ažuriran. Posle uvoza važi status iz aplikacije.'
                        : 'U Excelu instaliran, a uređaj u aplikaciji nije kod korisnika. Posle uvoza važi status iz aplikacije.',
                    'Instaliran',
                    $this->opisStanja($red)
                );
            }

            if (in_array($red['status'], self::ZATVORENI_STATUSI, true)
                && ! isset($aktivniSn[$red['sn']])
                && $kodKorisnika
                && $instaliranUAplikaciji
                && $red['pib'] !== null
                && $red['pib'] === HalkNormalizator::pib($red['lokacija']->pib)) {
                $this->nalaz($red, self::STATUS_SE_NE_SLAZE, 'U Excelu zatvoren, a uređaj je u aplikaciji i dalje instaliran kod istog trgovca (isti PIB) i nije aktivan ni pod jednim drugim TID-om.', $red['status'], $this->opisStanja($red));
            }
        }
    }

    /**
     * @param  list<array<string, mixed>>  $redovi
     */
    private function proveriDuplikate(array $redovi): void
    {
        $poTid = [];
        $aktivniTidPoSn = [];
        foreach ($redovi as $red) {
            if ($red['tid'] !== null) {
                $poTid[$red['tid']][] = $red;
            }
            if ($red['sn'] !== null && $red['tid'] !== null && in_array($red['status'], self::AKTIVNI_STATUSI, true)) {
                $aktivniTidPoSn[$red['sn']][$red['tid']][] = $red;
            }
        }

        foreach ($poTid as $tid => $grupa) {
            if (count($grupa) < 2) {
                continue;
            }

            $aktivnih = count(array_filter($grupa, fn (array $red): bool => in_array($red['status'], self::AKTIVNI_STATUSI, true)));
            $redoviExcel = implode(', ', array_column($grupa, 'red'));

            foreach ($grupa as $red) {
                $this->nalaz(
                    $red,
                    self::TID_KONFLIKT,
                    $aktivnih > 1
                        ? "TID je aktivan u više redova ({$redoviExcel}); samo jedan uređaj može biti pod TID-om."
                        : "Isti TID u više redova ({$redoviExcel}), najviše jedan aktivan: istorijski duplikat.",
                    $tid
                );
            }
        }

        foreach ($aktivniTidPoSn as $sn => $tidovi) {
            if (count($tidovi) < 2) {
                continue;
            }

            foreach ($tidovi as $grupa) {
                foreach ($grupa as $red) {
                    $this->nalaz($red, self::TID_KONFLIKT, 'Uređaj je aktivan pod više TID-ova: '.implode(', ', array_keys($tidovi)).'.', $sn);
                }
            }
        }
    }

    /**
     * Jedna lokacija u aplikaciji sa više HALK MID-ova ili MCC-ova među instaliranim redovima.
     *
     * @param  list<array<string, mixed>>  $redovi
     */
    private function proveriLokacijeSaViseMid(array $redovi): void
    {
        $poLokaciji = [];
        foreach ($redovi as $red) {
            if ($red['status'] === 'Instaliran' && $red['lokacija']?->lokacija_tipId === self::LOKACIJA_TIP_KORISNIK) {
                $poLokaciji[$red['lokacija']->id][] = $red;
            }
        }

        foreach ($poLokaciji as $grupa) {
            $midovi = array_unique(array_filter(array_column($grupa, 'acq_mid')));
            $mccovi = array_unique(array_filter(array_column($grupa, 'mcc')));
            if (count($midovi) < 2 && count($mccovi) < 2) {
                continue;
            }

            foreach ($grupa as $red) {
                $this->nalaz(
                    $red,
                    self::LOKACIJA_VISE_MID,
                    'Lokacija pokriva više HALK trgovaca: podeliti lokaciju u aplikaciji ili prihvatiti jedan MID.',
                    'MID '.$red['acq_mid'].', MCC '.$red['mcc'],
                    'MID-ovi: '.implode(', ', $midovi).'; MCC: '.implode(', ', $mccovi)
                );
            }
        }
    }

    /**
     * @param  array<string, mixed>  $red
     */
    private function opisStanja(array $red): string
    {
        return $red['status_aplikacija'].' / '.($red['lokacija']?->l_naziv ?? 'bez lokacije');
    }

    /**
     * Ime bez dijakritike i velikih slova, reči sortirane ("Jokić Jasmina" = "JASMINA JOKIĆ").
     */
    private function uporedivoIme(?string $ime): ?string
    {
        $ime = HalkNormalizator::tekst($ime);
        if ($ime === null) {
            return null;
        }

        $reci = explode(' ', Str::lower(Str::ascii($ime)));
        sort($reci);

        return implode(' ', $reci);
    }

    private function uporediveCifre(?string $telefon): ?string
    {
        $cifre = preg_replace('/\D/', '', (string) $telefon);
        $cifre = preg_replace('/^(00)?381/', '', $cifre);
        $cifre = ltrim($cifre, '0');

        return strlen($cifre) < 6 ? null : $cifre;
    }

    /**
     * @param  array<string, mixed>  $red
     */
    private function nalaz(array $red, int $kategorija, string $opis, ?string $excel = null, ?string $aplikacija = null): void
    {
        $this->nalazi[] = [
            'red' => $red['red'],
            'kategorija' => $kategorija,
            'naziv' => self::KATEGORIJE[$kategorija],
            'tid' => $red['tid'] ?? $red['tid_sirovo'],
            'sn' => $red['sn'] ?? $red['sn_sirovo'],
            'lokacijaId' => ($red['lokacija'] ?? null)?->id,
            'excel' => $excel,
            'aplikacija' => $aplikacija,
            'opis' => $opis,
        ];
    }
}
