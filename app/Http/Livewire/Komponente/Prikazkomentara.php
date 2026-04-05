<?php

namespace App\Http\Livewire\Komponente;

use Livewire\Component;
use App\Actions\Terminali\TerminalCommentActions;

class Prikazkomentara extends Component
{
    public $terminalComments;
    public $terminalLokacijaId;
    public $canEdit = false;
    public $newKoment = '';

    
    public function mount($canEdit = false)
    {
        $this->terminalComments = TerminalCommentActions::Comments($this->terminalLokacijaId);

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
        TerminalCommentActions::Delete($id);
        $this->terminalComments = TerminalCommentActions::Comments($this->terminalLokacijaId);
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

        TerminalCommentActions::Create($this->terminalLokacijaId, $this->newKoment);
        
        $this->terminalComments = TerminalCommentActions::Comments($this->terminalLokacijaId);
        
        $this->newKoment = '';
    } 

    public function render()
    {
        $this->terminalComments = TerminalCommentActions::Comments($this->terminalLokacijaId);
        return view('livewire.komponente.prikazkomentara');
    }
}
