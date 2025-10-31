<?php

namespace App\Http\Livewire\Bankomati;

use Livewire\Component;

use App\Models\BankomatTiketKomantar;
use App\Models\BankomatLocijaHirtory;
use App\Models\BankomatLokacija;

use App\Models\BankomatTiket;
use App\Models\User;
use Illuminate\Support\Facades\Config;

class TiketView extends Component
{
    public $tikid;
    public $tiket;
    public $validTiket = true;
    public $tiketLokacija;
    public $ticketRegion;
    public $userKreirao;
    public $userDodeljen;
    public $uderZatvorio;
    //public $kvarTip;
    public $kvatTipNaziv;
    public $bankomat_lokacija_id;

    public $historyData;

    //komentari
    public $modalKomentariVisible;
    public $komentari;

    //Zatvori tiket
    public $modalZatvoriTiketVisible;
    public $zatvori_komentar;

    //Obrisi tiket
    public $obrisiTiketModalVisible;

    //Dodeli tiket
    public $modalDodeliTiketVisible;
    public $searchUserName;
    public $searchUserLokacija;
    public $searchUserPozicija;
    public $noviDodeljenUserId;
     public $dodeljenUserInfo;

    public $user_pozicija_id;
     public function mount()
    {
        $this->tikid = request()->query('id');
        $this->tiket = BankomatTiket::where('id', '=', $this->tikid)->first();
        //does ticket exist
        if(!$this->tiket) {
            abort(404);
        }
        $this->bankomat_lokacija_id = $this->tiket->bankoamt_lokacija_id;
        
        $this->tiketLokacija = $this->tiket->lokacija()->first(); //LokacijaInfo::getInfo($this->tiket->tremina_lokacijalId)->first(); $this->tiket->lokacija()->first());
        $this->ticketRegion = $this->tiketLokacija->bankomat_region_id;
        //TODO check if user is allowed to see this ticket
        //is user allowed to see this ticket
        $this->user_pozicija_id = auth()->user()->pozicija_tipId;
        
        if($this->user_pozicija_id == 9 || $this->user_pozicija_id == 10 || $this->user_pozicija_id == 11){
            if($this->user_pozicija_id == 10){
                //sef servisa

            }
        }else{
            $this->validTiket = false;
        }

        if(!$this->validTiket) {
            abort(404);
        }

        $this->prioritet = $this->tiket->prioritet()->first();
        $this->userKreirao = User::where('id', '=', $this->tiket->user_prijava_id)->first();
        $this->userDodeljen = User::where('id', '=', $this->tiket->user_dodeljen_id)->first();
        $this->uderZatvorio = User::where('id', '=', $this->tiket->user_zatvorio_id)->first();

        $kvarTip = $this->tiket->kvarTip()->first();
        
        $this->kvarTipNaziv = $kvarTip->btkt_naziv ?? 'Ostalo';
        
        $this->historyData = $this->tiket->komentari()->get();
        $this->komentari = $this->tiket->komentari()->get();

        //dd($this->prioritet);
    }

    public function dodeliTiketShowModal()
    {
        $this->noviDodeljenUserId = null;
        $this->searchUserName = '';
        $this->searchUserLokacija = '';
        $this->searchUserPozicija = '';
        $this->modalDodeliTiketVisible = true;
    }

    public function changeUser()
    {
        $this->validate([
            'noviDodeljenUserId' => 'required',
        ]);
        $update_tiket = [
            'user_dodeljen_id' => $this->noviDodeljenUserId
        ];
        if($this->tiket->status == 'Otvoren') {
            $update_tiket['status'] = 'Dodeljen';
        }
        $this->tiket->update($update_tiket); 
        $this->modalDodeliTiketVisible = false;
        $this->redirect(request()->header('Referer'));
    }
    public function setDodeljenUserInfo($dodeljenUserId)
    {
        $this->noviDodeljenUserId = $dodeljenUserId;
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
                    ->when($this->tiket->user_dodeljen_id, function ($query) {
                        $query->where('users.id', '!=', $this->tiket->user_dodeljen_id);
                    })
                    ->paginate(Config::get('global.modal_search'), ['*'], 'usersp');
    }

    public function zatvoriTiketShowModal()
    {
        $this->modalZatvoriTiketVisible = true;
    }

    public function closeTiket()
    {
        $this->validate([
            'zatvori_komentar' => 'nullable|string|min:3|max:1000',
        ]);

        $tiket_update = [
            'user_zatvorio_id' => auth()->user()->id,
            'status' => 'Zatvoren',
        ];

        if($this->zatvori_komentar) {
            BankomatTiketKomantar::create([
                'bankomat_tiket_id' => $this->tikid,
                'komentar' => $this->zatvori_komentar,
                'user_id' => auth()->user()->id,
            ]);
            $komentari = BankomatTiketKomantar::where('bankomat_tiket_id', '=', $this->tikid)->get();
            $tiket_update['br_komentara'] = $komentari->count();
        };
        $this->tiket->update($tiket_update);
        //TODO update bankomat history table
        // 9 - zatvoren
        // 10 - obrisan tiket
        $this->addBankomatHistory(9);
        $this->modalZatvoriTiketVisible = false;
        $this->redirect(request()->header('Referer'));
    }

    private function addBankomatHistory($action)
    {
        $current = BankomatLokacija::where('id', '=', $this->bankomat_lokacija_id)->first();
        $new_histroy = [
                'bankomat_lokacija_id'=> $current->id,
                'bankomat_id' => $current->bankomat_id,
                'blokacija_id' => $current->blokacija_id,
                'bankomat_status_tip_id' => $current->bankomat_status_tip_id,
                'user_id' => auth()->user()->id,
                'history_action_id' => $action,
        ];

        if($action == 9) {
            $new_histroy['bankomat_tiket_id'] = $this->tiket->id;
        }
        BankomatLocijaHirtory::create($new_histroy);
    }

    public function deleteTiket()
    {
        $this->tiket->delete();
        $this->addBankomatHistory(10);
        $this->obrisiTiketModalVisible = false;
        $this->redirect(route('bankomat-tiketi'));
    }

    public function obrisiTiketShowModal()
    {
        $this->obrisiTiketModalVisible = true;
    }
    public function render()
    {
        return view('livewire.bankomati.tiket-view');
    }
}
