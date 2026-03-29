<?php

namespace App\Http\Livewire\Komponente;

use Livewire\Component;
use App\Actions\Terminali\SelectedTerminalInfo;
use App\Actions\Terminali\TerminalBacklist;
use App\Actions\Terminali\TerminalCommentActions;

class BlacklistAddRemove extends Component
{
    public $terminal_lokacija_id;
    public $canBlacklistErorr;
    public $btn_text;
    public $canBlacklist = true;
    public $selectedTerminal;
    public $newKoment;
    public $newkomentPrefix;

    public function mount($terminal_lokacija_id)
    {
        $this->terminal_lokacija_id = $terminal_lokacija_id;
        $this->newKoment = '';

        $this->selectedTerminal = SelectedTerminalInfo::selectedTerminalInfoTerminalLokacijaId($this->terminal_lokacija_id);
        if($this->selectedTerminal->blacklist == 1){
            $this->canBlacklistErorr = 'Da li ste sigurni da želite da uklonite terminal sa Blackliste?';
            $this->btn_text = 'Ukloni sa blackliste';
            $this->newkomentPrefix = ":: Blacklist ukljonjen ::\n";
        }else{
            $this->canBlacklistErorr = 'Da li ste sigurni da želite da dodate terminal na Blacklistu?';
            $this->btn_text = 'Dodaj na blacklist';
            $this->newkomentPrefix = ":: Blacklist dodat ::\n";
        }
        if($this->selectedTerminal->lokacija_tipId != 3){
            $this->canBlacklist = false;
            $this->canBlacklistErorr = 'Samo terminali koji su instalirani korisnicima mogu se dodavti na Blacklistu!';
        }
        if($this->selectedTerminal->ts_naziv != 'Instaliran'){
            $this->canBlacklist = false;
            $this->canBlacklistErorr = 'Samo terminali sa statsom "Instaliran" se mogu dodavti na Blacklistu!';
        }
    }

     /**
     * The update function
     *
     * @return void
     */
    public function blacklistUpdate()
    {
        $this->validate(
            [
                'newKoment' => 'required|string|min:6|max:255',
            ]
        );
        if(TerminalBacklist::AddRemoveBlacklist($this->terminal_lokacija_id)){
            TerminalBacklist::CreateBlacklistFile();
            $this->newKoment = $this->newkomentPrefix . $this->newKoment;
            TerminalCommentActions::Create($this->terminal_lokacija_id, $this->newKoment);
            $this->newKoment = '';
        }
        $this->canBlacklistErorr = '';
        $this->emit('blacklistUpdate');
    }

    public function render()
    {
        return view('livewire.komponente.blacklist-add-remove');
    }
}
