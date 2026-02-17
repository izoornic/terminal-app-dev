<?php

namespace App\Http\Livewire\Bankomati\Komponente;

use Livewire\Component;
use App\Models\Blokacija;

use Illuminate\Support\Facades\Config;
use Livewire\WithPagination;

class IzborLokacije extends Component
{
    use WithPagination;
    public $role_region;
    public $vrsta_lokacije;
    public $comp_index;
    public $trenutna_lokacija;

    public $searchPLokacijaNaziv;
    public $searchPlokacijaMesto;
    public $searchPlokacijaRegion;

    public function mount($comp_index = null, $vrsta_lokacije=null, $trenutna_lokacija = null)
    {
        $this->role_region =auth()->user()->userBankmatPositionAndRegion();

        $this->searchPLokacijaNaziv = '';
        $this->searchPlokacijaMesto = '';
        $this->searchPlokacijaRegion = ($this->role_region['role'] == 'admin') ? 0 : $this->role_region['region'];

        $this->trenutna_lokacija = $trenutna_lokacija;

        if($vrsta_lokacije) {
            $this->vrsta_lokacije = $vrsta_lokacije;
        }
        //$this->vrsta_lokacije = $vrsta_lokacije;
        if($comp_index) {
            $this->comp_index = $comp_index;
        }   
    }

    public function vrstaLokacije($vrsta_lokacije)
    {
        $this->vrsta_lokacije = $vrsta_lokacije;
    }

    /**
     * Puni tabelu u modalu iz koje se bira lokacija
     *
     * @param mixed $tipId
     * 
     * @return [type]
     * 
     */
    public function lokacijeTipa($tipId)
    {
        return Blokacija::select('blokacijas.*', 'bankomat_regions.r_naziv')
            ->leftJoin('bankomat_regions', 'blokacijas.bankomat_region_id', '=', 'bankomat_regions.id')
            ->where('blokacijas.blokacija_tip_id', '=', $tipId)
            ->where('bl_naziv', 'like', '%'.$this->searchPLokacijaNaziv.'%')
            ->where('bl_mesto', 'like', '%'.$this->searchPlokacijaMesto.'%')
            ->when($this->searchPlokacijaRegion, function ($query) {
                return $query->where('blokacijas.bankomat_region_id', $this->searchPlokacijaRegion);
            })
            ->when($this->trenutna_lokacija, function ($query) {
                return $query->where('blokacijas.id', '!=', $this->trenutna_lokacija);
            })
            ->paginate(Config::get('global.modal_search'), ['*'], 'loc');
    }

    public function novaLokacija($id)
    {
        $this->emit('novaLokacija', $id, $this->comp_index);
    }
    public function render()
    {
        return view('livewire.bankomati.komponente.izbor-lokacije');
    }
}
