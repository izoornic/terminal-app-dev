<?php

namespace App\Http\Livewire\Komponente;

use Livewire\Component;
use App\Actions\Bankomati\BankomatInformation;

class BankomatInfo extends Component
{
    public $bankomat_lokacija_id = 0;
    public $selectedBankomat;

    public $multySelected = false;
    public $multySelectedArray = [];
    public $multiSelectedInfo;

    public function mount($bankomat_lokacija_id)
    {
        if($this->multySelected) {
            $this->multiSelectedInfo = BankomatInformation::multiSelectedTInfo($this->multySelectedArray);
        }else{
            $this->bankomat_lokacija_id = $bankomat_lokacija_id;
            $this->selectedBankomat = BankomatInformation::bankomatInfo($this->bankomat_lokacija_id);
        }
    }

    public function render()
    {
        return view('livewire.komponente.bankomat-info');
    }
}
