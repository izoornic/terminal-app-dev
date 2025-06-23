<?php

namespace App\Http\Livewire\Managment;

use Livewire\Component;
use App\Models\LicencaDistributerTip;
use Illuminate\Support\Facades\Config;

class DistributeriPregled extends Component
{
     use \Livewire\WithPagination;

    //SEARCH
    public $searchName;
    public $searchMesto;
    public $searchPib;

    //OREDER BY
    public $orderBy = 'broj_licenci_count';
    public $orderDirection = 'desc';

    //protected $listeners = ['sortClick'];

    protected function getListeners() { 
        
        return ['sortClick' => 'sortClick']; 
    }

    public function sortClick($field)
    {
        if ($this->orderBy === $field) {
            $this->orderDirection = $this->orderDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->orderBy = $field;
            $this->orderDirection = 'desc';
            $this->emit('fieldChange', $field);
        }
        $this->emit('sortChange', $this->orderDirection);
    }
    /**
     * The read function.
     *
     * @return void
     */
    public function read() 
    {
        //Ovde može da se doda filter koji 'e da izuzme istekle licence u selectRow WHERE - "AND datum_kraj > now()"
       return LicencaDistributerTip::select('licenca_distributer_tips.*')
            ->leftJoin('licence_za_terminals', 'licenca_distributer_tips.id', '=', 'licence_za_terminals.distributerId')
            ->selectRaw('(  SELECT COUNT(*) 
                            FROM licence_za_terminals 
                            WHERE licenca_distributer_tips.id = licence_za_terminals.distributerId 
                            AND licence_za_terminals.licenca_poreklo <> 2 
                            AND naziv_licence = "esir") 
                            AS broj_licenci_count')
            ->groupBy('licenca_distributer_tips.id')
            ->where('distributer_naziv', 'like', '%'.$this->searchName.'%')
            ->where('distributer_mesto', 'like', '%'.$this->searchMesto.'%')
            ->where('distributer_pib', 'like', '%'.$this->searchPib.'%')
            ->orderBy($this->orderBy, $this->orderDirection)
            ->paginate(Config::get('global.paginate'), ['*'], 'lokacije'); 
    }

    public function render()
    {
        return view('livewire.managment.distributeri-pregled', [
            'data' => $this->read(),
        ]);
    }
}
