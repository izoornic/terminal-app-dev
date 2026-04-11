<?php

namespace App\Http\Livewire\Bankomati\Komponente;

use Livewire\Component;
use App\Models\BankomatLocijaHirtory;
use App\Models\BankomatTiket;
use App\Models\BankomatTiketKomantar;

class BankomatHistory extends Component
{
    public $bankomat_lokacija_id;
    
    public function mount($bankomat_lokacija_id)
    {
        $this->bankomat_lokacija_id = $bankomat_lokacija_id;
    }

    public function read()
    {
        $items = BankomatLocijaHirtory::select(
                'bankomat_status_tips.status_naziv',
                'bankomat_locija_hirtories.created_at',
                'blokacijas.bl_naziv',
                'blokacijas.bl_mesto',
                'bankomat_regions.r_naziv',
                'bankomat_lokacija_histroy_actions.id as history_action_id',
                'bankomat_lokacija_histroy_actions.akcija',
                'bankomat_lokacija_histroy_actions.vrsta_akcije',
                'bankomat_tikets.id as bankomat_tiket_id',
                'bankomat_tikets.opis',
                'bankomat_tikets.naplata',
                'bankomat_tiket_kvar_tips.btkt_naziv',
                )
            ->leftJoin('bankomat_lokacija_histroy_actions', 'bankomat_locija_hirtories.history_action_id', '=', 'bankomat_lokacija_histroy_actions.id')
            ->leftJoin('bankomat_status_tips', 'bankomat_locija_hirtories.bankomat_status_tip_id', '=', 'bankomat_status_tips.id')
            ->leftJoin('blokacijas', 'bankomat_locija_hirtories.blokacija_id', '=', 'blokacijas.id')
            ->leftJoin('bankomat_regions', 'blokacijas.bankomat_region_id', '=', 'bankomat_regions.id')
            ->leftJoin('bankomat_tikets', 'bankomat_locija_hirtories.bankomat_tiket_id', '=', 'bankomat_tikets.id')
            ->leftJoin('bankomat_tiket_kvar_tips', 'bankomat_tikets.bankomat_tiket_kvar_tip_id', '=', 'bankomat_tiket_kvar_tips.id')
            ->where('bankomat_locija_hirtories.bankomat_lokacija_id', '=', $this->bankomat_lokacija_id)
            ->orderBy('bankomat_locija_hirtories.created_at', 'desc')
            ->get();

        // Učitaj komentare za zatvorene tikete (history_action_id == 9) — jedan upit umesto N+1
        $tiketIds = $items->where('history_action_id', 9)->pluck('bankomat_tiket_id')->filter()->unique();

        if ($tiketIds->isNotEmpty()) {
            $komentari = BankomatTiketKomantar::select('bankomat_tiket_komantars.*', 'users.name')
                ->leftJoin('users', 'bankomat_tiket_komantars.user_id', '=', 'users.id')
                ->whereIn('bankomat_tiket_id', $tiketIds)
                ->get()
                ->groupBy('bankomat_tiket_id');

            $items->each(function ($item) use ($komentari) {
                if ($item->history_action_id == 9) {
                    $item->komentari = $komentari->get($item->bankomat_tiket_id, collect());
                }
            });
        }

        return $items;
    }

    public function render()
    {
        return view('livewire.bankomati.komponente.bankomat-history', 
        [ 'historyData' => $this->read(), ]);
    }
}
