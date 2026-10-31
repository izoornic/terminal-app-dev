<?php

namespace App\Http\Livewire\TerminalAcq;

use App\Actions\TerminalAcq\DodajTerminalAcq;
use App\Actions\TerminalAcq\HalkNormalizator;
use App\Models\Lokacija;
use App\Models\LokacijaKontaktOsoba;
use App\Models\PozicijaPrikazStranica;
use App\Models\Terminal;
use App\Models\TerminalAcq;
use App\Models\TerminalLokacija;
use App\Models\TerminalStatusTip;
use App\Models\TerminalVendor;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;
use RuntimeException;

/**
 * "Dodaj terminal": novi TID uz uređaj koji već ima red u terminal_lokacijas.
 *
 * Status i lokacija TID-a se nasleđuju od uređaja; polja lokacije (MID, MCC, PTT)
 * mogu se upisati samo ako su prazna. Pravila su u DodajTerminalAcq.
 */
class DodajTerminal extends Component
{
    /**
     * @var list<string>
     */
    public const KOMUNIKACIJE = ['GPRS', 'WIFI', 'ETH'];

    public bool $modalVisible = false;

    public string $acq_tid = '';

    public string $sn = '';

    public string $naziv_na_slipu = '';

    public string $komunikacija = '';

    public string $iccid = '';

    public string $inventarni_broj = '';

    public string $dodatne_opcije = '';

    public string $napomena = '';

    public string $datum_zahteva = '';

    public string $datum_instalacije = '';

    public bool $testni = false;

    public string $acq_mid = '';

    public string $mcc = '';

    public string $ptt = '';

    public function mount(): void
    {
        $this->proveriPristupStranici();
    }

    #[On('dodajTerminal')]
    public function otvori(): void
    {
        AcqTerminali::proveriUpravljanje();
        $this->resetValidation();
        $this->reset(['acq_tid', 'sn', 'naziv_na_slipu', 'komunikacija', 'iccid', 'inventarni_broj', 'dodatne_opcije', 'napomena', 'datum_zahteva', 'datum_instalacije', 'testni', 'acq_mid', 'mcc', 'ptt']);
        $this->modalVisible = true;
    }

    public function sacuvaj(DodajTerminalAcq $dodaj): void
    {
        AcqTerminali::proveriUpravljanje();
        $this->acq_tid = strtoupper(preg_replace('/\s+/', '', $this->acq_tid));
        $this->sn = trim($this->sn);
        $this->validate();

        $uredjaj = $this->uredjaj();
        if ($uredjaj === null) {
            $this->addError('sn', 'Uređaj sa ovim SN nema lokaciju (red u terminal_lokacijas).');

            return;
        }

        try {
            $acq = $dodaj->saUredjajem(
                [
                    'acq_tid' => $this->acq_tid,
                    'naziv_na_slipu' => $this->prazno($this->naziv_na_slipu),
                    'komunikacija' => $this->prazno($this->komunikacija),
                    'iccid' => $this->prazno($this->iccid),
                    'inventarni_broj' => $this->prazno($this->inventarni_broj),
                    'dodatne_opcije' => $this->prazno($this->dodatne_opcije),
                    'napomena' => $this->prazno($this->napomena),
                    'datum_zahteva' => $this->prazno($this->datum_zahteva),
                    'datum_instalacije' => $this->prazno($this->datum_instalacije),
                    'testni' => $this->testni,
                    'izvor' => TerminalAcq::IZVOR_RUCNO,
                ],
                $uredjaj['terminal']->id,
                auth()->user(),
                [
                    'acq_mid' => HalkNormalizator::mid($this->acq_mid),
                    'mcc' => HalkNormalizator::mcc($this->mcc),
                    'ptt' => HalkNormalizator::ptt($this->ptt),
                ]
            );
        } catch (RuntimeException $e) {
            $this->addError(str_contains($e->getMessage(), 'TID') && ! str_contains($e->getMessage(), 'Uređaj') ? 'acq_tid' : 'sn', $e->getMessage());

            return;
        }

        $this->modalVisible = false;
        $this->dispatch('tidDodat', id: $acq->id);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'acq_tid' => ['required', 'regex:/^PN\d{6}$/', Rule::unique('terminal_acq', 'acq_tid')],
            'sn' => ['required', 'string', Rule::exists('terminals', 'sn')],
            'naziv_na_slipu' => ['nullable', 'string', 'max:64'],
            'komunikacija' => ['nullable', Rule::in(self::KOMUNIKACIJE)],
            'iccid' => ['nullable', 'string', 'max:22'],
            'inventarni_broj' => ['nullable', 'string', 'max:24'],
            'dodatne_opcije' => ['nullable', 'string', 'max:1000'],
            'napomena' => ['nullable', 'string', 'max:1000'],
            'datum_zahteva' => ['nullable', 'date'],
            'datum_instalacije' => ['nullable', 'date'],
            'acq_mid' => ['nullable', 'regex:/^\d{1,15}$/'],
            'mcc' => ['nullable', 'regex:/^\d{1,4}$/'],
            'ptt' => ['nullable', 'regex:/^\d{5}$/'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'acq_tid.required' => 'Unesite ACQ TID.',
            'acq_tid.regex' => 'TID mora biti oblika PN i 6 cifara (npr. PN005313).',
            'acq_tid.unique' => 'Ovaj TID već postoji.',
            'sn.required' => 'Unesite SN uređaja.',
            'sn.exists' => 'Uređaj sa ovim SN ne postoji.',
            'naziv_na_slipu.max' => 'Naziv na slipu može imati najviše 64 znaka.',
            'komunikacija.in' => 'Izaberite komunikaciju iz liste.',
            'iccid.max' => 'ICCID može imati najviše 22 znaka.',
            'inventarni_broj.max' => 'Inventarni broj može imati najviše 24 znaka.',
            'datum_zahteva.date' => 'Datum zahteva nije ispravan.',
            'datum_instalacije.date' => 'Datum instalacije nije ispravan.',
            'acq_mid.regex' => 'MID sme imati samo cifre (do 15).',
            'mcc.regex' => 'MCC sme imati samo cifre (do 4).',
            'ptt.regex' => 'PTT mora imati 5 cifara.',
        ];
    }

