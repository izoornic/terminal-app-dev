<?php

namespace App\Http\Livewire\Komponente;

use Livewire\Component;
use App\Actions\Lokacije\LokacijaHistory;

class LokacijaHistoryComponent extends Component
{
    public $historyData;
    public $lokacija_id;

    public function mount($lokacija_id)
    {
        $this->lokacija_id = $lokacija_id;
        $this->historyData = LokacijaHistory::lokacijaHistoryData($this->lokacija_id);
        //dd($this->historyData);
    }
    public function render()
    {
        return view('livewire.komponente.lokacija-history-component');
    }
}
