<?php

namespace App\Http\Livewire\Rdelovi;

use Livewire\Component;

use App\Models\PartStock;
use App\Models\PartType;
use App\Models\Lokacija;
use App\Models\TerminalTip;
use Livewire\WithPagination;

use Illuminate\Support\Facades\Config;
class RezervniDelovi extends Component
{
    use WithPagination;

    public $locationId = '';
    public $categoryId = '';
    public $searchTerm = '';
    public $showLowStockOnly = false;

    //new part
    public $modalVisible = false;
    //edit part
    public $part_id = '';

    public $modal_title;
    public $modal_type;

    public $stock_id;

    /**
     * Listeners for Livewire events
     *
     * @var array
     */
    protected $listeners = ['newPart', 'modalActionSuccess'];

    public function newPart()
    {
        $this->part_id = '';
        if(auth()->user()->can('parts.types.manage')) {
            //show moda for adding new part
            $this->modal_title = 'Novi rezervni deo';
            $this->modal_type = 'dodaj_novi_deo';
            $this->modalVisible = true;
        }
    }

    public function modalActionSuccess($sifra='', $naziv='')
    {
        switch ($this->modal_type) {
            case 'dodaj_novi_deo':
                 $this->emit('flashMessage', 'Novi rezervni deo - '.$naziv.' - '.$sifra.' je uspešno dodat.');
                break;
            case 'dodaj_kolicinu':
                $this->emit('flashMessage', 'Stanje za rezervni deo  - '.$naziv.' - '.$sifra.' je uspešno azurirano.');
                break;
            case 'oduzmi_kolicinu':
                $this->emit('flashMessage', 'Stanje za rezervni deo  - '.$naziv.' - '.$sifra.' je uspešno azurirano.');
                break;
            case 'premesti_kolicinu':
                $this->emit('flashMessage', 'Količina za rezervni deo  - '.$naziv.' - '.$sifra.' je uspešno premeštena.');
                break;
            case 'azuriraj_rezervni_deo':
                $this->emit('flashMessage', 'Rezervni deo  - '.$naziv.' - '.$sifra.' je uspešno azurirano.');
                break;
        }
        $this->modalVisible = false;
    }

    public function updateShowModal($id)
    {
         $this->part_id = $id;
         if(auth()->user()->can('parts.types.manage')) {
            $this->modal_title = 'Azuriraj rezervni deo';
            $this->modal_type = 'azuriraj_rezervni_deo';
            $this->modalVisible = true;
         }
    }

    public function addStockShowModal($id)
    {
        $this->modal_title = 'Dodaj količinu';
        $this->modal_type = 'dodaj_kolicinu';
        $this->stock_id = $id;
        $this->modalVisible = true;
    }

    public function removeStockShowModal($id)
    {
        $this->modal_title = 'Oduzmi količinu';
        $this->modal_type = 'oduzmi_kolicinu';
        $this->stock_id = $id;
        $this->modalVisible = true;
    }

    public function moveStockShowModal($id)
    {
        $this->modal_title = 'Premesti količinu';
        $this->modal_type = 'premesti_kolicinu';
        $this->stock_id = $id;
        $this->modalVisible = true;
    }

    public function stocklHistoryShowModal($id)
    {
        $this->modal_title = 'Istorija dela';
        $this->modal_type = 'prikazi_istoriju';
        $this->part_id = $id;
        $this->modalVisible = true;
    }

    public function mount()
    {
        $this->locationId = request()->get('locationId');
        $this->categoryId = request()->get('categoryId');
        $this->searchTerm = request()->get('searchTerm');
        $this->showLowStockOnly = request()->get('showLowStockOnly');

        //dd(auth()->user()->can('parts.types.manage'));
        //dd(auth()->user()->hasRole('Admin'));
        //PartType::factory()->count(20)->create();
        //PartStock::factory()->count(20)->create();
        //PartStock::factory()->count(5)->outOfStock()->create();
    }

    public function read()
    {
        return PartStock::query()
            ->select(
                'part_stocks.*', 
                'part_types.id as tipid',
                'part_types.sifra',
                'part_types.naziv',
                'part_types.category_id', 
                'lokacijas.l_naziv',
                'regions.r_naziv',
                'terminal_tips.model as kategorija',
                'terminal_tips.proizvodjac'
                )
            ->leftJoin('part_types', 'part_types.id', '=', 'part_stocks.part_type_id')
            ->leftJoin('lokacijas', 'lokacijas.id', '=', 'part_stocks.lokacija_id')
            ->leftJoin('regions', 'regions.id', '=', 'lokacijas.regionId')
            ->leftJoin('terminal_tips', 'terminal_tips.id', '=', 'part_types.category_id')
            ->when($this->searchTerm, function ($q) {
                    $q->where('naziv', 'like', '%' . $this->searchTerm . '%')
                        ->orWhere('sifra', 'like', '%' . $this->searchTerm . '%');
            })
            ->when($this->locationId, function ($q) {
                    $q->where('lokacija_id', $this->locationId);
            })
            ->when($this->categoryId, function ($q) {
                    $q->where('category_id', $this->categoryId);
            })
            ->when($this->showLowStockOnly, function ($q) {
                $q->whereRaw('part_stocks.kolicina_dostupna <= part_types.min_kolicina');
            })
            ->orderBy('kolicina_dostupna')
            ->paginate(Config::get('global.terminal_paginate'), ['*'], 'tik');
    }

    public function locations()
    {
        return Lokacija::whereIn('lokacija_tipId', [1, 2])->get();;
    }

    public function categories()
    {
        return TerminalTip::all();
    }

    public function render()
    {
        return view('livewire.rdelovi.rezervni-delovi',
     [
            'stocks' => $this->read(),
            'locations' => $this->locations(),
            'categories' => $this->categories(),
        ]);
    }
}
