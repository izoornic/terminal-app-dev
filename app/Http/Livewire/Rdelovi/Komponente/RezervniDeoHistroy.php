<?php

namespace App\Http\Livewire\Rdelovi\Komponente;

use Livewire\Component;

class RezervniDeoHistroy extends Component
{
    public $part_id;

    public function mount($part_id)
    {
        $this->part_id = $part_id;
    }

    public function render()
    {
        return view('livewire.rdelovi.komponente.rezervni-deo-histroy');
    }
}
