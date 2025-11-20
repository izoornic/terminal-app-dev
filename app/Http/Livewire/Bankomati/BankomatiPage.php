<?php

namespace App\Http\Livewire\Bankomati;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\Bankomat;
use App\Models\BankomatsHistory;
use App\Models\BankomatLokacija;
use App\Models\BankomatLocijaHirtory;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

use App\Http\Helpers;

use App\Actions\Bankomati\BankomatiReadActions;

class BankomatiPage extends Component
{
    use WithPagination;

    //USER ROLE
    public $role_region;

    //NEW PRODUCT
    public $modelId;
    public $modalNewVisible = false;
    public $modalEditVisible = false;
    //SEARCH
    public $searchSB;
    public $searchTerminalId;
    public $searchModel;
    public $searchLokacijaNaziv;
    public $searchRegion;
    public $searchLocationTip;
    public $searchStatus;
    public $searchPib;
    public $searchProductTip;
    public $searchMesto;
    public $searchNazivSufix;

    //NEW EDIT
    public $b_sn;
    public $old_b_sn;
    public $bankomat_tid;
    public $proizvod_model;
    public $proizvod_model_tip;
    public $bankomat_status;
    public $bankomat_lokacija;
    public $datum_promene;
    public $datum_promene_error;
    public $old_datum_promene;
    public $vlasnik_proizvoda;
    public $old_vlasnik_proizvoda;
    public $flashKey = 1;
    public $location_key = 100;

    //STATUS MODAL
    public $modalStatusFormVisible = false;

    //PREMESTI MODAL
    public $modalPremestiVisible = false;
    public $vrsta_lokacije;
    public $nova_lokacija;

    public $searchPLokacijaNaziv;
    public $searchPlokacijaMesto;
    public $searchPlokacijaRegion;

    //HISTORY
    public $modalProizvodlHistoryVisible = false;

    //NEW TICKET
    public $modalNewTicketVisible = false;
    public $flashMessage;
    public $flashError;
    
    //NAPLATA MODAL
    public $modalNaplatVisible = false;
    public $naplata;
    public $old_naplata;

    //testing info component
   /*  public $selectedTerminals = [1,2,3,8,9,10];
    public $multiSelected = true; */

    /**
     * Listeners for Livewire events
     *
     * @var array
     */
    protected $listeners = ['newBankomat', 'newTicketCreated', 'novaLokacija'];

    public function newBankomat()
    {
        //SHOW MODAL
        $this->resetInputFields();
        $this->resetValidation();
        $this->datum_promene = date('Y-m-d');
        $this->flashKey ++;
        $this->location_key ++;
        $this->modalNewVisible = true;
    }

    public function newTicketCreated($id)
    {
        $this->modalNewTicketVisible = false;
        $this->emit('flashMessage', 'Tiket #'.$id.' je uspešno dodat.');
    }

    public function novaLokacija($id, $key)
    {
        //dd($id, $key);
        if($key == 'lokacija') $this->bankomat_lokacija = $id;
        if($key == 'vlasnik') $this->vlasnik_proizvoda = $id;
        if($key == 'premesti') $this->nova_lokacija = $id;
    }
    
    
    public function mount()
    {
        $this->role_region =auth()->user()->userBankmatPositionAndRegion();
    }

    private function resetInputFields()
    {
        $this->modelId = null;
        $this->b_sn = '';
        $this->bankomat_tid = '';
        $this->proizvod_model = '';
        $this->bankomat_status = '';
        $this->bankomat_lokacija = '';
        $this->old_b_sn = '';
        $this->vrsta_lokacije = '';
        $this->nova_lokacija = '';
        $this->proizvod_model_tip = '';
        $this->datum_promene_error = '';
        $this->vlasnik_proizvoda = '';
        $this->old_vlasnik_proizvoda = '';

        $this->searchPLokacijaNaziv = '';
        $this->searchPlokacijaMesto = '';
        $this->searchPlokacijaRegion = 0;

        $this->naplata = '';
        $this->old_naplata = '';
    }

    public function saveBankomat()
    {
        $this->validate(
            [ 
                'proizvod_model_tip' => 'required|numeric|exists:bankomat_product_tips,id',
                'b_sn' => 'required|string|max:255|unique:bankomats',
                'bankomat_tid' => 'nullable|string|max:255',
                'proizvod_model' => 'required|numeric',
                'bankomat_status' => 'required|numeric',
                'bankomat_lokacija' => 'required|exists:blokacijas,id',
                'vlasnik_proizvoda' => 'required|numeric|exists:blokacijas,id',
                'datum_promene' => 'required|date',
            ]);
        $this->datum_promene .= ' ' . Helpers::vremeKalendarNow();
        
        // Save logic here, e.g., create or update the location in the database
        DB::transaction(function() {
            //Bankomat
            $cuurent = Bankomat::create($this->modelData());
            //BankomatLokacija
            $p_ban_loc = BankomatLokacija::create([
                'bankomat_id' => $cuurent['id'], 
                'blokacija_id' => $this->bankomat_lokacija,
                'bankomat_status_tip_id' => $this->bankomat_status,
                'user_id' => auth()->user()->id,
                'created_at' => $this->datum_promene,
                'updated_at' => $this->datum_promene
            ]);
            BankomatLocijaHirtory::create([
                'bankomat_lokacija_id' => $p_ban_loc['id'],
                'bankomat_id' => $cuurent['id'], 
                'blokacija_id' => $this->bankomat_lokacija,
                'bankomat_status_tip_id' => $this->bankomat_status,
                'user_id' => auth()->user()->id,
                'created_at' => $this->datum_promene,
                'updated_at' => $this->datum_promene,
                'history_action_id' => 1
                ]);
        });
        $this->resetInputFields();
        $this->modalNewVisible = false;
    }

