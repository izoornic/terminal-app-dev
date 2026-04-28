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
    public $sub_locations_products;

    public function mount()
    {
        $this->role_region = auth()->user()->userBankmatPositionAndRegion();
        $this->role_region_id = $this->role_region['region'];

        $this->main_locations = Blokacija::where('is_duplicate', '=', 0)->where('blokacija_tip_id', '=', 3)->get();

        $this->main_locations->each(function ($main_location) {
            $children = $main_location->children()->get();

            $this->location_ids_by_main[$main_location->id] = $children->pluck('id')->toArray();

            $this->sum_of_products[$main_location->bl_naziv] = BankomatLokacija::whereIn('blokacija_id', $this->location_ids_by_main[$main_location->id])->count();

            $this->sub_locations_products[$main_location->bl_naziv] = $children->mapWithKeys(function ($child) {
                $label = trim("{$child->bl_naziv} {$child->bl_naziv_sufix}");
                $byTip = BankomatLokacija::where('bankomat_lokacijas.blokacija_id', $child->id)
                    ->join('bankomats', 'bankomat_lokacijas.bankomat_id', '=', 'bankomats.id')
                    ->join('bankomat_tips', 'bankomats.bankomat_tip_id', '=', 'bankomat_tips.id')
                    ->selectRaw('bankomat_tips.model, count(*) as count')
                    ->groupBy('bankomat_tips.model')
                    ->orderBy('bankomat_tips.model')
                    ->pluck('count', 'model')
                    ->toArray();
                return [$label => [
                    'total' => array_sum($byTip),
                    'tips'  => $byTip,
                ]];
            })->toArray();
        });
    }

    public function render()
    {
        return view('livewire.bankomati.dashboard');
    }
}
