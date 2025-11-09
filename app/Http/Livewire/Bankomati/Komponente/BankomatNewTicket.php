<?php

namespace App\Http\Livewire\Bankomati\Komponente;

use Livewire\Component;
use Livewire\WithPagination;

use App\Actions\Bankomati\BankomatInformation;
use App\Actions\Bankomati\BankomatTiketMailingActions;

use Illuminate\Support\Facades\Config;

use App\Models\User;
use App\Models\BankomatTiket;
use App\Models\BankomatTiketHistory;
use App\Models\BankomatTiketPrioritetTip;
use App\Models\BankomatLocijaHirtory;

class BankomatNewTicket extends Component
{
    use WithPagination;

    public $tiket_exists = false;

    public $bankomat_lokacija_id; 
    public $bakomatRegionId;

    public $vrsta_kvara;
    public $opis_kvara;

    //dodeljen user
    public $dodeljenUserId;
   
    public $dodeljenUserName;
    public $dodeljenUserPozicija;
    public $dodeljenUserLokacija;
    public $dodeljenUserMesto;


    public $searchUserName;
    public $searchUserLokacija;
    public $searchUserPozicija;

    public $prioritetTiketa;
    public $prioritetInfo;

    public $role_region;

    public function mount($bankomat_lokacija_id)
    {
        $this->role_region =auth()->user()->userBankmatPositionAndRegion();
        $this->bankomat_lokacija_id = $bankomat_lokacija_id;
        $this->tiket_exists = BankomatTiket::where('bankomat_lokacija_id', '=', $this->bankomat_lokacija_id)->where('status', '!=', 'Zatvoren')->first();
        //dd($this->tiket_exists);
        /* if($this->tiket_exists){
            
        } */
        $this->selectedBankomatTip = BankomatInformation::BankomatProizvodTip($this->bankomat_lokacija_id);
        $this->productTipId = $this->selectedBankomatTip->bankomat_produkt_tip_id;
        $this->bakomatRegionId = $this->selectedBankomatTip->bankomat_region_id;

        // 9 - Admin bankomata
        // 10 - Šef servisa bankomata
        // 11 - Serviser bankomata
        if($this->role_region['role'] == 'serviser'){
            $this->dodeljenUserId = auth()->user()->id;
            $this->setDodeljenUserInfo($this->dodeljenUserId);
        }

        //$this->prioritetTiketa = 1;
        //$this->setPrioritetInfo($this->prioritetTiketa);
        //dd(BankomatTiketPrioritetTip::prList());
       
    }

    public function setDodeljenUserInfo($dodeljenUserId)
    {
        $this->dodeljenUserId = $dodeljenUserId;
        $dodeljenUserInfo = User::select('users.id', 'users.name', 'blokacijas.bl_naziv', 'blokacijas.bl_mesto', 'pozicija_tips.naziv')
                    ->leftJoin('blokacijas', 'users.lokacijaId', '=', 'blokacijas.id')
                    ->leftJoin('pozicija_tips', 'users.pozicija_tipId', '=', 'pozicija_tips.id')
                    ->where('users.id', $dodeljenUserId)->first();
        $this->dodeljenUserName = $dodeljenUserInfo->name;
        $this->dodeljenUserPozicija = $dodeljenUserInfo->naziv;
        $this->dodeljenUserLokacija = $dodeljenUserInfo->bl_naziv;
        $this->dodeljenUserMesto = $dodeljenUserInfo->bl_mesto;
    }

    public function searchUser()
    {
        $pozicije_tips = ($this->role_region['role'] == 'admin') ? [9, 10, 11] : [10, 11];
        return User::select('users.id', 'users.name', 'blokacijas.bl_naziv', 'pozicija_tips.naziv')
                    ->leftJoin('blokacijas', 'users.lokacijaId', '=', 'blokacijas.id')
                    ->leftJoin('pozicija_tips', 'users.pozicija_tipId', '=', 'pozicija_tips.id')
                    ->leftJoin('bankomat_regions', 'bankomat_regions.id', '=', 'blokacijas.bankomat_region_id')
                    ->where('name', 'like', '%'.$this->searchUserName.'%')
                    ->where('bl_naziv', 'like', '%'.$this->searchUserLokacija.'%')
                    ->where('naziv', 'like', '%'.$this->searchUserPozicija.'%')
                    ->whereIn('users.pozicija_tipId', $pozicije_tips)
                    ->when($this->role_region['role'] != 'admin', function ($query) {
                        $query->where('bankomat_regions.id', '=', $this->role_region['region']);
                    })
                    ->paginate(Config::get('global.modal_search'), ['*'], 'usersp');
    }

    public function setPrioritetInfo($id)
    {
        $this->prioritetTiketa = $id;
        $this->prioritetInfo = BankomatTiketPrioritetTip::prioritetInfo($id);
        //dd($id,$this->prioritetInfo);
        //$this->render();
    }

    public function newTicket()
    {
        $this->validate([
            'vrsta_kvara' => 'required|numeric',
            'opis_kvara' => 'required|string|max:255',
            'dodeljenUserId' => 'required',
            'prioritetTiketa' => 'required',
        ]);

        $ticket = new BankomatTiket();
        $ticket->bankomat_lokacija_id = $this->bankomat_lokacija_id;
        $ticket->status = 'Dodeljen';
        $ticket->bankomat_tiket_kvar_tip_id = ($this->vrsta_kvara == 1000) ? null : $this->vrsta_kvara;
        $ticket->opis = $this->opis_kvara;
        $ticket->user_prijava_id = auth()->user()->id;
        $ticket->user_dodeljen_id = $this->dodeljenUserId;
        $ticket->bankomat_tiket_prioritet_id = $this->prioritetTiketa;
        $ticket->br_komentara = 0;
        $ticket->save();

        $ticket->newHistroy(2);

        $cuurent = BankomatLocijaHirtory::where('bankomat_lokacija_id', '=', $this->bankomat_lokacija_id)->orderBy('created_at', 'desc')->first();
        BankomatLocijaHirtory::create([
                'bankomat_lokacija_id' => $this->bankomat_lokacija_id,
                'bankomat_id' => $cuurent['bankomat_id'],
                'blokacija_id' => $cuurent['blokacija_id'],
                'bankomat_status_tip_id' => $cuurent['bankomat_status_tip_id'],
                'user_id' => auth()->user()->id,
                'naplata' => $cuurent['naplata'],
                'updated_at' => date('Y-m-d H:i:s'),
                'history_action_id' => 8,
                'bankomat_tiket_id' => $ticket->id
            ]);  

        /*$mail_action = new BankomatTiketMailingActions($ticket->id);
        $mail_action->sendEmails("novi"); */

        $this->emit('newTicketCreated', $ticket->id); //168); // $ticket->id);
        
    }

    public function render()
    {
        return view('livewire.bankomati.komponente.bankomat-new-ticket');
    }
}
