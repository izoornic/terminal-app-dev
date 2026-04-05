<?php

namespace App\Http\Livewire\Bankomati;

use App\Models\BankomatLokacija;
use App\Models\Blokacija;
use Livewire\Component;

class Dashboard extends Component
{
    public $role_region;
    public $role_region_id;
    public $main_locations;
    public $location_ids_by_main;
    public $sum_of_products;

    public function mount()
    {
        $this->role_region =auth()->user()->userBankmatPositionAndRegion();
        $this->role_region_id = $this->role_region['region'];

        //pick up all main locations
        $this->main_locations = Blokacija::where('is_duplicate', '=', 0)->where('blokacija_tip_id', '=', 3)->get();

         $this->main_locations->each(function ($main_location) {
            /* $this->location_ids_by_main[$main_location->id] = Blokacija::where('parent_id', '=', $main_location->id)->pluck('id')->toArray(); */
            //let's tyu with relations
            $this->location_ids_by_main[$main_location->id] = $main_location->children()->pluck('id')->toArray();
            //let's count number of products for each location
            $this->sum_of_products[$main_location->bl_naziv] = BankomatLokacija::whereIn('blokacija_id', $this->location_ids_by_main[$main_location->id])->count();
         });
        //dd($this->sum_of_products);
    }

    public function render()
    {
        return view('livewire.bankomati.dashboard');
    }
}
