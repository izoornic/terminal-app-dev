<?php

namespace App\Http\Livewire\Managment;

use Livewire\Component;
use App\Models\LicencaDistributerTip;

class DistributerLicencePregled extends Component
{
    //set on MOUNT
    public $distId;
    public $dist_name;

    public $dataClicked;
    public $wkey = 1;

    protected $listeners = ['chartClicked' => 'getClickedData'];

    public function getClickedData($data)
    {
        $this->wkey ++;
        $this->dataClicked = $data;
        $this->render();
        //dd($data);
        //$this->dispatchBrowserEvent('chartClicked', ['data' => $data]);
    }

    /**
     * mount
     *
     * @return void
     */
    public function mount()
    {
        $this->distId = request()->query('id') ?? 0;

        $this->dist_name = LicencaDistributerTip::where('id', '=', $this->distId)->first()->distributer_naziv ?? 'Sve licence';
        $this->dataClicked = '';
    }

    public function render()
    {
        return view('livewire.managment.distributer-licence-pregled', [
            'dataDisp' => $this->dataClicked,
        ]);
    }
}
