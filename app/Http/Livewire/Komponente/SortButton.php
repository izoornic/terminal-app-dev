<?php

namespace App\Http\Livewire\Komponente;

use Livewire\Component;

class SortButton extends Component
{
    public $field;
    public $orderBy;
    public $orderDirection;

    public $btn_text;
    public $active;

    protected $listeners = ['sortChange', 'fieldChange'];
    
    public function mount()
    {
        $this->orderDirection = 'desc';
        $this->active = ($this->field === $this->orderBy) ? true : false;
    }

    public function sortClick()
    {
        $this->emit('sortClick', $this->field);
    }

    public function sortChange($sort)
    {
        $this->orderDirection = $sort;
    }

    public function fieldChange($field)
    {
       $this->active =  ($this->field === $field) ? true : false;
    }

    public function render()
    {
        return view('livewire.komponente.sort-button');
    }
}
