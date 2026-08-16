<?php

namespace App\Http\Livewire;

use App\Models\Lokacija;
use App\Models\LicencaDistributerTip;
use App\Models\DistributerLokacijaIndex;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

use Livewire\Component;
use Livewire\WithPagination;

class DistributerLokacija extends Component
{
    use WithPagination;

    public $distId;
    public $dist_name;

    //Dodaj lokaciju modal
    public $modalAddLocVisible;
    public $plokacija;
    public $searchPLokacijaNaziv;
    public $searchPlokacijaMesto;
    public $searchPlokacijaRegion;

    /**
     * mount
     *
     * @return void
     */
    public function mount()
    {
        $this->distId = request()->query('id');
        $this->dist_name = LicencaDistributerTip::DistributerName($this->distId);
    }

    /**
     * [Description for createShowModal]
     *
     * @return void
     * 
     */
    public function createShowModal()
    {
        $this->plokacija = null;
       $this->modalAddLocVisible = true;
    }

    /**
     * Puni tabelu u modalu iz koje se bira lokacija
     *
     * @param mixed $tipId
     * 
     * @return [type]
     * 
     */
    public function lokacijeTipa($tipId=4)
    {
        return Lokacija::select('lokacijas.*', 'regions.r_naziv')
            ->leftJoin('regions', 'lokacijas.regionId', '=', 'regions.id')
            ->whereNotIn('lokacijas.id', function ($q) {
                return $q->select('lokacijaId')->from('distributer_lokacija_indices');
                //->where('licenca_distributer_tipsId', '=', $this->distId );
            })
            ->where('lokacija_tipId', '=', $tipId)
            ->where('l_naziv', 'like', '%'.$this->searchPLokacijaNaziv.'%')
            ->where('mesto', 'like', '%'.$this->searchPlokacijaMesto.'%')
            ->where('lokacijas.regionId', ($this->searchPlokacijaRegion > 0) ? '=' : '<>', $this->searchPlokacijaRegion)
            ->paginate(Config::get('global.modal_search'), ['*'], 'loc');
    }

     /**
     * Prikazuje naziv lokacije na koju se premesta terminal
     *
     * @return void
     */
    public function novaLokacija()
    {
        return Lokacija::select('lokacijas.*', 'regions.r_naziv')
            ->leftJoin('lokacija_tips', 'lokacijas.lokacija_tipId', '=', 'lokacija_tips.id')
            ->leftJoin('regions', 'lokacijas.regionId', '=', 'regions.id')
            ->where('lokacijas.id', '=', $this->plokacija)
            ->first();
    }

    public function create()
    {
        DB::transaction(function(){

            DistributerLokacijaIndex::create([
                'lokacijaId' => $this->plokacija,
                'licenca_distributer_tipsId' => $this->distId
            ]);

            LicencaDistributerTip::find($this->distId)->increment('broj_lokacija');
        });
        $this->modalAddLocVisible = false;
    }
    // Customer::find($customer_id)->decrement('loyalty_points', 50);

    /**
     * The read function.
     *
     * @return void
     */
    public function read()
    {
        return DistributerLokacijaIndex::select('lokacijas.*', 'regions.r_naziv')
            ->leftJoin('lokacijas', 'lokacijas.id', '=', 'distributer_lokacija_indices.lokacijaId')
            ->leftJoin('regions', 'regions.id', '=', 'lokacijas.regionId')
            ->where('distributer_lokacija_indices.licenca_distributer_tipsId', '=', $this->distId)
            ->paginate(Config::get('global.paginate'), ['*'], 'lokacije'); 
    }

    public function render()
    {
        return view('livewire.distributer-lokacija', [
            'data' => $this->read(),
        ]);
    }
}
