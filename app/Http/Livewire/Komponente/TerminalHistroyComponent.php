<?php

namespace App\Http\Livewire\Komponente;

use Livewire\Component;
use App\Actions\Terminali\TerminalHistory;


class TerminalHistroyComponent extends Component
{
    public $terminal_lokacija_id;
    public $historyData = [];

    public function mount($terminal_lokacija_id)
    {
        $this->terminal_lokacija_id = $terminal_lokacija_id;
        $this->historyData = TerminalHistory::terminalHistoryData($this->terminal_lokacija_id);
    }
    public function render()
    {
        return view('livewire.komponente.terminal-histroy-component');
    }
}
