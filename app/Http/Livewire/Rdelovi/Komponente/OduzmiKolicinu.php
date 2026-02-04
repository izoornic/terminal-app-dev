<?php

namespace App\Http\Livewire\Rdelovi\Komponente;

use Livewire\Component;
use App\Models\PartStock;
use App\PartsInventory\Services\PartStockService;

class OduzmiKolicinu extends Component
{
    public $stock_id;
    public $kolicina;
    public $partStock;
    public $partType;
    public $oduzmimoguce = true;

    public function mount($stock_id)
    {
        $this->stock_id = $stock_id;
        $this->partStock = PartStock::find($stock_id);
        $this->partType = $this->partStock->partType;
    }

    public function save()
    {
        $this->validate([
            'kolicina' => 'required|numeric|min:1',
        ]);
        if($this->kolicina > $this->partStock->kolicina_dostupna){
            $this->oduzmimoguce = false;
            return;
        }
        $partStockService = new PartStockService();
        $partStockService->removeStock($this->partStock->part_type_id, $this->partStock->lokacija_id, $this->kolicina, auth()->user()->id);
       
        $this->emit('modalActionSuccess', $this->partType->sifra, $this->partType->naziv);
    }

    public function render()
    {
        return view('livewire.rdelovi.komponente.oduzmi-kolicinu');
    }
}
