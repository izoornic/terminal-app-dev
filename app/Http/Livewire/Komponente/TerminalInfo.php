<?php

namespace App\Http\Livewire\Komponente;

use Livewire\Component;
use App\Models\TerminalLokacija;
use App\Actions\Terminali\SelectedTerminalInfo;
use App\Actions\TerminalAcq\TidInfoUredjaja;

class TerminalInfo extends Component
{
    public $terminal_lokacija_id = 0;

    public $multySelected = false;
    public $multySelectedArray = [];
    public $multiSelectedInfo;
    public $selectedTerminal;

    /**
     * ACQ TID pod kojim je uređaj (ako je pod aktivnim TID-om), za prikaz.
     *
     * @var array{acq_tid: string, naziv_na_slipu: ?string, komunikacija: ?string, testni: bool, datum_instalacije: ?string, acq_mid: ?string, mcc: ?string, naslednik: ?string}|null
     */
    public ?array $acq = null;

    public function mount($terminal_lokacija_id)
    {
        if($this->multySelected) {
            $this->multiSelectedInfo = $this->multiSelectedTInfo();
        }else{
            $this->terminal_lokacija_id = $terminal_lokacija_id;
            $this->selectedTerminal = SelectedTerminalInfo::selectedTerminalInfoTerminalLokacijaId($this->terminal_lokacija_id);
            $this->acq = TidInfoUredjaja::zaTerminalLokaciju($this->terminal_lokacija_id);
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
                'lokacijas.mesto',
                'terminal_acq.acq_tid')
            ->join('terminals', 'terminal_lokacijas.terminalId', '=', 'terminals.id')
            ->leftJoin('terminal_acq', 'terminal_acq.terminalId', '=', 'terminal_lokacijas.terminalId')
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
