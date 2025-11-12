<?php

namespace App\Http\Livewire\Bankomati\Komponente;

use Livewire\Component;
use App\Actions\Bankomati\BankomatInformation;
use App\Models\Blokacija;

class BankomatInfo extends Component
{
     public $bankomat_lokacija_id = 0;
    public $selectedBankomat;
    public $kontaktOsobe;
    public $kontaktOsobeVisible;

    public $multySelected = false;
    public $multySelectedArray = [];
    public $multiSelectedInfo;

    public function mount($bankomat_lokacija_id)
    {
        $this->kontaktOsobeVisible = false;
        if($this->multySelected) {
            $this->multiSelectedInfo = BankomatInformation::multiSelectedTInfo($this->multySelectedArray);
        }else{
            $this->bankomat_lokacija_id = $bankomat_lokacija_id ?? 0;
            $this->selectedBankomat = BankomatInformation::bankomatInfo($this->bankomat_lokacija_id);
            $blokacija = Blokacija::where('id', $this->selectedBankomat->blokacijaid)->first();
            $this->kontaktOsobe = $blokacija->kontaktOsobe()->get();
        }
    }
    public function render()
    {
        return view('livewire.bankomati.komponente.bankomat-info');
    }
}
