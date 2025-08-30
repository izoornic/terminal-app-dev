<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\TerminalLokacija;
use Illuminate\Support\Facades\DB;

class Dashboard extends Component
{
    public $too = 50;
    public $is_admin = false;

    public function mount()
    {
        $this->is_admin = auth()->user()->pozicija_tipId == 1;
    }

    public function read()
    {
        return TerminalLokacija::select('lokacija_tips.id as lokacija_naziv', 'lokacija_tips.lt_naziv as mesto','lokacija_tips.id as pid', DB::raw('count(*) as total'))
            ->leftJoin('lokacijas', 'terminal_lokacijas.lokacijaId', '=', 'lokacijas.id')
            ->leftJoin('lokacija_tips', 'lokacijas.lokacija_tipId', '=', 'lokacija_tips.id')
            /* ->when($this->searchTerminalTip != '', function ($rtval){
                return $rtval->leftJoin('terminals', 'terminal_lokacijas.terminalId', '=', 'terminals.id')
                ->where('terminals.terminal_tipId', $this->searchTerminalTip);
            }) */
            ->groupBy('lokacijas.lokacija_tipId')
            ->orderBy('lokacija_tips.id')
            ->get();
    }

    public function render()
    {
        //dd($userRole = auth()->user()->pozicija_tipId);
        return view('livewire.dashboard', [
            'data' => $this->read()
        ]);
    }

    
}
