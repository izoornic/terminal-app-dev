<?php

namespace App\Http\Livewire\Bankomati;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\Bankomat;
use App\Models\Blokacija;
use App\Models\BankomatLokacija;
use App\Models\BankomatLocijaHirtory;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

use App\Http\Helpers;

use App\Actions\Bankomati\BankomatiReadActions;
use Illuminate\Http\Request;

class BankomatiPage extends Component
{
    use WithPagination;

    public $modelId;
    public $is_edit = false;
    public $modalNewEditVisible = false;

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
    public $flashKey = 1;

    //testing info component
   /*  public $selectedTerminals = [1,2,3,8,9,10];
    public $multiSelected = true; */
    

    /**
     * Listeners for Livewire events
     *
     * @var array
     */
    protected $listeners = ['newBankomat', 'newTicketCreated'];

    public function newBankomat()
    {
        //SHOW MODAL
        $this->resetInputFields();
        $this->resetValidation();
        $this->datum_promene = date('Y-m-d');
        $this->is_edit = false;
        $this->modalNewEditVisible = true;
    }

    public function newTicketCreated($id)
    {
        $this->modalNewTicketVisible = false;
        $this->emit('flashMessage', 'Tiket #'.$id.' je uspešno dodat.');
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

        $this->searchPLokacijaNaziv = '';
        $this->searchPlokacijaMesto = '';
        $this->searchPlokacijaRegion = 0;
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
                'bankomat_lokacija' => 'required|numeric',
                'datum_promene' => 'required|date',
            ]);
        $this->datum_promene .= ' ' . Helpers::vremeKalendarNow();
        
        // Save logic here, e.g., create or update the location in the database
        DB::transaction(function(){
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
        $this->modalNewEditVisible = false;
    }

    public function editBankomat($id)
    {
        //SHOW MODAL
        //Ovde je $id iz tabele bankomats jer se edituju samo polja iz te tabele
        $this->resetValidation();
        $this->resetInputFields();
        $this->modelId = $id;
        $this->is_edit = true;
        $this->loadModel();
        $this->modalNewEditVisible = true;

    }

    public function updateBankomat()
    {
        $this->validate(
            [
                'b_sn' => ($this->b_sn == $this->old_b_sn) ? '' : 'required|string|max:255|unique:bankomats',
                'bankomat_tid' => 'nullable|string|max:255',
                'proizvod_model' => 'required|numeric',
            ]
        );
        $bankomat = Bankomat::where('id', '=', $this->modelId)->first();
        $bankomat->update($this->modelData());
        $this->resetInputFields();
        $this->modalNewEditVisible = false;
        
    }

    private function modelData()
    {
        return [
            'b_sn' => $this->b_sn,
            'b_terminal_id' => ($this->bankomat_tid) ?: null,
            'bankomat_tip_id' => $this->proizvod_model,
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

        if(Helpers::equalGraterOrLessThan($cuurent->updated_at, $this->datum_promene.' ' . Helpers::vremeDbDate($cuurent->updated_at)) == 'gt') {
            $this->datum_promene_error = 'Datum promene ne može biti manji od datum poslednje promene.';
            return;
        }elseif(Helpers::equalGraterOrLessThan(date('Y-m-d'), $this->datum_promene) == 'eq') {
            $this->datum_promene = date('Y-m-d H:i:s');
        }else{
            $this->datum_promene .= ' ' . Helpers::vremeDbDate($cuurent->updated_at);
        }

        DB::transaction(function()use($cuurent){
            $cuurent->update(['bankomat_status_tip_id' => $this->bankomat_status, 'updated_at' => $this->datum_promene]);

            BankomatLocijaHirtory::create([
                'bankomat_lokacija_id' => $this->modelId,
                'bankomat_id' => $cuurent['bankomat_id'],
                'blokacija_id' => $cuurent['blokacija_id'],
                'bankomat_status_tip_id' => $cuurent['bankomat_status_tip_id'],
                'user_id' => $cuurent['user_id'],
                'created_at' => $cuurent['created_at'],
                'updated_at' => $cuurent['updated_at'],
                'history_action_id' => 2
            ]);  
             
            
        });

        $this->modalStatusFormVisible = false;
    }

    public function premestiShowModal($id, $status)
    {
        $this->resetInputFields();
        $this->modelId = $id; //ovo je id terminal_lokacijas tabele
        $this->bankomat_status = $status;
        $this->modalPremestiVisible = true;
    }

    public function moveBankomat()
    {
        $this->validate(['datum_promene' => 'required|date']);
        $cuurent = BankomatLokacija::where('id', '=', $this->modelId)->first();
        //ako je isti status akcija je [3] PREMESTEN ako je razlicit status onda je akcija [4] Premesten i promenjen status
        $history_akcija = ($cuurent->bankomat_status_tip_id == $this->bankomat_status) ? 3 : 4;

        if($cuurent->blokacija_id == $this->nova_lokacija) {
            $this->datum_promene_error = 'Niste promenili lokaciju.';
            return;
        }

        if(Helpers::equalGraterOrLessThan($cuurent->updated_at, $this->datum_promene.' ' . Helpers::vremeDbDate($cuurent->updated_at)) == 'gt') {
            $this->datum_promene_error = 'Datum promene ne može biti manji od datum poslednje promene.';
            return;
        }elseif(Helpers::equalGraterOrLessThan(date('Y-m-d'), $this->datum_promene) == 'eq') {
            $this->datum_promene = date('Y-m-d H:i:s');
        }else{
            $this->datum_promene .= ' ' . Helpers::vremeDbDate($cuurent->updated_at);
        }


        DB::transaction(function()use($cuurent, $history_akcija){
            $cuurent->update(['blokacija_id' => $this->nova_lokacija, 'bankomat_status_tip_id' => $this->bankomat_status, 'updated_at' => $this->datum_promene]);

            BankomatLocijaHirtory::create([
                'bankomat_lokacija_id' => $this->modelId,
                'bankomat_id' => $cuurent['bankomat_id'],
                'blokacija_id' => $cuurent['blokacija_id'],
                'bankomat_status_tip_id' => $cuurent['bankomat_status_tip_id'],
                'user_id' => $cuurent['user_id'],
                'created_at' => $cuurent['created_at'],
                'updated_at' => $cuurent['updated_at'],
                'history_action_id' => $history_akcija
            ]);              
            
        });

        $this->modalPremestiVisible = false;
    }

    /**
     * Puni tabelu u modalu iz koje se bira lokacija
     *
     * @param mixed $tipId
     * 
     * @return [type]
     * 
     */
    public function lokacijeTipa($tipId)
    {
        return Blokacija::select('blokacijas.*', 'bankomat_regions.r_naziv')
            ->leftJoin('bankomat_regions', 'blokacijas.bankomat_region_id', '=', 'bankomat_regions.id')
            ->where('blokacijas.blokacija_tip_id', '=', $tipId)
            ->where('bl_naziv', 'like', '%'.$this->searchPLokacijaNaziv.'%')
            ->where('bl_mesto', 'like', '%'.$this->searchPlokacijaMesto.'%')
            ->where('blokacijas.bankomat_region_id', ($this->searchPlokacijaRegion > 0) ? '=' : '<>', $this->searchPlokacijaRegion)
            ->paginate(Config::get('global.modal_search'), ['*'], 'loc');
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

    
    public function read()
    {
        $searchParams=[
            'b_sn' => $this->searchSB,
            'b_terminal_id' => $this->searchTerminalId,
            'proizvod_model' => $this->searchModel,
            'lokacija_naziv' => $this->searchLokacijaNaziv,
            'region' => $this->searchRegion,
            'tip' => $this->searchLocationTip,
            'status' => $this->searchStatus,
            'pib' => $this->searchPib,
            'product_tip' => $this->searchProductTip
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
