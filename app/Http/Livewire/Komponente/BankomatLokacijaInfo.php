<?php

namespace App\Http\Livewire\Komponente;

use Livewire\Component;
use App\Models\Blokacija;

class BankomatLokacijaInfo extends Component
{
    public $b_lokacija_id;

    public function mount($b_lokacija_id)
    {
        $this->b_lokacija_id = $b_lokacija_id;
    }

    public function read()
    {
        return Blokacija::select(
            'blokacijas.*', 
            'blokacija_tips.id as tipid',
            'blokacija_tips.bl_tip_naziv as tip', 
            'bankomat_regions.r_naziv'
            )
                ->leftJoin('blokacija_tips', 'blokacijas.blokacija_tip_id', '=', 'blokacija_tips.id')
                ->leftJoin('bankomat_regions', 'blokacijas.bankomat_region_id', '=', 'bankomat_regions.id')
                ->where('blokacijas.id', '=', $this->b_lokacija_id)
                ->first();
    }
    public function render()
    {
        return view('livewire.komponente.bankomat-lokacija-info', [
            'data' => $this->read(),
        ]);
    }
}
