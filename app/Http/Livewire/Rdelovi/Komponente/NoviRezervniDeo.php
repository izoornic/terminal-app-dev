<?php

namespace App\Http\Livewire\Rdelovi\Komponente;

use Livewire\Component;
use App\PartsInventory\Services\PartStockService;
use App\Models\PartType;

class NoviRezervniDeo extends Component
{
    public $sifra;
    public $naziv;
    public $opis;
    public $kategorija;
    public $cena;
    public $jedinica_mere;
    public $min_kolcina;
    public $aktivan;

    public $lokacija;
    public $kolicina;

    public $is_edit = false;
    public $kategorija_naziv;
    public $part_id;
    public $part;

    protected $stockService;
    //private $partType;

    protected $listeners = ['novaLokacija'];
    public function novaLokacija($id)
    {
        $this->lokacija = $id;
        //show moda for adding new part
    }

    public $jedinica_mere_list = ['kom', 'kg', 'l', 'm', 'm2', 'set', 'par'];

    public function mount($is_edit = false, $part_id = null)
    {
        $this->is_edit = $is_edit;
        $this->part_id = $part_id;
        if($is_edit) {
            $this->part = PartType::find($part_id);
            $this->sifra = $this->part->sifra;
            $this->naziv = $this->part->naziv;
            $this->opis = $this->part->opis;
            $this->kategorija_naziv = $this->part->category->model ?? 'Nekategorisan';
            $this->cena = $this->part->cena;
            $this->jedinica_mere = $this->part->jedinica_mere;
            $this->min_kolcina = $this->part->min_kolicina;
            $this->aktivan = $this->part->aktivan;
            $this->kolicina = $this->part->kolicina;
        }else{
            $this->aktivan = true;
            $this->jedinica_mere = 'kom';
        }
    }
    public function rules()
    {
        if($this->is_edit) {
            return [
                'sifra' => 'required|unique:part_types,sifra,'.$this->part_id,
                'naziv' => 'required',
                'opis' => 'nullable|string',
                'cena' => 'required|numeric|min:0',
                'jedinica_mere' => 'required|in:'.implode(',', $this->jedinica_mere_list),
                'min_kolcina' => 'required|numeric|min:0',
                'aktivan' => 'required|boolean'
            ]; 
        }else{
            return [
                'sifra' => 'required|unique:part_types,sifra',
                'naziv' => 'required',
                'opis' => 'nullable|string',
                'kategorija' => 'required|exists:terminal_tips,id',
                'cena' => 'required|numeric|min:0',
                'jedinica_mere' => 'required|in:'.implode(',', $this->jedinica_mere_list),
                'min_kolcina' => 'required|numeric|min:0',
                'aktivan' => 'required|boolean',
                'lokacija' => 'required|exists:lokacijas,id',
                'kolicina' => 'required|numeric|min:0',
            ];
        }
    }

    public function newRezervniDeo()
    {
        //dd($this->jedinica_mere);
        $this->validate();
         if($this->is_edit) {
            $partType = PartType::find($this->part_id);
            $partType->sifra = $this->sifra;
            $partType->naziv = $this->naziv;
            $partType->opis = $this->opis;
            $partType->cena = $this->cena;
            $partType->jedinica_mere = $this->jedinica_mere;
            $partType->min_kolicina = $this->min_kolcina;
            $partType->aktivan = $this->aktivan;
            $partType->save();
         }else{
            $partType = new PartType();
            $partType->sifra = $this->sifra;
            $partType->naziv = $this->naziv;
            $partType->category_id = $this->kategorija;
            $partType->cena = $this->cena;
            $partType->opis = $this->opis;
            $partType->jedinica_mere = $this->jedinica_mere;
            $partType->min_kolicina = $this->min_kolcina;
            $partType->aktivan = $this->aktivan;
            $partType->save();

            $stockService = new PartStockService();
            $stockService->addStock($partType->id, $this->lokacija, $this->kolicina, auth()->user()->id);
         }
       
        $this->emit('modalActionSuccess', $this->sifra, $this->naziv);
    }
    
    public function render()
    {
        return view('livewire.rdelovi.komponente.novi-rezervni-deo');
    }
}
