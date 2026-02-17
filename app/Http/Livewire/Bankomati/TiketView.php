<?php

namespace App\Http\Livewire\Bankomati;

use Livewire\Component;

use App\Models\BankomatTiketKomantar;
use App\Models\BankomatLocijaHirtory;
use App\Models\BankomatLokacija;

use App\Models\BankomatTiket;
use App\Models\User;
use Illuminate\Support\Facades\Config;

use App\Actions\Bankomati\BankomatTiketMailingActions;
use Illuminate\Support\Facades\DB;

use App\Actions\Bankomati\BankomatTimeActions;

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
    public $bankomat_status_id;

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
    public $serviseri;
    public $prioritet;
    public $kvarTipNaziv;
    public $modalStatusFormVisible;
    public $old_datum_promene;
    public $bankomat_status;
    public $datum_promene;
    public $datum_promene_error;

    public $role_region;
    public $can_change_prioritet = false;

    public $modalPrioritetFormVisible;

    public $zatvori_datum_promene;
    public $zatvori_vreme_promene;

    public $naplata;
    public $old_naplata;
    

    /**
     * Listeners for Livewire events
     *
     * @var array
     */
    protected $listeners = ['statusChanged'];

    public function statusChanged()
    {
        $this->modalStatusFormVisible = false;
    }

    public function mount()
    {
        $this->tikid = request()->query('id');
        $this->tiket = BankomatTiket::where('id', '=', $this->tikid)->first();
        //does ticket exist
        if(!$this->tiket) {
            abort(404);
        }
        $this->bankomat_lokacija_id = $this->tiket->bankomat_lokacija_id;
        $this->naplata = ($this->tiket->naplata) ? 1 : 0; //$this->tiket->naplata;
        $this->old_naplata = ($this->tiket->naplata) ? 1 : 0;
        

        $this->tiketLokacija = $this->tiket->lokacija()->first();
        $this->ticketRegion = $this->tiketLokacija->bankomat_region_id;
        
        //is user allowed to see this ticket
        $this->role_region =auth()->user()->userBankmatPositionAndRegion();
        if($this->role_region['role'] != 'admin') {
            if($this->role_region['region'] != $this->ticketRegion) {
                //proverimo da li je serviser ili sef
                if($this->role_region['role'] == 'sef'){
                    //pokupi idjeve svih u servisu
                    $this->serviseri = User::select('users.id')
                                            ->join('blokacijas', 'blokacijas.id', '=', 'users.lokacijaId')
                                            ->join('bankomat_regions', 'bankomat_regions.id', '=', 'blokacijas.bankomat_region_id')
                                            ->whereIn('pozicija_tipId', [10, 11])
                                            ->where('bankomat_region_id', $this->role_region['region'])  
                                            ->pluck('id')
                                            ->toArray();
                }else{
                    $this->serviseri = [auth()->user()->id];
                }
                //dd($this->tiket->user_dodeljen_id, $this->serviseri);
                if(!in_array($this->tiket->user_dodeljen_id, $this->serviseri)){
                    abort(404);
                }
            }
        }

        $this->can_change_prioritet = ($this->role_region['role'] == 'admin' || $this->role_region['role'] == 'sef') ? true : false;

        $this->prioritet = $this->tiket->prioritet()->first();
        $this->userKreirao = User::where('id', '=', $this->tiket->user_prijava_id)->first();
        $this->userDodeljen = User::where('id', '=', $this->tiket->user_dodeljen_id)->first();
        $this->uderZatvorio = User::where('id', '=', $this->tiket->user_zatvorio_id)->first();

        $kvarTip = $this->tiket->kvarTip()->first();
        
        $this->kvarTipNaziv = $kvarTip->btkt_naziv ?? 'Ostalo';
        
        $this->komentari = $this->tiket->komentari()->get();
    }

    public function prioritetShowModal()
    {
        $this->modalPrioritetFormVisible = true;
    }

    public function setPrioritet($prioritet){
        //dd($prioritet);
        /* BankomatTiket::where('id', '=', $this->tikid)->update(['bankomat_tiket_prioritet_id' => $prioritet]);
        $this->tiket = BankomatTiket::where('id', '=', $this->tikid)->first(); */

        $this->tiket->bankomat_tiket_prioritet_id = $prioritet;
        $this->tiket->save();
        $this->prioritet = $this->tiket->prioritet()->first();
        $this->modalPrioritetFormVisible = false;
    }

    public function statusShowModal()
    {
        $this->datum_promene = date('Y-m-d');
        $this->datum_promene_error = '';
        $this->modalStatusFormVisible = true;
    }

    public function statusUpdate()
   {
        $this->validate(['datum_promene' => 'required|date']);

        $cuurent = BankomatLokacija::where('id', '=', $this->modelId)->first();

        if($cuurent->bankomat_status_tip_id == $this->bankomat_status) {
            $this->datum_promene_error = 'Niste promenili status.';
            return;
        }

        //Provera datuma koji je korisnik uneo za promenu statusa
        if(!$this->validDatumPromene([1, 2, 4])) return;
        
        //dd($this->datum_promene);
        DB::transaction(function()use($cuurent){
            $cuurent->update(['bankomat_status_tip_id' => $this->bankomat_status, 'updated_at' => $this->datum_promene]);

            BankomatLocijaHirtory::create([
                'bankomat_lokacija_id' => $this->modelId,
                'bankomat_id' => $cuurent['bankomat_id'],
                'blokacija_id' => $cuurent['blokacija_id'],
                'bankomat_status_tip_id' => $cuurent['bankomat_status_tip_id'],
                'user_id' => $cuurent['user_id'],
                'naplata' => $cuurent['naplata'],
                'updated_at' => $cuurent['updated_at'],
                'history_action_id' => 2
            ]);  
             
            
        });

        $this->modalStatusFormVisible = false;
    }

    public function dodeliTiketShowModal()
    {
        $this->noviDodeljenUserId = null;
        if($this->role_region['role'] == 'serviser') $this->setDodeljenUserInfo(auth()->user()->id);
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
        $this->tiket->newHistroy(4);

        $mail_action = new BankomatTiketMailingActions( $this->tiket->id);
        $mail_action->sendEmails("dodeljen");

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
        $positions = ($this->role_region['role'] == 'admin') ? [9, 10, 11] : [10, 11];
        return User::select('users.id', 'users.name', 'blokacijas.bl_naziv', 'pozicija_tips.naziv')
                    ->leftJoin('blokacijas', 'users.lokacijaId', '=', 'blokacijas.id')
                    ->leftJoin('pozicija_tips', 'users.pozicija_tipId', '=', 'pozicija_tips.id')
                    ->leftJoin('bankomat_regions', 'bankomat_regions.id', '=', 'blokacijas.bankomat_region_id')
                    ->where('name', 'like', '%'.$this->searchUserName.'%')
                    ->where('bl_naziv', 'like', '%'.$this->searchUserLokacija.'%')
                    ->where('naziv', 'like', '%'.$this->searchUserPozicija.'%')
                    ->whereIn('users.pozicija_tipId', $positions)
                    ->when($this->tiket->user_dodeljen_id, function ($query) {
                        $query->where('users.id', '!=', $this->tiket->user_dodeljen_id);
                    })
                    ->when($this->role_region['role'] != 'admin', function ($query) {
                        $query->where('bankomat_regions.id', '=', $this->role_region['region']);
                    })
                    ->paginate(Config::get('global.modal_search'), ['*'], 'usersp');
    }

    public function zatvoriTiketShowModal()
    {
        $this->modalZatvoriTiketVisible = true;
        $this->zatvori_datum_promene = date('Y-m-d');
        $this->zatvori_vreme_promene = date('H:i:s');
        $this->datum_promene_error = '';
    }

    public function closeTiket()
    {
        $this->validate([
            'zatvori_komentar' => 'nullable|string|min:3|max:1000',
            'zatvori_datum_promene' => 'required|date',
            'zatvori_vreme_promene' => 'required|date_format:H:i:s',
        ]);

        if(!BankomatTimeActions::compareTicketTime($this->bankomat_lokacija_id, $this->zatvori_datum_promene, $this->zatvori_vreme_promene, 'close')) {
            $this->datum_promene_error = 'Datum zatvaranja tiketa ne može biti manji od datuma otvaranja tiketa.';
            return false;
        }

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
        $this->tiket->newHistroy(3);
        //$action: 
        // 9 - zatvoren
        // 10 - obrisan tiket
        $this->addBankomatHistory(9);

        $mail_action = new BankomatTiketMailingActions( $this->tiket->id);
        $mail_action->sendEmails("zatvoren");

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
            $created_at = $this->zatvori_datum_promene . ' ' . $this->zatvori_vreme_promene;
            $new_histroy['created_at'] = $created_at;
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

    public function updated()
    {
       if($this->naplata != $this->old_naplata) {
            $this->old_naplata = $this->naplata;
            $this->tiket->naplata = $this->naplata;
            $this->tiket->update();
       };
    }
    public function render()
    {
        $this->bankomat_lokacija_id = $this->tiket->bankomat_lokacija_id;
        //Promena statusa modal
        $bankomat_lokacija_model = BankomatLokacija::where('id', '=', $this->bankomat_lokacija_id)->first();
        $this->old_datum_promene = $bankomat_lokacija_model->updated_at;
        $this->bankomat_status = $bankomat_lokacija_model->status->status_naziv;
        $this->bankomat_status_id = $bankomat_lokacija_model->status->id;

        return view('livewire.bankomati.tiket-view');
    }
}
