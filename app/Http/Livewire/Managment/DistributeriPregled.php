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
    public $orderBy = 'broj_licenci';
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
            $this->orderDirection = 'asc';
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
       return LicencaDistributerTip::select('licenca_distributer_tips.*')
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
