<?php

namespace App\Http\Livewire\Komponente;

use Livewire\Component;

use App\Models\Lokacija;

class LokacijaInfo extends Component
{

     public $lokacija_id;

    public function mount($lokacija_id)
    {
        $this->lokacija_id = $lokacija_id;
    }

    public function read()
    {
        return Lokacija::select(
            'lokacijas.*', 
            'lokacija_tips.id as tipid',
            'lokacija_tips.lt_naziv as tip', 
            'regions.r_naziv'
            )
                ->leftJoin('lokacija_tips', 'lokacijas.lokacija_tipId', '=', 'lokacija_tips.id')
                ->leftJoin('regions', 'lokacijas.regionId', '=', 'regions.id')
                ->where('lokacijas.id', '=', $this->lokacija_id)
                ->first();
    }

    public function render()
    {
        return view('livewire.komponente.lokacija-info', [
            'data' => $this->read(),
        ]);
    }
}
