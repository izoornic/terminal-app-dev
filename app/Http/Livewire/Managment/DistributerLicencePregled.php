<?php

namespace App\Http\Livewire\Managment;

use Livewire\Component;
use App\Models\LicencaDistributerTip;

class DistributerLicencePregled extends Component
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
        return view('livewire.managment.distributer-licence-pregled');
    }
}
