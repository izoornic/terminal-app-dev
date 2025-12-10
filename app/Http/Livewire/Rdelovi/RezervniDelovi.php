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

    public function read()
    {
        return PartStock::with(['partType.category', 'location'])
            ->when($this->locationId, function ($q) {
                $q->where('lokacija_id', $this->locationId);
            })
            ->when($this->searchTerm, function ($q) {
                $q->whereHas('partType', function ($query) {
                    $query->where('naziv', 'like', '%' . $this->searchTerm . '%')
                        ->orWhere('sifra', 'like', '%' . $this->searchTerm . '%');
                });
            })
            ->when($this->categoryId, function ($q) {
                $q->whereHas('partType', function ($query) {
                    $query->where('category_id', $this->categoryId);
                });
            })
            ->when($this->showLowStockOnly, function ($q) {
                $q->lowStock();
            })
            ->orderBy('kolicina_dostupna')
            ->paginate(Config::get('global.paginate'), ['*'], 'tik');
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
