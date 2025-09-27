<?php

namespace App\Http\Livewire\Komponente;

use Livewire\Component;
use App\Models\BankomatLocijaHirtory;

class BankomatHistory extends Component
{
    public $bankomat_lokacija_id;
    
    public function mount($bankomat_lokacija_id)
    {
        $this->bankomat_lokacija_id = $bankomat_lokacija_id;
    }

    public function read()
    {
        return BankomatLocijaHirtory::select(
                'bankomat_status_tips.status_naziv', 
                'bankomat_locija_hirtories.updated_at', 
                'users.name as user_name',
                'blokacijas.bl_naziv',
                'blokacijas.bl_mesto',
                'bankomat_regions.r_naziv',
                'bankomat_lokacija_histroy_actions.akcija',
                'bankomat_lokacija_histroy_actions.vrsta_akcije'
                )
            ->leftJoin('bankomat_lokacija_histroy_actions', 'bankomat_locija_hirtories.history_action_id', '=', 'bankomat_lokacija_histroy_actions.id')
            ->leftJoin('bankomat_status_tips', 'bankomat_locija_hirtories.bankomat_status_tip_id', '=', 'bankomat_status_tips.id')
            ->leftJoin('blokacijas', 'bankomat_locija_hirtories.blokacija_id', '=', 'blokacijas.id')
            ->leftJoin('users', 'bankomat_locija_hirtories.user_id', '=', 'users.id')
            ->leftJoin('bankomat_regions', 'blokacijas.bankomat_region_id', '=', 'bankomat_regions.id')
            ->where('bankomat_locija_hirtories.bankomat_lokacija_id', '=', $this->bankomat_lokacija_id)
            ->orderBy('bankomat_locija_hirtories.updated_at', 'desc')
            ->get();
    }
    public function render()
    {
        //dd($this->read());
        return view('livewire.komponente.bankomat-history', [
            'historyData' => $this->read(),
        ]);
    }
}
