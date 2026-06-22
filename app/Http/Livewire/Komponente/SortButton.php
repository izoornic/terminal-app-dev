<?php

namespace App\Http\Livewire\Komponente;

use Livewire\Component;
use Livewire\Attributes\On;

class SortButton extends Component
{
    public $field;
    public $orderBy;
    public $orderDirection;

    public $btn_text;
    public $active;

    public function mount()
    {
        $this->orderDirection = 'desc';
        $this->active = ($this->field === $this->orderBy) ? true : false;
    }

    public function sortClick()
    {
        $this->dispatch('sortClick', $this->field);
    }

    #[On('sortChange')]
    public function sortChange($sort)
    {
        $this->orderDirection = $sort;
    }

    #[On('fieldChange')]
    public function fieldChange($field)
    {
       $this->active =  ($this->field === $field) ? true : false;
    }

    public function render()
    {
        return view('livewire.komponente.sort-button');
    }
}
