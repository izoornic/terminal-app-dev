<?php

namespace App\Http\Livewire\Komponente;

use Livewire\Component;

class AddNewItemButton extends Component
{
    public $btn_name;
    public $btn_event;

    public function mount($btn_name, $btn_event)
    {
        $this->btn_name = $btn_name;
        $this->btn_event = $btn_event;
    }

    public function btnClick()
    {
        $this->emit($this->btn_event);
    }

    public function render()
    {
        return view('livewire.komponente.add-new-item-button');
    }
}
