<?php

namespace App\Http\Livewire\Komponente;

use Livewire\Component;
use App\Models\TerminalLokacija;
use App\Ivan\SelectedTerminalInfo;

class TerminalInfo extends Component
{
    public $terminal_lokacija_id = 0;

    public $multySelected = false;
    public $multySelectedArray = [];
    public $multiSelectedInfo;

    public function mount($terminal_lokacija_id)
    {
        if($this->multySelected) {
            $this->multiSelectedInfo = $this->multiSelectedTInfo();
        }else{
            $this->terminal_lokacija_id = $terminal_lokacija_id;
            $this->selectedTerminal = SelectedTerminalInfo::selectedTerminalInfoTerminalLokacijaId($this->terminal_lokacija_id);
        }
    }

    private function multiSelectedTInfo()
    {
       return TerminalLokacija::select(
                'terminal_lokacijas.id', 
                'terminals.sn', 
                'lokacijas.l_naziv', 
                'lokacijas.l_naziv_sufix', 
                'terminal_status_tips.ts_naziv',
                'lokacijas.mesto',)
            ->join('terminals', 'terminal_lokacijas.terminalId', '=', 'terminals.id')
            ->leftJoin('lokacijas', 'terminal_lokacijas.lokacijaId', '=', 'lokacijas.id')
            ->leftJoin('terminal_status_tips', 'terminal_lokacijas.terminal_statusId', '=', 'terminal_status_tips.id')
            ->whereIn('terminal_lokacijas.id', $this->multySelectedArray)
            ->get();
    }

    public function render()
    {
        return view('livewire.komponente.terminal-info');
    }
}
