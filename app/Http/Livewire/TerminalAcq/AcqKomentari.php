<?php

namespace App\Http\Livewire\TerminalAcq;

use App\Actions\TerminalAcq\TerminalAcqKomentari;
use App\Models\PozicijaPrikazStranica;
use App\Models\TerminalAcq;
use App\Models\TerminalAcqComment;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;
use RuntimeException;

/**
 * Komentari ACQ TID-a u modalu stranice ACQ terminali: svi sa pravom na stranicu dodaju,
 * a briše autor, Admin ili Call centar (meko). Ispod se, samo za čitanje, prikazuju
 * komentari uređaja ako je TID pod uređajem.
 */
class AcqKomentari extends Component
{
    #[Locked]
    public int $terminalAcqId;

    public string $noviKomentar = '';

    #[Locked]
    public ?string $greska = null;

    public function mount(int $terminalAcqId): void
    {
        $this->proveriPristup();
        $this->terminalAcqId = TerminalAcq::findOrFail($terminalAcqId)->id;
    }

    public function posalji(TerminalAcqKomentari $komentari): void
    {
        $this->proveriPristup();
        $this->validate(
            ['noviKomentar' => ['required', 'min:3', 'max:1000']],
            [
                'noviKomentar.required' => 'Unesite komentar.',
                'noviKomentar.min' => 'Komentar mora imati bar 3 znaka.',
                'noviKomentar.max' => 'Komentar može imati najviše 1000 znakova.',
            ]
        );

        $komentari->dodaj(TerminalAcq::findOrFail($this->terminalAcqId), $this->noviKomentar, auth()->user());
        $this->reset(['noviKomentar', 'greska']);
        $this->dispatch('acqKomentarPromenjen');
    }

    public function obrisi(int $komentarId, TerminalAcqKomentari $komentari): void
    {
        $this->proveriPristup();
        $komentar = TerminalAcqComment::query()->where('terminal_acqId', $this->terminalAcqId)->findOrFail($komentarId);

        try {
            $komentari->obrisi($komentar, auth()->user(), AcqTerminali::mozeUpravljati());
            $this->greska = null;
        } catch (RuntimeException $e) {
            $this->greska = $e->getMessage();

            return;
        }
        $this->dispatch('acqKomentarPromenjen');
    }

    public function render(): View
    {
        $acq = TerminalAcq::query()->with(['comments.user', 'terminal', 'terminalLokacija'])->findOrFail($this->terminalAcqId);

        return view('livewire.terminal-acq.acq-komentari', [
            'acq' => $acq,
            'mozeUpravljati' => AcqTerminali::mozeUpravljati(),
        ]);
    }

    private function proveriPristup(): void
    {
        abort_unless(PozicijaPrikazStranica::isRoleHasRightToAccess(auth()->user()->pozicija_tipId, AcqTerminali::ROUTE_NAME), 403);
    }
}
