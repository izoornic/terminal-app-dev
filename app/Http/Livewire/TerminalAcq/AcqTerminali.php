<?php

namespace App\Http\Livewire\TerminalAcq;

use App\Actions\TerminalAcq\DeinstalirajTerminalAcq;
use App\Actions\TerminalAcq\DodeliUredjajTerminalAcq;
use App\Actions\TerminalAcq\IzvrsiPrekonfiguraciju;
use App\Actions\TerminalAcq\OpozoviTerminalAcq;
use App\Actions\TerminalAcq\TerminalAcqLista;
use App\Models\Lokacija;
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
use RuntimeException;

/**
 * Lista ACQ TID-ova (Faza 4): pretraga, filteri po efektivnom statusu, detalj sa istorijom
 * i akcije nad TID-om (dodela uređaja, izvršenje prekonfiguracije, deinstalacija, opoziv).
 * Svaka akcija proverava pravo i stanje TID-a; pravila su u Action klasama.
 */
class AcqTerminali extends Component
{
    use WithPagination;

    /**
     * Pravo na stranicu (pozicija_prikaz_stranicas) važi i za akcije komponente,
     * jer Livewire zahtevi ne prolaze kroz accessrole middleware.
     */
    public const ROUTE_NAME = 'acq-terminali';

    /**
     * Pravo bez rute (red u stranicas, show_in_meni = 0) za akcije koje menjaju evidenciju:
     * Dodaj terminal, ručna prekonfiguracija, opoziv. Ostale akcije (dodela uređaja,
     * izvršenje prekonfiguracije, deinstalacija) ima svako ko ima pravo na stranicu.
     */
    public const PRAVO_UPRAVLJANJE = 'acq-terminali-upravljanje';

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

    public string $dodelaSn = '';

    public ?int $deinstalacijaLokacijaId = null;

    public ?int $deinstalacijaStatusId = null;

    #[Locked]
    public ?string $porukaAkcije = null;

    #[Locked]
    public ?string $greskaAkcije = null;

    #[Locked]
    public ?string $porukaListe = null;

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
        $this->reset(['dodelaSn', 'deinstalacijaLokacijaId', 'deinstalacijaStatusId', 'porukaAkcije', 'greskaAkcije']);
        $this->detaljVisible = true;
    }

    public function dodeliUredjaj(DodeliUredjajTerminalAcq $dodela): void
    {
        $this->akcija(function (TerminalAcq $acq) use ($dodela): string {
            if (trim($this->dodelaSn) === '') {
                throw new RuntimeException('Unesite SN uređaja.');
            }
            $upozorenja = $dodela->izvrsi($acq, $this->dodelaSn, auth()->user());

            return trim('Uređaj je dodeljen TID-u. '.implode(' ', $upozorenja));
        });
    }

    public function izvrsiPrekonfiguraciju(IzvrsiPrekonfiguraciju $prekonfiguracija): void
    {
        $this->akcija(fn (TerminalAcq $acq): string => trim('Prekonfiguracija je izvršena. '.implode(' ', $prekonfiguracija->izvrsi($acq, auth()->user()))));
    }

    public function deinstaliraj(DeinstalirajTerminalAcq $deinstalacija): void
    {
        $this->akcija(function (TerminalAcq $acq) use ($deinstalacija): string {
            if ($this->deinstalacijaLokacijaId === null || $this->deinstalacijaStatusId === null) {
                throw new RuntimeException('Izaberite lokaciju i status uređaja.');
            }
            $deinstalacija->izvrsi($acq, $this->deinstalacijaLokacijaId, $this->deinstalacijaStatusId, auth()->user());

            return 'TID je deinstaliran, a uređaj premešten.';
        });
    }

    public function opozovi(OpozoviTerminalAcq $opoziv): void
    {
        self::proveriUpravljanje();
        $this->akcija(function (TerminalAcq $acq) use ($opoziv): string {
            $opoziv->izvrsi($acq, auth()->user());

            return 'TID je opozvan.';
        });
    }

    #[On('tidDodat')]
    public function osvezi(?string $poruka = null): void
    {
        $this->porukaListe = $poruka;
        $this->resetPage();
    }

    /**
     * Ručna prekonfiguracija aktivnog TID-a (zahtev stigao mailom): otvara formu RucnaPrekonfiguracija.
     */
    public function otvoriPrekonfiguraciju(): void
    {
        self::proveriUpravljanje();
        $acq = TerminalAcq::findOrFail($this->detaljId);
        if ($acq->terminalId === null) {
            return;
        }

        $this->detaljVisible = false;
        $this->dispatch('rucnaPrekonfiguracija', stariId: $acq->id);
    }

    public function render(TerminalAcqLista $lista): View
    {
        return view('livewire.terminal-acq.acq-terminali', [
            'data' => $this->read($lista),
            'statusi' => TerminalStatusTip::tidStatusList(),
            'statusiFilter' => $lista->statusiUpotrebljeni(),
            'brziFilteri' => TerminalAcqLista::brziFilteri(),
            'detalj' => $this->detaljVisible ? $this->detalj() : null,
            'ciljneLokacije' => Lokacija::query()->whereIn('lokacija_tipId', DeinstalirajTerminalAcq::CILJNI_TIPOVI_LOKACIJE)
                ->orderBy('lokacija_tipId')->orderBy('l_naziv')->pluck('l_naziv', 'id'),
            'statusiUredjaja' => TerminalStatusTip::tipoviList(),
            'statusZaInstalacijuId' => TerminalStatusTip::idPoNazivu(TerminalStatusTip::ZA_INSTALACIJU),
            'statusCekaPrekonfiguracijuId' => TerminalStatusTip::idPoNazivu(TerminalStatusTip::CEKA_PREKONFIGURACIJU),
            'mozeUpravljati' => self::mozeUpravljati(),
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
     * Zajednički tok akcije nad TID-om iz detalja: pravo, učitavanje TID-a iz baze,
     * poruka o uspehu ili grešci (RuntimeException iz Action klase).
     *
     * @param  callable(TerminalAcq): string  $izvrsi
     */
    private function akcija(callable $izvrsi): void
    {
        $this->proveriPristup();
        $this->porukaAkcije = null;
        $this->greskaAkcije = null;

        try {
            $this->porukaAkcije = $izvrsi(TerminalAcq::findOrFail($this->detaljId));
            $this->reset(['dodelaSn', 'deinstalacijaLokacijaId', 'deinstalacijaStatusId']);
        } catch (RuntimeException $e) {
            $this->greskaAkcije = $e->getMessage();
        }
    }

    /**
     * Da li prijavljeni korisnik sme da dodaje TID, radi ručnu prekonfiguraciju i opoziv
     * (Admin i Call centar; čita se iz pozicija_prikaz_stranicas).
     */
    public static function mozeUpravljati(): bool
    {
        return PozicijaPrikazStranica::isRoleHasRightToAccess(auth()->user()->pozicija_tipId, self::PRAVO_UPRAVLJANJE);
    }

    public static function proveriUpravljanje(): void
    {
        abort_unless(self::mozeUpravljati(), 403);
    }

    /**
     * Stranicu vide pozicije kojima je dodeljena ruta (Admin, Call centar, Šef servisa, Serviser)
     */
    private function proveriPristup(): void
    {
        abort_unless(PozicijaPrikazStranica::isRoleHasRightToAccess(auth()->user()->pozicija_tipId, self::ROUTE_NAME), 403);
    }
}
