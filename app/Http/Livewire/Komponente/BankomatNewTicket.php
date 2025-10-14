<?php

namespace App\Http\Livewire\Komponente;

use Livewire\Component;
use Livewire\WithPagination;

use App\Actions\Bankomati\BankomatInformation;
use App\Actions\Bankomati\BankomatTiketMailingActions;

use Illuminate\Support\Facades\Config;

use App\Models\User;
use App\Models\BankomatTiket;
use App\Models\BankomatTiketPrioritetTip;

use Illuminate\Http\Request;

class BankomatNewTicket extends Component
{
    use WithPagination;

    public $bankomat_lokacija_id;
    public $productTipId;
    public $bakomatRegionId;

    public $vrsta_kvara;
    public $opis_kvara;

    public $userPozicija;
    public $dodeljenUserId;
    public $dodeljenUserInfo;

    public $searchUserName;
    public $searchUserLokacija;
    public $searchUserPozicija;

    public $prioritetTiketa;
    public $prioritetInfo;

    public function mount($bankomat_lokacija_id)
    {
        $this->bankomat_lokacija_id = $bankomat_lokacija_id;
        $this->selectedBankomatTip = BankomatInformation::BankomatProizvodTip($this->bankomat_lokacija_id);
        $this->productTipId = $this->selectedBankomatTip->bankomat_produkt_tip_id;
        $this->bakomatRegionId = $this->selectedBankomatTip->bankomat_region_id;

        $this->userPozicija = auth()->user()->pozicija_tipId;
        // 9 - Admin bankomata
        // 10 - Šef servisa bankomata
        // 11 - Serviser bankomata
        if($this->userPozicija == 11){
            $this->dodeljenUserId = auth()->user()->id;
        }

        //$this->prioritetTiketa = 1;
        //$this->setPrioritetInfo($this->prioritetTiketa);
        //dd(BankomatTiketPrioritetTip::prList());
       
    }

    public function setDodeljenUserInfo($dodeljenUserId)
    {
        $this->dodeljenUserId = $dodeljenUserId;
        $this->dodeljenUserInfo = User::select('users.id', 'users.name', 'blokacijas.bl_naziv', 'blokacijas.bl_mesto', 'pozicija_tips.naziv')
                    ->leftJoin('blokacijas', 'users.lokacijaId', '=', 'blokacijas.id')
                    ->leftJoin('pozicija_tips', 'users.pozicija_tipId', '=', 'pozicija_tips.id')
                    ->where('users.id', $dodeljenUserId)->first();
    }

    public function searchUser()
    {
        return User::select('users.id', 'users.name', 'blokacijas.bl_naziv', 'pozicija_tips.naziv')
                    ->leftJoin('blokacijas', 'users.lokacijaId', '=', 'blokacijas.id')
                    ->leftJoin('pozicija_tips', 'users.pozicija_tipId', '=', 'pozicija_tips.id')
                    ->leftJoin('bankomat_regions', 'bankomat_regions.id', '=', 'blokacijas.bankomat_region_id')
                    ->where('name', 'like', '%'.$this->searchUserName.'%')
                    ->where('bl_naziv', 'like', '%'.$this->searchUserLokacija.'%')
                    ->where('naziv', 'like', '%'.$this->searchUserPozicija.'%')
                    ->whereIn('users.pozicija_tipId', [9,10, 11])
                    ->paginate(Config::get('global.modal_search'), ['*'], 'usersp');
    }

    public function setPrioritetInfo($id)
    {
        $this->prioritetTiketa = $id;
        $this->prioritetInfo = BankomatTiketPrioritetTip::prioritetInfo($id);
        //dd($id,$this->prioritetInfo);
        //$this->render();
    }

    public function newTicket(Request $request)
    {
        $this->validate([
            'vrsta_kvara' => 'required|numeric',
            'opis_kvara' => 'required|string|max:255',
            'dodeljenUserId' => 'required',
            'prioritetTiketa' => 'required',
        ]);

        /* $ticket = new BankomatTiket();
        $ticket->bankoamt_lokacija_id = $this->bankomat_lokacija_id;
        $ticket->bankomat_tiket_kvar_tip_id = ($this->vrsta_kvara == 1000) ? null : $this->vrsta_kvara;
        $ticket->opis = $this->opis_kvara;
        $ticket->user_prijava_id = auth()->user()->id;
        $ticket->user_dodeljen_id = $this->dodeljenUserId;
        $ticket->bankomat_tiket_prioritet_id = $this->prioritetTiketa;
        $ticket->save();

        $mail_action = new BankomatTiketMailingActions($ticket->id);
        $mail_action->sendEmails("novi"); */

        $this->emit('newTicketCreated', 168); // $ticket->id);
        
    }

    public function render()
    {
        return view('livewire.komponente.bankomat-new-ticket');
    }
}
