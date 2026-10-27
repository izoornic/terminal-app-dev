<?php

namespace App\Actions\TerminalAcq;

use App\Models\Lokacija;
use App\Models\TerminalAcq;
use App\Models\TerminalTip;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

/**
 * Faza 3b: čita Excel zahteva banke (instalacija ili prekonfiguracija) i za svaki red
 * vraća predlog sa greškama i upozorenjima. Ne upisuje ništa.
 *
 * Red je prekonfiguracija kad "dodatne opcije" sadrže "prekonfiguracija PNxxxxxx";
 * taj TID je stari TID. Redovi su nizovi skalara, pa mogu da žive u Livewire stanju.
 */
class ProcitajZahtevBanke
{
    public const INSTALACIJA = 'instalacija';

    public const PREKONFIGURACIJA = 'prekonfiguracija';

    public const LOKACIJA_POSTOJECA = 'postojeca';

    public const LOKACIJA_NOVA = 'nova';

    /**
     * @var list<string>
     */
    public const OBAVEZNE_KOLONE = ['acq_tid', 'acq_mid', 'naziv_trgovca', 'adresa', 'mesto', 'pib'];

    private const LOKACIJA_TIP_KORISNIK = 3;

    public function __construct(private HalkExcelCitac $citac) {}

    /**
     * @return array{datum_zahteva: string, redovi: list<array<string, mixed>>}
     *
     * @throws \RuntimeException kad fajl nema zaglavlje ili obavezne kolone
     */
    public function procitaj(string $putanja, string $nazivFajla): array
    {
        $fajlPrekonfiguracija = Str::contains(Str::lower($nazivFajla), 'prekonfiguracij');
        $redovi = [];
        $tidoviUFajlu = [];
        $noviPibUFajlu = [];

        foreach ($this->citac->procitaj($putanja, self::OBAVEZNE_KOLONE) as $sirov) {
            $red = $this->osnova($sirov);

            $this->proveriTid($red, $tidoviUFajlu);
            if ($red['vrsta'] === self::PREKONFIGURACIJA) {
                $this->proveriStariTid($red);
            } elseif ($fajlPrekonfiguracija) {
                $red['upozorenja'][] = 'Fajl je zahtev za prekonfiguraciju, a red nema "prekonfiguracija PN…"; uvozi se kao instalacija.';
            }

            $this->predloziLokaciju($red, $noviPibUFajlu);
            $this->proveriPodatke($red);

            $red['izabran'] = $red['greske'] === [];
            $redovi[] = $red;
        }

        return ['datum_zahteva' => $this->datumIzNaziva($nazivFajla), 'redovi' => $redovi];
    }

    /**
     * @param  array<string, mixed>  $sirov
     * @return array<string, mixed>
     */
    private function osnova(array $sirov): array
    {
        $tid = HalkNormalizator::tid($sirov['acq_tid'] ?? null);
        $dodatneOpcije = HalkNormalizator::tekst($sirov['dodatne_opcije'] ?? null);
        $stariTid = preg_match('/prekonfiguracija\s+(PN\d{6})/i', (string) $dodatneOpcije, $pogodak) ? strtoupper($pogodak[1]) : null;
        $model = HalkNormalizator::tekst($sirov['model'] ?? null);

        return [
            'red' => $sirov['red'],
            'tid' => $tid['ispravan'] && $tid['stari_tid'] === null ? $tid['tid'] : null,
            'tid_sirovo' => HalkNormalizator::tekst($sirov['acq_tid'] ?? null),
            'testni' => $tid['testni'],
            'vrsta' => $stariTid !== null ? self::PREKONFIGURACIJA : self::INSTALACIJA,
            'stari_tid' => $stariTid,
            'stari_acq_id' => null,
            'stari_opis' => null,
            'naziv_trgovca' => HalkNormalizator::tekst($sirov['naziv_trgovca'] ?? null),
            'naziv_na_slipu' => HalkNormalizator::tekst($sirov['naziv_na_slipu'] ?? null),
            'adresa' => HalkNormalizator::tekst($sirov['adresa'] ?? null),
            'mesto' => HalkNormalizator::tekst($sirov['mesto'] ?? null),
            'ptt' => HalkNormalizator::ptt($sirov['ptt'] ?? null),
            'pib' => HalkNormalizator::pib($sirov['pib'] ?? null),
            'acq_mid' => HalkNormalizator::mid($sirov['acq_mid'] ?? null) ?? HalkNormalizator::mid($sirov['sifra_trgovca'] ?? null),
            'mcc' => HalkNormalizator::mcc($sirov['mcc'] ?? null),
            'kontakt' => HalkNormalizator::tekst($sirov['kontakt'] ?? null),
            'telefon' => HalkNormalizator::telefonZaKontakt($sirov['telefon'] ?? null),
            'komunikacija' => HalkNormalizator::komunikacija($sirov['komunikacija'] ?? null),
            'dodatne_opcije' => $dodatneOpcije,
            'model' => $model,
            'trazeni_terminal_tipId' => $this->modelId($model),
            'lokacija_akcija' => self::LOKACIJA_NOVA,
            'lokacijaId' => null,
            'regionId' => null,
            'opcije' => [],
            'sn' => '',
            'greske' => [],
            'upozorenja' => [],
        ];
    }

