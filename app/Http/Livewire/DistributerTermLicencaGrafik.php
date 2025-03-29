<?php

namespace App\Http\Livewire;

use Livewire\Component;

use App\Models\LicencaDistributerTip;

use App\Http\Helpers;

class DistributerTermLicencaGrafik extends Component
{
    //set on MOUNT
    public $distId;
    public $dist_name;
    
    /**
     * mount
     *
     * @return void
     */
    public function mount()
    {
        $this->distId = request()->query('id') ?? 0;

        $this->dist_name = LicencaDistributerTip::where('id', '=', $this->distId)->first()->distributer_naziv ?? 'Sve licence';
    }

    public function render()
    {
        //dd($this->read2());
        return view('livewire.distributer-term-licenca-grafik');
    }
}
