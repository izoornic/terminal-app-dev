<?php

namespace App\Http\Livewire\TerminalAcq;

use App\Actions\TerminalAcq\IzvrsiPrekonfiguraciju;
use App\Actions\TerminalAcq\ProcitajZahtevBanke;
use App\Actions\TerminalAcq\UpisiZahtevBanke;
use App\Models\LokacijaKontaktOsoba;
use App\Models\PozicijaPrikazStranica;
use App\Models\Region;
use App\Models\TerminalAcq;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use RuntimeException;

/**
 * Ručna prekonfiguracija (zahtev stigao mailom, ne Excelom), za sve slučajeve: ista firma,
 * nova firma na postojećoj lokaciji, nova firma na novoj lokaciji.
 *
 * Forma pravi isti red kao Excel zahteva ("prekonfiguracija <stari TID>" u dodatnim
 * opcijama), pa važe iste provere (ProcitajZahtevBanke) i isti upis (UpisiZahtevBanke),
 * sa izvorom "rucno". Podrazumevano novi TID čeka tehničara; uz "izvrši odmah" se u istoj
 * transakciji izvršava i prekonfiguracija (IzvrsiPrekonfiguraciju).
 */
class RucnaPrekonfiguracija extends Component
{
    public const NOVA_LOKACIJA = 'nova';

    public bool $modalVisible = false;

    #[Locked]
    public ?int $stariId = null;

    #[Locked]
    public string $stariTid = '';

    public string $novi_tid = '';

    public string $naziv_trgovca = '';

    public string $naziv_na_slipu = '';

    public string $adresa = '';

    public string $mesto = '';

    public string $ptt = '';

    public string $pib = '';

    public string $acq_mid = '';

    public string $mcc = '';

    public string $kontakt = '';

    public string $telefon = '';

    public string $komunikacija = '';

    public string $napomena = '';

    public string $datum_zahteva = '';

    public string $lokacija = '';

    public ?int $regionId = null;

    public bool $izvrsiOdmah = false;

    /**
     * Rezultat poslednje provere (greške, upozorenja, ponuđene lokacije) — samo za prikaz.
     *
     * @var array<string, mixed>|null
     */
    #[Locked]
    public ?array $pregled = null;

    #[Locked]
    public ?string $greska = null;

    public function mount(): void
    {
        $this->proveriPristupStranici();
    }

    #[On('rucnaPrekonfiguracija')]
    public function otvori(int $stariId): void
    {
        AcqTerminali::proveriUpravljanje();
        $stari = TerminalAcq::with('terminal')->findOrFail($stariId);
        if ($stari->terminalId === null) {
            return;
        }

        $this->resetValidation();
        $this->reset(['novi_tid', 'komunikacija', 'napomena', 'lokacija', 'regionId', 'izvrsiOdmah', 'pregled', 'greska']);

        $lokacija = $stari->efektivnaLokacija();
        $kontakt = $lokacija !== null ? LokacijaKontaktOsoba::query()->where('lokacijaId', $lokacija->id)->first() : null;

        $this->stariId = $stari->id;
        $this->stariTid = $stari->acq_tid;
        $this->naziv_trgovca = (string) $lokacija?->l_naziv;
        $this->naziv_na_slipu = (string) $stari->naziv_na_slipu;
        $this->adresa = (string) $lokacija?->adresa;
        $this->mesto = (string) $lokacija?->mesto;
        $this->ptt = (string) $lokacija?->ptt;
        $this->pib = (string) $lokacija?->pib;
        $this->acq_mid = (string) $lokacija?->acq_mid;
        $this->mcc = (string) $lokacija?->mcc;
        $this->kontakt = (string) $kontakt?->name;
        $this->telefon = (string) $kontakt?->tel;
        $this->komunikacija = (string) $stari->komunikacija;
        $this->datum_zahteva = now()->toDateString();
        $this->modalVisible = true;
    }

