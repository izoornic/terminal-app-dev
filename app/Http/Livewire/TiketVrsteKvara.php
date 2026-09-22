<?php

namespace App\Http\Livewire;

use App\Models\PozicijaPrikazStranica;
use App\Models\TiketOpisKvaraTip;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class TiketVrsteKvara extends Component
{
    use WithPagination;

    /**
     * Pravo na stranicu (pozicija_prikaz_stranicas) važi i za akcije komponente,
     * jer Livewire zahtevi ne prolaze kroz accessrole middleware.
     */
    public const ROUTE_NAME = 'tiket-vrste-kvara';

    #[Locked]
    public ?int $modelId = null;

    public bool $newEditModalVisible = false;
    public bool $is_edit = false;
    public bool $deleteModalVisible = false;

    //NEW EDIT
    public string $tok_naziv = '';
    public ?int $tok_dodela_nadleznostiId = null;
    public ?int $list_order = null;

    //DELETE
    #[Locked]
    public int $broj_tiketa = 0;
    #[Locked]
    public int $broj_akcija = 0;

    //SEARCH
    public string $searchNaziv = '';
    public ?int $searchNadleznost = null;

    public function mount(): void
    {
        $this->proveriPristup();
    }

    #[On('newVrstaKvara')]
    public function newVrstaKvara(): void
    {
        $this->proveriPristup();
        $this->resetValidation();
        $this->resetInputFields();
        $this->is_edit = false;
        $this->list_order = (int) TiketOpisKvaraTip::query()->max('list_order') + 1;
        $this->newEditModalVisible = true;
    }

    public function saveNewVrstaKvara(): void
    {
        $this->proveriPristup();
        $this->tok_naziv = trim($this->tok_naziv);
        $this->validate();

        TiketOpisKvaraTip::create([...$this->modelData(), 'termnal_tipId' => TiketOpisKvaraTip::TERMINAL_TIP]);

        $this->resetInputFields();
        $this->newEditModalVisible = false;
    }

    public function showUpdateModal(int $id): void
    {
        $this->proveriPristup();
        $this->resetValidation();
        $this->loadModel($id);
        $this->is_edit = true;
        $this->newEditModalVisible = true;
    }

    public function updateVrstaKvara(): void
    {
        $this->proveriPristup();
        $this->tok_naziv = trim($this->tok_naziv);
        $this->validate();

        TiketOpisKvaraTip::findOrFail($this->modelId)->update($this->modelData());

        $this->resetInputFields();
        $this->newEditModalVisible = false;
    }

    public function showDeleteModal(int $id): void
    {
        $this->proveriPristup();
        $model = $this->loadModel($id);
        $this->broj_tiketa = $model->tiketi()->count();
        $this->broj_akcija = $model->akcije()->count();
        $this->deleteModalVisible = true;
    }

    /**
     * Briše vrstu kvara zajedno s koracima za servisera. Vrsta koju koristi bar jedan tiket se ne briše
     * (broj se ponovo čita iz baze, tiket je mogao nastati dok je modal bio otvoren).
     */
    public function deleteVrstaKvara(): void
    {
        $this->proveriPristup();
        $model = TiketOpisKvaraTip::findOrFail($this->modelId);

        $this->broj_tiketa = $model->tiketi()->count();
        if ($this->broj_tiketa > 0) {
            return;
        }

        DB::transaction(function () use ($model): void {
            $model->akcije()->delete();
            $model->delete();
        });

        $this->resetInputFields();
        $this->deleteModalVisible = false;
    }

    public function updatedSearchNaziv(): void
    {
        $this->resetPage();
    }

    public function updatedSearchNadleznost(): void
    {
        $this->resetPage();
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'tok_naziv' => ['required', 'string', 'max:128', Rule::unique('tiket_opis_kvara_tips', 'tok_naziv')->ignore($this->modelId)],
            'tok_dodela_nadleznostiId' => ['required', 'integer', Rule::in(array_keys(TiketOpisKvaraTip::nadleznosti()))],
            'list_order' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'tok_naziv.required' => 'Unesite naziv vrste kvara.',
            'tok_naziv.max' => 'Naziv može imati najviše 128 znakova.',
            'tok_naziv.unique' => 'Vrsta kvara s ovim nazivom već postoji.',
            'tok_dodela_nadleznostiId.required' => 'Izaberite nadležnost.',
            'tok_dodela_nadleznostiId.in' => 'Izaberite nadležnost iz liste.',
            'list_order.required' => 'Unesite redosled u listi.',
            'list_order.integer' => 'Redosled mora biti ceo broj.',
            'list_order.min' => 'Redosled mora biti veći od nule.',
        ];
    }

    public function read(): LengthAwarePaginator
    {
        return TiketOpisKvaraTip::query()
            ->withCount('tiketi')
            ->when($this->searchNaziv !== '', fn (Builder $query) => $query->where('tok_naziv', 'like', '%'.$this->searchNaziv.'%'))
            ->when($this->searchNadleznost, fn (Builder $query) => $query->where('tok_dodela_nadleznostiId', $this->searchNadleznost))
            ->orderBy('list_order')
            ->orderBy('id')
            ->paginate(Config::get('global.paginate'));
    }

    public function render(): View
    {
        return view('livewire.tiket-vrste-kvara', [
            'data' => $this->read(),
            'nadleznosti' => TiketOpisKvaraTip::nadleznosti(),
        ]);
    }

    /**
     * Stranicu i akcije vide samo pozicije kojima je dodeljena ruta (Admin i Call centar)
     */
    private function proveriPristup(): void
    {
        abort_unless(PozicijaPrikazStranica::isRoleHasRightToAccess(auth()->user()->pozicija_tipId, self::ROUTE_NAME), 403);
    }

    private function loadModel(int $id): TiketOpisKvaraTip
    {
        $model = TiketOpisKvaraTip::findOrFail($id);

        $this->modelId = $model->id;
        $this->tok_naziv = $model->tok_naziv;
        $this->tok_dodela_nadleznostiId = $model->tok_dodela_nadleznostiId;
        $this->list_order = $model->list_order;

        return $model;
    }

    /**
     * @return array{tok_naziv: string, tok_dodela_nadleznostiId: int|null, list_order: int|null}
     */
    private function modelData(): array
    {
        return [
            'tok_naziv' => $this->tok_naziv,
            'tok_dodela_nadleznostiId' => $this->tok_dodela_nadleznostiId,
            'list_order' => $this->list_order,
        ];
    }

    private function resetInputFields(): void
    {
        $this->modelId = null;
        $this->tok_naziv = '';
        $this->tok_dodela_nadleznostiId = null;
        $this->list_order = null;
        $this->broj_tiketa = 0;
        $this->broj_akcija = 0;
    }
}
