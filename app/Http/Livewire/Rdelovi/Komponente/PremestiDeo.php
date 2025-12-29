<?php

namespace App\Http\Livewire\Rdelovi\Komponente;

use Livewire\Component;
use App\Models\PartStock;
//use App\PartsInventory\Services\PartStockService;
use App\PartsInventory\Services\TransferService;

class PremestiDeo extends Component
{
    public $stock_id;
    public $kolicina;
    public $partStock;
    public $partType;
    public $lokacija;
    public $premestimoguce = true;

    protected $listeners = [
        'novaLokacija',
    ];

    public function novaLokacija($id)
    {
        $this->lokacija = $id;
    }

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
            'lokacija' => 'required|exists:lokacijas,id',
        ]);
        if($this->kolicina > $this->partStock->kolicina_dostupna){
            $this->premestimoguce = false;
            return;
        }
        //$partStockService = new PartStockService();
        $transferService = new TransferService();
        $transferService->transferItem($this->partStock->part_type_id, $this->partStock->lokacija_id, $this->lokacija, $this->kolicina, auth()->user()->id, 'transfer');
        //$partStockService->addStock($this->partStock->part_type_id, $this->partStock->lokacija_id, $this->kolicina, auth()->user()->id);
       
        $this->emit('modalActionSuccess', $this->partType->sifra, $this->partType->naziv);
    }
    
    public function render()
    {
        return view('livewire.rdelovi.komponente.premesti-deo');
    }
}
