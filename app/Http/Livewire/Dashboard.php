<?php

namespace App\Http\Livewire;

use Livewire\Component;

class Dashboard extends Component
{
    public $too = 50;
    public $is_admin = false;

    public function mount()
    {
        $this->is_admin = auth()->user()->pozicija_tipId == 1;
    }

    public function render()
    {
        //dd($userRole = auth()->user()->pozicija_tipId);
        return view('livewire.dashboard');
    }

    
}