    /**
     * @param  array<string, mixed>  $red
     * @param  array<string, int|string>  $tidoviUFajlu
     */
    private function proveriTid(array &$red, array &$tidoviUFajlu): void
    {
        if ($red['tid'] === null) {
            $red['greske'][] = 'Neispravan TID "'.$red['tid_sirovo'].'".';

            return;
        }

        if (isset($tidoviUFajlu[$red['tid']])) {
            $red['greske'][] = "TID je već u redu {$tidoviUFajlu[$red['tid']]} ovog fajla.";

            return;
        }
        $tidoviUFajlu[$red['tid']] = $red['red'];

        if (TerminalAcq::query()->where('acq_tid', $red['tid'])->exists()) {
            $red['greske'][] = 'TID već postoji u aplikaciji; red se preskače.';
        }
    }

    /**
     * @param  array<string, mixed>  $red
     */
    private function proveriStariTid(array &$red): void
    {
        if ($red['stari_tid'] === $red['tid']) {
            $red['greske'][] = 'Stari i novi TID su isti.';

            return;
        }

        $stari = TerminalAcq::query()->with(['terminal', 'naslednik'])->where('acq_tid', $red['stari_tid'])->first();
        if ($stari === null) {
            $red['greske'][] = "Stari TID {$red['stari_tid']} ne postoji u aplikaciji.";

            return;
        }

        $lokacija = $stari->efektivnaLokacija();
        $red['stari_acq_id'] = $stari->id;
        $red['stari_opis'] = trim($stari->acq_tid.' – '.($lokacija?->l_naziv ?? 'bez lokacije').' (PIB '.($lokacija?->pib ?? '—').')'.($stari->terminal !== null ? ', SN '.$stari->terminal->sn : ''));

        if ($stari->terminalId === null) {
            $red['greske'][] = "Stari TID {$stari->acq_tid} nema uređaj (zatvoren ili čeka instalaciju).";
        }

        if ($stari->naslednik !== null) {
            $red['greske'][] = "Prekonfiguracija starog TID-a je već zatražena ({$stari->naslednik->acq_tid}).";
        }

        if ($stari->terminal !== null && $red['trazeni_terminal_tipId'] !== null && $stari->terminal->terminal_tipId !== $red['trazeni_terminal_tipId']) {
            $red['upozorenja'][] = 'Model iz zahteva nije model uređaja starog TID-a.';
        }

        if ($lokacija !== null) {
            $red['regionId'] = $lokacija->regionId;
            if ($red['pib'] !== null && HalkNormalizator::pib($lokacija->pib) === $red['pib']) {
                $red['lokacija_akcija'] = self::LOKACIJA_POSTOJECA;
                $red['lokacijaId'] = $lokacija->id;
            }
        }
    }