    public function render(): View
    {
        return view('livewire.terminal-acq.dodaj-terminal', [
            'uredjaj' => $this->modalVisible ? $this->uredjaj() : null,
            'komunikacije' => self::KOMUNIKACIJE,
        ]);
    }

    /**
     * Uređaj po SN-u sa onim što TID od njega nasleđuje (lokacija, status), za prikaz i proveru.
     *
     * @return array{terminal: Terminal, terminalLokacija: TerminalLokacija, lokacija: ?Lokacija, status: ?string, vendor: ?string, kontakti: \Illuminate\Support\Collection<int, LokacijaKontaktOsoba>, aktivniTid: ?string, upozorenje: ?string}|null
     */
    private function uredjaj(): ?array
    {
        $sn = trim($this->sn);
        if ($sn === '') {
            return null;
        }

        $terminal = Terminal::query()->where('sn', $sn)->first();
        $terminalLokacija = $terminal !== null ? TerminalLokacija::query()->where('terminalId', $terminal->id)->first() : null;
        if ($terminalLokacija === null) {
            return null;
        }

        $lokacija = Lokacija::find($terminalLokacija->lokacijaId);
        $status = TerminalStatusTip::find($terminalLokacija->terminal_statusId)?->ts_naziv;

        return [
            'terminal' => $terminal,
            'terminalLokacija' => $terminalLokacija,
            'lokacija' => $lokacija,
            'status' => $status,
            'vendor' => $terminal->vendor_id !== null ? TerminalVendor::find($terminal->vendor_id)?->name : null,
            'kontakti' => $lokacija !== null ? LokacijaKontaktOsoba::query()->where('lokacijaId', $lokacija->id)->get() : collect(),
            'aktivniTid' => TerminalAcq::query()->where('terminalId', $terminal->id)->value('acq_tid'),
            'upozorenje' => $lokacija !== null && in_array($lokacija->lokacija_tipId, [1, 2], true)
                ? 'Uređaj je u magacinu ili servisnom centru; TID će nasleđivati tu lokaciju dok se uređaj ne premesti.'
                : null,
        ];
    }

    private function prazno(string $vrednost): ?string
    {
        $vrednost = trim($vrednost);

        return $vrednost === '' ? null : $vrednost;
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
