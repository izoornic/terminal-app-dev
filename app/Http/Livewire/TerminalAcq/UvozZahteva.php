<?php

namespace App\Http\Livewire\TerminalAcq;

use App\Actions\TerminalAcq\ProcitajZahtevBanke;
use App\Actions\TerminalAcq\UpisiZahtevBanke;
use App\Models\PozicijaPrikazStranica;
use App\Models\Region;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use RuntimeException;

/**
 * Faza 3b: uvoz Excel zahteva banke (instalacija / prekonfiguracija).
 *
 * Fajl se arhivira u storage/app/halk-zahtevi/, pa se iz njega pravi pregled. Pri
 * potvrdi se fajl ponovo čita sa servera; iz browsera se uzimaju samo izbori
 * korisnika (red, lokacija, region, SN), ne i podaci reda.
 */
class UvozZahteva extends Component
{
    use WithFileUploads;

    /**
     * Pravo na stranicu (pozicija_prikaz_stranicas) važi i za akcije komponente,
     * jer Livewire zahtevi ne prolaze kroz accessrole middleware.
     */
    public const ROUTE_NAME = 'halk-uvoz-zahteva';

    public const NOVA_LOKACIJA = 'nova';

    private const ARHIVA = 'halk-zahtevi';

    public ?TemporaryUploadedFile $fajl = null;

    #[Locked]
    public ?string $putanja = null;

    #[Locked]
    public ?string $nazivFajla = null;

    public string $datumZahteva = '';

    /**
     * Pregled (samo za prikaz; pri potvrdi se ponovo čita iz fajla).
     *
     * @var list<array<string, mixed>>
     */
    public array $redovi = [];

    /**
     * Izbori korisnika po broju reda: izabran, lokacija ('nova' ili id), regionId, sn.
     *
     * @var array<int|string, array{izabran: bool, lokacija: string, regionId: int|string|null, sn: string}>
     */
    public array $izbori = [];

    public ?int $regionZaSve = null;

    /**
     * @var array{tidova: int, lokacija: int, kontakata: int, upozorenja: list<string>}|null
     */
    #[Locked]
    public ?array $rezultat = null;

    #[Locked]
    public ?string $greska = null;

    public function mount(): void
    {
        $this->proveriPristup();
    }

    public function procitaj(ProcitajZahtevBanke $citac): void
    {
        $this->proveriPristup();
        $this->validate(
            ['fajl' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240']],
            [
                'fajl.required' => 'Izaberite Excel fajl zahteva.',
                'fajl.mimes' => 'Fajl mora biti Excel (.xlsx ili .xls).',
                'fajl.max' => 'Fajl može imati najviše 10 MB.',
            ]
        );

        $this->resetPregleda();
        $this->nazivFajla = $this->fajl->getClientOriginalName();
        $this->putanja = $this->fajl->storeAs(self::ARHIVA, now()->format('Y-m-d_His').'_'.$this->nazivFajla, 'local');
        $this->fajl = null;

        try {
            $zahtev = $citac->procitaj(Storage::disk('local')->path($this->putanja), $this->nazivFajla);
        } catch (RuntimeException $e) {
            $this->greska = $e->getMessage();

            return;
        }

        $this->datumZahteva = $zahtev['datum_zahteva'];
        $this->redovi = $zahtev['redovi'];
        foreach ($zahtev['redovi'] as $red) {
            $this->izbori[$red['red']] = [
                'izabran' => $red['izabran'],
                'lokacija' => $red['lokacija_akcija'] === ProcitajZahtevBanke::LOKACIJA_NOVA ? self::NOVA_LOKACIJA : (string) $red['lokacijaId'],
                'regionId' => $red['regionId'],
                'sn' => '',
            ];
        }
    }

    public function primeniRegionZaSve(): void
    {
        $this->proveriPristup();

        foreach ($this->izbori as $red => $izbor) {
            if ($izbor['lokacija'] === self::NOVA_LOKACIJA) {
                $this->izbori[$red]['regionId'] = $this->regionZaSve;
            }
        }
    }

    public function potvrdi(ProcitajZahtevBanke $citac, UpisiZahtevBanke $upis): void
    {
        $this->proveriPristup();
        $this->validate(
            ['datumZahteva' => ['required', 'date']],
            ['datumZahteva.required' => 'Unesite datum zahteva.', 'datumZahteva.date' => 'Datum zahteva nije ispravan.']
        );

        if ($this->putanja === null || ! Storage::disk('local')->exists($this->putanja)) {
            $this->greska = 'Fajl zahteva više nije dostupan; učitajte ga ponovo.';

            return;
        }

        try {
            $zahtev = $citac->procitaj(Storage::disk('local')->path($this->putanja), $this->nazivFajla);
            $this->rezultat = $upis->upisi($zahtev['redovi'], $this->izboriZaUpis($zahtev['redovi']), $this->datumZahteva, $this->nazivFajla, auth()->user());
        } catch (RuntimeException $e) {
            $this->greska = $e->getMessage();

            return;
        }

        $this->greska = null;
        $this->redovi = [];
        $this->izbori = [];
    }

    public function otkazi(): void
    {
        $this->proveriPristup();
        $this->resetPregleda();
        $this->rezultat = null;
        $this->putanja = null;
        $this->nazivFajla = null;
    }

    public function render(): View
    {
        return view('livewire.terminal-acq.uvoz-zahteva', [
            'regioni' => Region::query()->orderBy('r_naziv')->pluck('r_naziv', 'id'),
            'brojIzabranih' => count(array_filter($this->izbori, fn (array $izbor): bool => (bool) $izbor['izabran'])),
        ]);
    }

    /**
     * Izbori u obliku za UpisiZahtevBanke; red sa greškom (po sveže pročitanom fajlu)
     * se nikad ne šalje kao izabran.
     *
     * @param  list<array<string, mixed>>  $redovi  iz ProcitajZahtevBanke, ne iz Livewire stanja
     * @return array<int|string, array{izabran: bool, lokacija_akcija: string, lokacijaId: ?int, regionId: ?int, sn: string}>
     */
    private function izboriZaUpis(array $redovi): array
    {
        $saGreskom = [];
        foreach ($redovi as $red) {
            $saGreskom[$red['red']] = $red['greske'] !== [];
        }

        $izbori = [];
        foreach ($this->izbori as $red => $izbor) {
            $nova = $izbor['lokacija'] === self::NOVA_LOKACIJA;
            $izbori[$red] = [
                'izabran' => (bool) $izbor['izabran'] && ! ($saGreskom[$red] ?? true),
                'lokacija_akcija' => $nova ? ProcitajZahtevBanke::LOKACIJA_NOVA : ProcitajZahtevBanke::LOKACIJA_POSTOJECA,
                'lokacijaId' => $nova || $izbor['lokacija'] === '' ? null : (int) $izbor['lokacija'],
                'regionId' => $izbor['regionId'] !== null && $izbor['regionId'] !== '' ? (int) $izbor['regionId'] : null,
                'sn' => trim((string) $izbor['sn']),
            ];
        }

        return $izbori;
    }

    private function resetPregleda(): void
    {
        $this->resetValidation();
        $this->greska = null;
        $this->redovi = [];
        $this->izbori = [];
        $this->datumZahteva = '';
        $this->regionZaSve = null;
    }

    /**
     * Stranicu i akcije vide samo pozicije kojima je dodeljena ruta (Admin i Call centar)
     */
    private function proveriPristup(): void
    {
        abort_unless(PozicijaPrikazStranica::isRoleHasRightToAccess(auth()->user()->pozicija_tipId, self::ROUTE_NAME), 403);
    }
}