    /**
     * Postojeća lokacija trgovca po PIB-u (i adresi), ili nova lokacija. Više redova sa istim
     * novim PIB-om: prvi pravi lokaciju, a ostali sa drugom adresom postaju podlokacije.
     *
     * @param  array<string, mixed>  $red
     * @param  array<string, int|string>  $noviPibUFajlu
     */
    private function predloziLokaciju(array &$red, array &$noviPibUFajlu): void
    {
        $kandidati = $red['pib'] === null ? collect() : Lokacija::query()
            ->where('lokacija_tipId', self::LOKACIJA_TIP_KORISNIK)
            ->where('pib', $red['pib'])
            ->orderBy('id')
            ->get();

        $red['opcije'] = $kandidati
            ->map(fn (Lokacija $lokacija): array => ['id' => $lokacija->id, 'naziv' => trim($lokacija->l_naziv.' '.$lokacija->l_naziv_sufix).', '.$lokacija->adresa.', '.$lokacija->mesto])
            ->values()
            ->all();

        if ($red['lokacija_akcija'] === self::LOKACIJA_POSTOJECA) {
            $this->proveriMid($red, $kandidati->firstWhere('id', $red['lokacijaId']) ?? Lokacija::find($red['lokacijaId']));

            return;
        }

        $istaAdresa = $kandidati->first(fn (Lokacija $lokacija): bool => $this->uporedivaAdresa($lokacija->adresa) === $this->uporedivaAdresa($red['adresa']));

        if ($istaAdresa !== null) {
            $red['lokacija_akcija'] = self::LOKACIJA_POSTOJECA;
            $red['lokacijaId'] = $istaAdresa->id;
        } elseif ($kandidati->count() === 1) {
            $red['lokacija_akcija'] = self::LOKACIJA_POSTOJECA;
            $red['lokacijaId'] = $kandidati->first()->id;
            $red['upozorenja'][] = 'Lokacija sa ovim PIB-om ima drugu adresu: izaberite postojeću ili novu podlokaciju.';
        } elseif ($kandidati->count() > 1) {
            $red['lokacija_akcija'] = self::LOKACIJA_POSTOJECA;
            $red['upozorenja'][] = 'Više lokacija ima ovaj PIB: izaberite lokaciju ili novu podlokaciju.';
        } elseif ($red['pib'] !== null && isset($noviPibUFajlu[$red['pib']])) {
            $red['upozorenja'][] = "PIB je i u redu {$noviPibUFajlu[$red['pib']]}: ista adresa koristi istu novu lokaciju, druga adresa postaje podlokacija.";
        }

        if ($red['pib'] !== null && $kandidati->isEmpty()) {
            $noviPibUFajlu[$red['pib']] ??= $red['red'];
        }

        if ($red['lokacija_akcija'] === self::LOKACIJA_POSTOJECA && $red['lokacijaId'] !== null) {
            $this->proveriMid($red, $kandidati->firstWhere('id', $red['lokacijaId']));
        }
    }

    /**
     * @param  array<string, mixed>  $red
     */
    private function proveriMid(array &$red, ?Lokacija $lokacija): void
    {
        $midLokacije = HalkNormalizator::mid($lokacija?->acq_mid);
        if ($midLokacije === null || $red['acq_mid'] === null || $midLokacije === $red['acq_mid']) {
            return;
        }

        $red['upozorenja'][] = $red['vrsta'] === self::PREKONFIGURACIJA
            ? "MID lokacije biće zamenjen pri izvršenju prekonfiguracije: {$midLokacije} → {$red['acq_mid']}."
            : "MID lokacije ({$midLokacije}) se razlikuje od zahteva i ne menja se; izmena ide kroz formu lokacije.";
    }

    /**
     * @param  array<string, mixed>  $red
     */
    private function proveriPodatke(array &$red): void
    {
        foreach (['naziv_trgovca' => 'naziv trgovca', 'adresa' => 'adresa', 'mesto' => 'mesto', 'pib' => 'PIB'] as $polje => $naziv) {
            if ($red[$polje] === null) {
                $red['greske'][] = "Nedostaje {$naziv}.";
            }
        }

        if ($red['pib'] !== null && ! HalkNormalizator::pibIspravan($red['pib'])) {
            $red['upozorenja'][] = "PIB {$red['pib']} nema ispravnu kontrolnu cifru.";
        }

        $cifre = ltrim(preg_replace('/^(00)?381/', '', preg_replace('/\D/', '', (string) $red['telefon'])), '0');
        if ($red['telefon'] !== null && (strlen($cifre) < 8 || strlen($cifre) > 10)) {
            $red['upozorenja'][] = "Telefon \"{$red['telefon']}\" ima neočekivan broj cifara.";
        }

        if ($red['model'] !== null && $red['trazeni_terminal_tipId'] === null) {
            $red['upozorenja'][] = "Model \"{$red['model']}\" nije u šifarniku modela terminala.";
        }
    }

    private function modelId(?string $model): ?int
    {
        if ($model === null) {
            return null;
        }

        $trazeni = Str::lower(preg_replace('/\s+/', ' ', $model));

        return TerminalTip::all()
            ->first(fn (TerminalTip $tip): bool => Str::lower($tip->proizvodjac.' '.$tip->model) === $trazeni || Str::lower($tip->model) === $trazeni)
            ?->id;
    }

    private function uporedivaAdresa(?string $adresa): string
    {
        return preg_replace('/[^a-z0-9]/', '', Str::lower(Str::ascii((string) $adresa)));
    }

    private function datumIzNaziva(string $nazivFajla): string
    {
        if (preg_match('/(\d{1,2})\.(\d{1,2})\.(\d{4})/', $nazivFajla, $delovi)) {
            return CarbonImmutable::create((int) $delovi[3], (int) $delovi[2], (int) $delovi[1])->toDateString();
        }

        return CarbonImmutable::now()->toDateString();
    }
}
