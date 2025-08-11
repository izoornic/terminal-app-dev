<?php

namespace App\Http\Livewire\Komponente;

use Livewire\Component;
use App\Models\TerminalLokacija;
use App\Ivan\SelectedTerminalInfo;

class Prikazkomentara extends Component
{
    public $selectedTerminalComments;
    public $terminalLokacijaId;
    public $canEdit = false;
    public $newKoment = '';

    
    public function mount($canEdit = false)
    {
        $this->selectedTerminalComments = TerminalLokacija::find($this->terminalLokacijaId)->comments()->where('is_active', true)->get();
        //$selectedTerminalComments = $selectedTerminalComments;
        $this->canEdit = $canEdit;
    }

    /**
     * Obrisi komentar
     *
     * @param mixed $id
     * 
     * @return [type]
     * 
     */
    public function obrisiKomentar($id)
    {
        $komentar = TerminalLokacija::find($this->terminalLokacijaId)->comments()->find($id);
        if($komentar){
            $komentar->update(['is_active' => false, 'deleted_at' => now()]);
            $this->selectedTerminalComments = TerminalLokacija::find($this->terminalLokacijaId)->comments()->where('is_active', true)->get();
            TerminalLokacija::where('id', $this->terminalLokacijaId)->update(['br_komentara' => $this->selectedTerminalComments->count()]);  
        }
    }

    /**
     * Posalji komentar
     *
     * @return void
     */
    public function posaljiKomentar()
    {
        $this->validate([
            'newKoment' => 'required|min:3|max:1000',
        ]);

        TerminalLokacija::find($this->terminalLokacijaId)->comments()
            ->create([
                'comment' => $this->newKoment,
                'userId' => auth()->user()->id,
            ]);

        $this->selectedTerminalComments = TerminalLokacija::find($this->terminalLokacijaId)->comments()->where('is_active', true)->get();
        TerminalLokacija::where('id', $this->terminalLokacijaId)
            ->update([
                'br_komentara'          => $this->selectedTerminalComments->count(), 
                'last_comment_userId'   => auth()->user()->id, 
                'last_comment_at'       => now()
            ]);
        
        $this->newKoment = '';
    } 

    public function render()
    {
        $this->selectedTerminalComments = TerminalLokacija::find($this->terminalLokacijaId)->comments()->where('is_active', true)->get();
        return view('livewire.komponente.prikazkomentara');
    }
}
