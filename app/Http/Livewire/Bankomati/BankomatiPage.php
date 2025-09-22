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

use App\Actions\Bankomati\BankomatiReadActions;


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
    public $searchTip;
    public $searchStatus;
    public $searchPib;

    //NEW EDIT
    public $b_sn;
    public $old_b_sn;
    public $bankomat_tid;
    public $bankomat_model;
    public $bankomat_status;
    public $bankomat_lokacija;

    //STATUS MODAL
    public $modalStatusFormVisible = false;

    //PREMESTI MODAL
    public $modalPremestiVisible = false;
    public $vrsta_lokacije;
    public $nova_lokacija;

    public $searchPLokacijaNaziv;
    public $searchPlokacijaMesto;
    public $searchPlokacijaRegion;

    /**
     * Listeners for Livewire events
     *
     * @var array
     */
    protected $listeners = ['newBankomat'];

    public function newBankomat()
    {
        //SHOW MODAL
        $this->resetInputFields();
        $this->is_edit = false;
        $this->modalNewEditVisible = true;
    }

    private function resetInputFields()
    {
        $this->modelId = null;
        $this->b_sn = '';
        $this->bankomat_tid = '';
        $this->bankomat_model = '';
        $this->bankomat_status = '';
        $this->bankomat_lokacija = '';
        $this->old_b_sn = '';
        $this->vrsta_lokacije = '';
        $this->nova_lokacija = '';

        $this->searchPLokacijaNaziv = '';
        $this->searchPlokacijaMesto = '';
        $this->searchPlokacijaRegion = 0;
    }

    public function saveBankomat()
    {
        $this->validate(
            [
                'b_sn' => 'required|string|max:255|unique:bankomats',
                'bankomat_tid' => 'required|string|max:255',
                'bankomat_model' => 'required|numeric',
                'bankomat_status' => 'required|numeric',
                'bankomat_lokacija' => 'required|numeric',
            ]);
        //dd($this->modelData(), $this->bankomat_lokacija, $this->bankomat_status);
        // Save logic here, e.g., create or update the location in the database
        DB::transaction(function(){
            //Bankomat
            $cuurent = Bankomat::create($this->modelData());
            //BankomatLokacija
            BankomatLokacija::create(
                ['bankomat_id' => $cuurent['id'], 
                'blokacija_id' => $this->bankomat_lokacija,
                'bankomat_status_tip_id' => $this->bankomat_status,
                'user_id' => auth()->user()->id
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
                'bankomat_tid' => 'required|string|max:255',
                'bankomat_model' => 'required|numeric',
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
            'b_terminal_id' => $this->bankomat_tid,
            'bankomat_tip_id' => $this->bankomat_model,
        ];
    }

    public function loadModel()
    {
        $bankomat = Bankomat::where('id', '=', $this->modelId)->first();
        $this->b_sn = $bankomat->b_sn;
        $this->old_b_sn = $bankomat->b_sn;
        $this->bankomat_tid = $bankomat->b_terminal_id; 
        $this->bankomat_model = $bankomat->bankomat_tip_id;
    }

    public function statusShowModal($id, $status)
    {
        $this->resetInputFields();
        $this->modelId = $id; //ovo je id terminal_lokacijas tabele
        $this->bankomat_status = $status;
        //$this->selectedTerminal = SelectedTerminalInfo::selectedTerminalInfoTerminalId($this->modelId);
        //$this->resetValidation();
        //$this->reset();
        $this->modalStatusFormVisible = true;

    }

    public function statusUpdate()
    {
        BankomatLokacija::where('id', '=', $this->modelId)->update(['bankomat_status_tip_id' => $this->bankomat_status]);
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
        DB::transaction(function(){
            $cuurent = BankomatLokacija::where('id', '=', $this->modelId)->first();
            BankomatLocijaHirtory::create([
                'bankomat_lokacija_id' => $this->modelId,
                'bankomat_id' => $cuurent['bankomat_id'],
                'blokacija_id' => $cuurent['blokacija_id'],
                'bankomat_status_tip_id' => $cuurent['bankomat_status_tip_id'],
                'user_id' => $cuurent['user_id'],
            ]);              
            $cuurent->update(['blokacija_id' => $this->nova_lokacija, 'bankomat_status_tip_id' => $this->bankomat_status]);
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

    
    public function read()
    {
        $searchParams=[
            'b_sn' => $this->searchSB,
            'b_terminal_id' => $this->searchTerminalId,
            'bankomat_model' => $this->searchModel,
            'lokacija_naziv' => $this->searchLokacijaNaziv,
            'region' => $this->searchRegion,
            'tip' => $this->searchTip,
            'status' => $this->searchStatus,
            'pib' => $this->searchPib
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
