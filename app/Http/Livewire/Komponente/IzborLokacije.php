<?php

namespace App\Http\Livewire\Komponente;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\Lokacija;

use Illuminate\Support\Facades\Config;

class IzborLokacije extends Component
{
    use WithPagination;

    public $tipovi_lokacija;
    public $vrsta_lokacije;
    public $lokacija_tipId;
     public $searchPLokacijaNaziv;
    public $searchPlokacijaMesto;
    public $searchPlokacijaRegion;

    public function mount($tipovi_lokacija = [1, 2, 3])
    {
        $this->tipovi_lokacija = $tipovi_lokacija;
        //dd($this->tipovi_lokacija);
    }
/* 
    public function setVrstaLokacije($id)
    {
        $this->vrsta_lokacije = $id;
    } */

    public function lokacijeTipa($tipId)
    {
        return Lokacija::select('lokacijas.*', 'regions.r_naziv')
        ->leftJoin('regions', 'lokacijas.regionId', '=', 'regions.id')
        ->where('lokacijas.lokacija_tipId', '=', $tipId)
        ->where('lokacijas.l_naziv', 'like', '%'.$this->searchPLokacijaNaziv.'%')
        ->where('lokacijas.mesto', 'like', '%'.$this->searchPlokacijaMesto.'%')
        ->when($this->searchPlokacijaRegion, function ($query) {
            return $query->where('lokacijas.regionId', $this->searchPlokacijaRegion);
        })
        ->paginate(Config::get('global.modal_search'), ['*'], 'loc');
    }

    public function novaLokacija($id)
    {
        $this->emit('novaLokacija', $id);
    }
   
    public function render()
    {
        return view('livewire.komponente.izbor-lokacije');
    }
}