    public function editBankomat($id)
    {
        //SHOW MODAL
        //Ovde je $id iz tabele bankomats jer se edituju samo polja iz te tabele
        $this->resetValidation();
        $this->resetInputFields();
        $this->modelId = $id ?? null;
        $this->loadModel();
        $this->location_key ++;
        $this->modalEditVisible = true;

    }

    public function updateBankomat()
    {
        $this->validate(
            [
                'b_sn' => ($this->b_sn == $this->old_b_sn) ? '' : 'required|string|max:255|unique:bankomats',
                'bankomat_tid' => 'nullable|string|max:255',
                'proizvod_model' => 'required|numeric',
                'vlasnik_proizvoda' => 'required|numeric|exists:blokacijas,id',
            ]
        );
        $bankomat = Bankomat::where('id', '=', $this->modelId)->first();
        $bankomat->update($this->modelData());
        //ako je promenjen vlasnik proizvoda upisati u history
        if($this->old_vlasnik_proizvoda != $this->vlasnik_proizvoda){
            $cuurent = BankomatLokacija::where('bankomat_id', '=', $bankomat->id)->first();
            BankomatLocijaHirtory::create([
                'bankomat_lokacija_id' => $cuurent['id'],
                'bankomat_id' => $bankomat['id'], 
                'blokacija_id' => $cuurent['blokacija_id'],
                'bankomat_status_tip_id' => $cuurent['bankomat_status_tip_id'],
                'user_id' => auth()->user()->id,
                'naplata' => $cuurent['naplata'],
                'history_action_id' => 7
                ]);
            BankomatsHistory::create([
                'bankomat_id' => $bankomat['id'],
                'bankomat_tip_id' => $bankomat['bankomat_tip_id'],
                'b_sn' => $bankomat['b_sn'],
                'b_terminal_id' => $bankomat['b_terminal_id'],
                'komentar' => 'Promenjen vlasnik proizvoda',
                'vlasnik_uredjaja' => $this->old_vlasnik_proizvoda,
                'user_id' => auth()->user()->id
                ]);
        }

        $this->resetInputFields();
        $this->modalEditVisible = false;
        
    }

    private function modelData()
    {
        return [
            'b_sn' => $this->b_sn,
            'b_terminal_id' => ($this->bankomat_tid) ?: null,
            'bankomat_tip_id' => $this->proizvod_model,
            'vlasnik_uredjaja' => $this->vlasnik_proizvoda
        ];
    }

    public function loadModel()
    {
        $bankomat = Bankomat::where('id', '=', $this->modelId)->first();
        $this->proizvod_model_tip = $bankomat->bankomat_produkt_tip()->first()->id;
        $this->b_sn = $bankomat->b_sn;
        $this->old_b_sn = $bankomat->b_sn;
        $this->bankomat_tid = $bankomat->b_terminal_id; 
        $this->proizvod_model = $bankomat->bankomat_tip_id;
        $this->vlasnik_proizvoda = $bankomat->vlasnik_uredjaja;
        $this->old_vlasnik_proizvoda = $bankomat->vlasnik_uredjaja;
        //dd($this->proizvod_model_tip, $this->b_sn, $this->bankomat_tid, $this->proizvod_model, $this->vlasnik_proizvoda);
    }

    private function validDatumPromene($history_a) 
    {
        $history_actions = $history_a ?? null;
        //Poslednja akcije koja se menja
        $last_change_model = BankomatLocijaHirtory::where('bankomat_lokacija_id', '=', $this->modelId)->whereIn('history_action_id', $history_actions)->orderBy('updated_at', 'desc')->first();

        //dd($history_actions,$last_change_model, $this->datum_promene);
        if(Helpers::equalGraterOrLessThan($last_change_model->updated_at->format('Y-m-d'), $this->datum_promene) == 'gt') {
            $this->datum_promene_error = 'Datum promene ne može biti manji od datuma poslednje promene.';
            return false;
        }

        $this->datum_promene .= ' ' . date('H:i:s');
        return true;
    }

