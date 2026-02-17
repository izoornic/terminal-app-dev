<?php

namespace App\Http\Livewire\Bankomati;

use App\Models\Blokacija;
use Livewire\Component;

class Dashboard extends Component
{
    public $role_region;
    public $role_region_id;
    public $main_locations;

    public function mount()
    {
        //pick up all main locations
        $this->main_locations = Blokacija::where('is_duplicate', '=', 0)->where('blokacija_tip_id', '=', 3)->get();

        $this->role_region =auth()->user()->userBankmatPositionAndRegion();
        $this->role_region_id = $this->role_region['region'];

        //dd($this->main_locations);
    }

    public function render()
    {
        return view('livewire.bankomati.dashboard');
    }
}
