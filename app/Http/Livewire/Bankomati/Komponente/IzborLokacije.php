<?php

namespace App\Http\Livewire\Bankomati\Komponente;

use Livewire\Component;
use App\Models\Blokacija;
use App\Models\BlokacijaTip;

use Illuminate\Support\Facades\Config;
use Livewire\WithPagination;

class IzborLokacije extends Component
{
    use WithPagination;
    public $role_region;
    public $vrsta_lokacije;
    public $comp_index;
    public $trenutna_lokacija;
    public $tipovi_lokacija;
    public $premesti_vlasnik;
    public $premesti_vlasnik_tip;

    public $izbor_glavne_lokacije;

    public $searchPLokacijaNaziv;
    public $searchPlokacijaMesto;
    public $searchPlokacijaRegion;

    public function mount($comp_index = null, $tipovi_lokacija = null, $trenutna_lokacija = null, $izbor_glavne_lokacije = false, $premesti_vlasnik = false)
    {
        $this->role_region =auth()->user()->userBankmatPositionAndRegion();

        $this->searchPLokacijaNaziv = '';
        $this->searchPlokacijaMesto = '';
        $this->searchPlokacijaRegion = ($this->role_region['role'] == 'admin' || $this->role_region['role'] == 'programer') ? 0 : $this->role_region['region'];

        //iskljucije trenutnu lokaciju kada se proizvod premesta u neku drugu
        $this->trenutna_lokacija = $trenutna_lokacija;

        /* if($vrsta_lokacije) {
            $this->vrsta_lokacije = $vrsta_lokacije;
        } */
        //emituje indeh kada su dve komponente na istoj stranici;
        if($comp_index) {
            $this->comp_index = $comp_index;
        }  
        //sluzi kada se dodaje nova podlokacija da filtrira samo glavne lokacije
        if($izbor_glavne_lokacije) {
            $this->izbor_glavne_lokacije = $izbor_glavne_lokacije;
            // $this->vrsta_lokacije = 3;
        }
        //krati broj ponudjenih tipova lokacija ako je setovan
        if($tipovi_lokacija) {
            $this->tipovi_lokacija = $tipovi_lokacija;
        }else {
            $this->tipovi_lokacija = BlokacijaTip::get()->pluck('id')->toArray();
        }

        if($premesti_vlasnik) {
            $premesti_vlasnik_lokacija = Blokacija::where('id', $premesti_vlasnik)->first();
            $this->premesti_vlasnik = $premesti_vlasnik_lokacija->parent_id;
            $this->premesti_vlasnik_tip = $premesti_vlasnik_lokacija->blokacija_tip_id;
        }
    }

    public function vrstaLokacije($vrsta_lokacije)
    {
        //dd($vrsta_lokacije);
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
            ->when($this->izbor_glavne_lokacije, function ($query) {
                return $query->where('blokacijas.is_duplicate', '=', 0);
            })
            ->when($this->premesti_vlasnik, function ($query) use ($tipId) {
                if($tipId == 3 && $this->premesti_vlasnik_tip == 3) {
                    return $query->where('blokacijas.parent_id', '=', $this->premesti_vlasnik);
                }
            })
            ->orderBy('blokacijas.is_duplicate', 'asc')
            ->orderBy('blokacijas.bl_naziv', 'asc')
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
