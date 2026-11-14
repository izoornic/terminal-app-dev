<?php

namespace App\Http\Livewire\TerminalAcq;

use App\Models\PozicijaPrikazStranica;
use App\Models\TerminalAcqOpcija;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Šifarnik opcija ACQ TID-a (stranica acq-opcije, samo Admin). Opcija se ne briše nego
 * deaktivira: ne nudi se više na TID-ovima, a TID-ovi koji je imaju je i dalje vide.
 */
class AcqOpcije extends Component
{
    /**
     * Pravo na stranicu (pozicija_prikaz_stranicas) važi i za akcije komponente,
     * jer Livewire zahtevi ne prolaze kroz accessrole middleware.
     */
    public const ROUTE_NAME = 'acq-opcije';

    #[Locked]
    public ?int $modelId = null;

    public bool $newEditModalVisible = false;

    public bool $is_edit = false;

    public string $naziv = '';

    public string $opis = '';

    public ?int $redosled = null;

    public bool $aktivna = true;

    public function mount(): void
    {
        $this->proveriPristup();
    }

    #[On('novaAcqOpcija')]
    public function novaOpcija(): void
    {
        $this->proveriPristup();
        $this->resetValidation();
        $this->resetInputFields();
        $this->is_edit = false;
        $this->redosled = (int) TerminalAcqOpcija::query()->max('redosled') + 1;
        $this->newEditModalVisible = true;
    }

    public function sacuvajNovu(): void
    {
        $this->proveriPristup();
        $this->pripremiUnos();
        $this->validate();

        TerminalAcqOpcija::create($this->modelData());

        $this->resetInputFields();
        $this->newEditModalVisible = false;
    }

    public function showUpdateModal(int $id): void
    {
        $this->proveriPristup();
        $this->resetValidation();

        $model = TerminalAcqOpcija::findOrFail($id);
        $this->modelId = $model->id;
        $this->naziv = $model->naziv;
        $this->opis = (string) $model->opis;
        $this->redosled = $model->redosled;
        $this->aktivna = $model->aktivna;

        $this->is_edit = true;
        $this->newEditModalVisible = true;
    }

    public function sacuvajIzmene(): void
    {
        $this->proveriPristup();
        $this->pripremiUnos();
        $this->validate();

        TerminalAcqOpcija::findOrFail($this->modelId)->update($this->modelData());

        $this->resetInputFields();
        $this->newEditModalVisible = false;
    }

    public function promeniAktivnost(int $id): void
    {
        $this->proveriPristup();
        $model = TerminalAcqOpcija::findOrFail($id);
        $model->update(['aktivna' => ! $model->aktivna]);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'naziv' => ['required', 'string', 'max:64', Rule::unique('terminal_acq_opcijas', 'naziv')->ignore($this->modelId)],
            'opis' => ['nullable', 'string', 'max:255'],
            'redosled' => ['required', 'integer', 'min:1'],
            'aktivna' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'naziv.required' => 'Unesite naziv opcije.',
            'naziv.max' => 'Naziv može imati najviše 64 znaka.',
            'naziv.unique' => 'Opcija s ovim nazivom već postoji.',
            'opis.max' => 'Opis može imati najviše 255 znakova.',
            'redosled.required' => 'Unesite redosled.',
            'redosled.integer' => 'Redosled mora biti ceo broj.',
            'redosled.min' => 'Redosled mora biti veći od nule.',
        ];
    }

    /**
     * @return Collection<int, TerminalAcqOpcija>
     */
    public function read(): Collection
    {
        return TerminalAcqOpcija::query()->withCount('terminali')->poRedosledu()->get();
    }

    public function render(): View
    {
        return view('livewire.terminal-acq.acq-opcije', [
            'data' => $this->read(),
        ]);
    }

    private function pripremiUnos(): void
    {
        $this->naziv = trim(preg_replace('/\s+/u', ' ', $this->naziv));
        $this->opis = trim($this->opis);
    }

    /**
     * @return array{naziv: string, opis: ?string, redosled: ?int, aktivna: bool}
     */
    private function modelData(): array
    {
        return [
            'naziv' => $this->naziv,
            'opis' => $this->opis !== '' ? $this->opis : null,
            'redosled' => $this->redosled,
            'aktivna' => $this->aktivna,
        ];
    }

    private function resetInputFields(): void
    {
        $this->modelId = null;
        $this->naziv = '';
        $this->opis = '';
        $this->redosled = null;
        $this->aktivna = true;
    }

    /**
     * Stranicu i akcije vide samo pozicije kojima je dodeljena ruta (Admin)
     */
    private function proveriPristup(): void
    {
        abort_unless(PozicijaPrikazStranica::isRoleHasRightToAccess(auth()->user()->pozicija_tipId, self::ROUTE_NAME), 403);
    }
}
