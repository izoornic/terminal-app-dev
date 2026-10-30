<?php

namespace App\Http\Livewire\TerminalAcq;

use App\Actions\TerminalAcq\TerminalAcqLista;
use App\Models\LokacijaKontaktOsoba;
use App\Models\PozicijaPrikazStranica;
use App\Models\TerminalAcq;
use App\Models\TerminalStatusTip;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Config;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Lista ACQ TID-ova (Faza 4): pretraga, filteri po efektivnom statusu i detalj sa istorijom.
 */
class AcqTerminali extends Component
{
    use WithPagination;

    /**
     * Pravo na stranicu (pozicija_prikaz_stranicas) važi i za akcije komponente,
     * jer Livewire zahtevi ne prolaze kroz accessrole middleware.
     */
    public const ROUTE_NAME = 'acq-terminali';

    public string $searchTid = '';

    public string $searchSn = '';

    public string $searchTrgovac = '';

    public string $searchPib = '';

    public string $searchMid = '';

    public ?int $searchStatus = null;

    public ?string $brziFilter = null;

    #[Locked]
    public ?int $detaljId = null;

    public bool $detaljVisible = false;

    public function mount(): void
    {
        $this->proveriPristup();
    }

    public function updated(string $svojstvo): void
    {
        if (str_starts_with($svojstvo, 'search') || $svojstvo === 'brziFilter') {
            $this->resetPage();
        }
    }

    public function postaviBrziFilter(?string $filter): void
    {
        $this->brziFilter = $this->brziFilter === $filter ? null : $filter;
        $this->resetPage();
    }

    public function prikaziDetalj(int $id): void
    {
        $this->proveriPristup();
        $this->detaljId = TerminalAcq::findOrFail($id)->id;
        $this->detaljVisible = true;
    }

    #[On('tidDodat')]
    public function osvezi(): void
    {
        $this->resetPage();
    }

    public function render(TerminalAcqLista $lista): View
    {
        return view('livewire.terminal-acq.acq-terminali', [
            'data' => $this->read($lista),
            'statusi' => TerminalStatusTip::tidStatusList(),
            'statusiFilter' => $lista->statusiUpotrebljeni(),
            'brziFilteri' => TerminalAcqLista::brziFilteri(),
            'detalj' => $this->detaljVisible ? $this->detalj() : null,
        ]);
    }

    private function read(TerminalAcqLista $lista): LengthAwarePaginator
    {
        return $lista->upit([
            'tid' => $this->searchTid,
            'sn' => $this->searchSn,
            'trgovac' => $this->searchTrgovac,
            'pib' => $this->searchPib,
            'mid' => $this->searchMid,
            'statusId' => $this->searchStatus,
            'brzi' => $this->brziFilter,
        ])->paginate(Config::get('global.terminal_paginate'));
    }

    /**
     * @return array{acq: TerminalAcq, statusId: ?int, lokacija: ?\App\Models\Lokacija, kontakti: \Illuminate\Support\Collection<int, LokacijaKontaktOsoba>, istorija: \Illuminate\Support\Collection<int, \App\Models\TerminalAcqHistory>}|null
     */
    private function detalj(): ?array
    {
        $acq = TerminalAcq::query()
            ->with(['terminal', 'terminalLokacija', 'trazeniTerminalTip', 'prethodni', 'naslednik', 'histories.terminal', 'histories.lokacija'])
            ->find($this->detaljId);
        if ($acq === null) {
            return null;
        }

        $lokacija = $acq->efektivnaLokacija();

        return [
            'acq' => $acq,
            'statusId' => $acq->efektivniStatusId(),
            'lokacija' => $lokacija,
            'kontakti' => $lokacija !== null ? LokacijaKontaktOsoba::query()->where('lokacijaId', $lokacija->id)->get() : collect(),
            'istorija' => $acq->histories->reverse()->values(),
        ];
    }

    /**
     * Stranicu i akcije vide samo pozicije kojima je dodeljena ruta (Admin i Call centar)
     */
    private function proveriPristup(): void
    {
        abort_unless(PozicijaPrikazStranica::isRoleHasRightToAccess(auth()->user()->pozicija_tipId, self::ROUTE_NAME), 403);
    }
}
