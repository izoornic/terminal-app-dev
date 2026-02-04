<?php

namespace App\Http\Livewire\Rdelovi\Komponente;

use Livewire\Component;
use App\Models\PartType;

class RzervniDeoInfo extends Component
{
    public $partType_id;
    public $rzervniDeo;

    public function mount($partType_id)
    {
        $this->partType_id = $partType_id;
        $this->rzervniDeo = PartType::find($partType_id);
    }

    public function render()
    {
        return view('livewire.rdelovi.komponente.rzervni-deo-info');
    }
}