    public function statusShowModal($id, $status)
    {
        $this->resetInputFields();
        $this->modelId = $id; //ovo je id terminal_lokacijas tabele
        $this->old_datum_promene =  BankomatLokacija::where('id', '=', $this->modelId)->first()->updated_at;
        $this->bankomat_status = $status;
        $this->datum_promene = date('Y-m-d');

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

    public function premestiShowModal($id, $status)
    {
        $this->resetInputFields();
        $this->resetValidation();
        $this->modelId = $id; //ovo je id terminal_lokacijas tabele
        $this->bankomat_status = $status;
        $this->flashKey ++;
        $this->datum_promene = date('Y-m-d');
        $this->modalPremestiVisible = true;
    }

    public function moveBankomat()
    {
        $this->validate([
            'datum_promene' => 'required|date', 
            'nova_lokacija' => 'required|exists:blokacijas,id'
    ]);
        $cuurent = BankomatLokacija::where('id', '=', $this->modelId)->first();
        //ako je isti status akcija je [3] PREMESTEN ako je razlicit status onda je akcija [4] Premesten i promenjen status
        $history_akcija = ($cuurent->bankomat_status_tip_id == $this->bankomat_status) ? 3 : 4;

        if($cuurent->blokacija_id == $this->nova_lokacija) {
            $this->datum_promene_error = 'Niste promenili lokaciju.';
            return;
        }

        //Provera datuma koji je korisnik uneo za promenu statusa
        if(!$this->validDatumPromene([1, 3, 4])) return;

        DB::transaction(function()use($cuurent, $history_akcija){
            $cuurent->update(['blokacija_id' => $this->nova_lokacija, 'bankomat_status_tip_id' => $this->bankomat_status, 'updated_at' => $this->datum_promene]);

            BankomatLocijaHirtory::create([
                'bankomat_lokacija_id' => $this->modelId,
                'bankomat_id' => $cuurent['bankomat_id'],
                'blokacija_id' => $cuurent['blokacija_id'],
                'bankomat_status_tip_id' => $cuurent['bankomat_status_tip_id'],
                'user_id' => $cuurent['user_id'],
                'naplata' => $cuurent['naplata'],
                'updated_at' => $cuurent['updated_at'],
                'history_action_id' => $history_akcija
            ]);              
            
        });

        $this->modalPremestiVisible = false;
    }

    public function proizvodlHistoryShowModal($id)
    {
        $this->modelId = $id;
        $this->modalProizvodlHistoryVisible = true;
    }

    public function newTiketShowModal($id)
    {
        $this->modelId = $id;
        $this->modalNewTicketVisible = true;
    }

    public function naplatShowModal($id, $naplata)
    {
        $this->resetInputFields();
        $this->modelId = $id;
        $this->naplata = $naplata;
        $this->old_naplata = $naplata; //BankomatLokacija::where('id', '=', $this->modelId)->first()->naplata;
        $this->datum_promene = date('Y-m-d');
        //dd($this->old_naplata);
        $this->modalNaplatVisible = true;
    }

    public function promeniNaplatu()
    {
       if($this->old_naplata != $this->naplata) {
            if(!$this->validDatumPromene([1, 5, 6])) return;
            $history_akcija = ($this->naplata) ? 5 : 6;

            DB::transaction(function()use($history_akcija){
                $cuurent = BankomatLokacija::where('id', '=', $this->modelId)->first();
                $cuurent->update(['naplata' => ($this->naplata ? 1 : 0), 'updated_at' => $this->datum_promene]);

                BankomatLocijaHirtory::create([
                'bankomat_lokacija_id' => $this->modelId,
                'bankomat_id' => $cuurent['bankomat_id'],
                'blokacija_id' => $cuurent['blokacija_id'],
                'bankomat_status_tip_id' => $cuurent['bankomat_status_tip_id'],
                'user_id' => $cuurent['user_id'],
                'naplata' => $cuurent['naplata'],
                'updated_at' => $cuurent['updated_at'],
                'history_action_id' => $history_akcija
            ]);          
            });
       }else {
           $this->datum_promene_error = 'Niste promenili naplatu.';
            return;
       }
        
        $this->modalNaplatVisible = false;
    }

    
    public function read()
    {
        $searchParams=[
            'b_sn' => $this->searchSB,
            'b_terminal_id' => $this->searchTerminalId,
            'proizvod_model' => $this->searchModel,
            'lokacija_naziv' => $this->searchLokacijaNaziv,
            'region' => ($this->role_region['role'] == 'admin') ? $this->searchRegion : $this->role_region['region'],
            'tip' => $this->searchLocationTip,
            'status' => $this->searchStatus,
            'pib' => $this->searchPib,
            'product_tip' => $this->searchProductTip,
            'naziv_sufix' => $this->searchNazivSufix,
            'mesto' => $this->searchMesto,

        ]; 
        
       $builder = BankomatiReadActions::BankomatiRead($searchParams);
       // paginate the builder
        $perPage = Config::get('global.terminal_paginate');
        $terms = $builder->paginate($perPage, ['*'], 'terminali');
        return $terms;
    }   
    /**
     * Render the component view
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.bankomati.bankomati-page', [
            'data' => $this->read(),
        ]);
    }
}