    /**
     * Provera bez upisa: greške, upozorenja i predlog lokacije, kao u pregledu Excel zahteva.
     */
    public function proveri(ProcitajZahtevBanke $citac): void
    {
        AcqTerminali::proveriUpravljanje();
        $this->greska = null;
        $red = $citac->procitajRucno($this->sirov());
        $this->pregled = $red;

        $ponudjene = array_map(fn (array $opcija): string => (string) $opcija['id'], $red['opcije']);
        if ($this->lokacija === '' || ($this->lokacija !== self::NOVA_LOKACIJA && ! in_array($this->lokacija, $ponudjene, true))) {
            $this->lokacija = $red['lokacija_akcija'] === ProcitajZahtevBanke::LOKACIJA_NOVA
                ? self::NOVA_LOKACIJA
                : (string) ($red['lokacijaId'] ?? '');
        }
        $this->regionId ??= $red['regionId'];
    }

    public function sacuvaj(ProcitajZahtevBanke $citac, UpisiZahtevBanke $upis, IzvrsiPrekonfiguraciju $prekonfiguracija): void
    {
        AcqTerminali::proveriUpravljanje();
        $this->validate(
            ['datum_zahteva' => ['required', 'date']],
            ['datum_zahteva.required' => 'Unesite datum zahteva.', 'datum_zahteva.date' => 'Datum zahteva nije ispravan.']
        );

        $red = $citac->procitajRucno($this->sirov());
        $this->pregled = $red;
        if ($red['greske'] !== []) {
            $this->greska = 'Ispravite greške pa sačuvajte ponovo.';

            return;
        }

        $nova = $this->lokacija === self::NOVA_LOKACIJA;
        $izbor = [
            'izabran' => true,
            'lokacija_akcija' => $nova ? ProcitajZahtevBanke::LOKACIJA_NOVA : ProcitajZahtevBanke::LOKACIJA_POSTOJECA,
            'lokacijaId' => $nova || $this->lokacija === '' ? null : (int) $this->lokacija,
            'regionId' => $this->regionId,
            'sn' => '',
        ];

        try {
            $obavestenja = DB::transaction(function () use ($red, $izbor, $upis, $prekonfiguracija): array {
                $upis->upisi([$red], [$red['red'] => $izbor], $this->datum_zahteva, null, auth()->user(), TerminalAcq::IZVOR_RUCNO);

                if (! $this->izvrsiOdmah) {
                    return [];
                }

                return $prekonfiguracija->izvrsi(TerminalAcq::query()->where('acq_tid', $red['tid'])->firstOrFail(), auth()->user());
            });
        } catch (RuntimeException $e) {
            $this->greska = $e->getMessage();

            return;
        }

        $this->modalVisible = false;
        $this->dispatch('tidDodat', poruka: trim(($this->izvrsiOdmah
            ? "Prekonfiguracija {$this->stariTid} → {$red['tid']} je izvršena."
            : "TID {$red['tid']} čeka prekonfiguraciju (stari {$this->stariTid}).").' '.implode(' ', $obavestenja)));
    }

    public function render(): View
    {
        return view('livewire.terminal-acq.rucna-prekonfiguracija', [
            'regioni' => Region::query()->orderBy('r_naziv')->pluck('r_naziv', 'id'),
            'komunikacije' => DodajTerminal::KOMUNIKACIJE,
        ]);
    }

    /**
     * Red u obliku HalkExcelCitac; stari TID ide u dodatne opcije kao u Excel zahtevu.
     *
     * @return array<string, mixed>
     */
    private function sirov(): array
    {
        return [
            'acq_tid' => $this->novi_tid,
            'acq_mid' => $this->acq_mid,
            'naziv_trgovca' => $this->naziv_trgovca,
            'naziv_na_slipu' => $this->naziv_na_slipu,
            'adresa' => $this->adresa,
            'mesto' => $this->mesto,
            'ptt' => $this->ptt,
            'pib' => $this->pib,
            'mcc' => $this->mcc,
            'telefon' => $this->telefon,
            'kontakt' => $this->kontakt,
            'komunikacija' => $this->komunikacija,
            'dodatne_opcije' => trim("prekonfiguracija {$this->stariTid}; ".$this->napomena, '; '),
        ];
    }

    /**
     * Komponenta je deo stranice ACQ terminali, pa se učitava uz pravo na stranicu;
     * same akcije traže pravo upravljanja (Admin i Call centar).
     */
    private function proveriPristupStranici(): void
    {
        abort_unless(PozicijaPrikazStranica::isRoleHasRightToAccess(auth()->user()->pozicija_tipId, AcqTerminali::ROUTE_NAME), 403);
    }
}
