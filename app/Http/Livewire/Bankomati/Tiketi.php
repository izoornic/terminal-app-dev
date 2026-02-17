<?php

namespace App\Http\Livewire\Bankomati;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Config;
use App\Actions\Bankomati\BankomatTiketReadActions;

use App\Models\User;

class Tiketi extends Component
{
    use WithPagination;

    public $searchProductTip;
    public $searchStatus;
    public $searchLokacijaNaziv;
    public $searchMesto;
    public $searchRegion;
    public $searchTid;
    public $searchDatumPocetak;
    public $searchDatumKraj;
    public $searchComments;

    public $filter_clear_buttons = [
        'searchProductTip' => 'Tip proizvoda',
        'searchStatus' => 'Status tiketa',
        'searchLokacijaNaziv' => 'Naziv lokacije',
        'searchMesto' => 'Mesto',
        'searchRegion' => 'Region',
        'searchTid' => 'ID proizvoda',
        'searchDatumPocetak' => 'Datum početak',
        'searchDatumKraj' => 'Datum kraj',
        'searchComments' => 'Komentari'
    ];
    public $filter_displayed_buttons = [];
    public $searchVanRegionaProductTip;
    public $searchVanRegionaStatus;
    public $searchVanRegionaLokacijaNaziv;
    public $searchVanRegionaMesto;

    //promenjiva koja prikazuje tikete za servisera van njegovog regiona
    public $serviseri;
    
    public $role_region;

    public $newTicketShowModal = false;
    public $bankomat_lokacija_id;
    
    /**
     * Listeners for Livewire events
     *
     * @var array
     */
    protected $listeners = ['newTicket', 'izabraniProizvod', 'newTicketCreated'];

    public function newTicket()
    {
        $this->bankomat_lokacija_id = null;
        $this->newTicketShowModal = true;
    }

    public function izabraniProizvod($id)
    {
        $this->bankomat_lokacija_id = $id;
        //dd($this->bankomat_lokacija_id);
    }

    public function newTicketCreated($id)
    {
        $this->newTicketShowModal = false;
        $this->emit('flashMessage', 'Tiket #'.$id.' je uspešno dodat.');
    }

    public function mount()
    {
        $this->role_region =auth()->user()->userBankmatPositionAndRegion();
        if($this->role_region['role'] != 'admin') {
            $this->searchRegion = $this->role_region['region'];
            if($this->role_region['role'] == 'sef'){
                //pokupi idjeve svih u servisu
                $this->serviseri = User::select('users.id')
                                        ->join('blokacijas', 'blokacijas.id', '=', 'users.lokacijaId')
                                        ->join('bankomat_regions', 'bankomat_regions.id', '=', 'blokacijas.bankomat_region_id')
                                        ->whereIn('pozicija_tipId', [10, 11])
                                        ->where('bankomat_region_id', $this->role_region['region'])  
                                        ->pluck('id');
            }else{
                $this->serviseri = [auth()->user()->id];
            }

        }
        
        $this->searchProductTip = session()->get('searchProductTip') ?? null;
        $this->searchStatus = session()->get('searchStatus') ?? 'Aktivan';
        $this->searchLokacijaNaziv = session()->get('searchLokacijaNaziv') ?? null;
        $this->searchMesto = session()->get('searchMesto') ?? null;
        $this->searchTid = session()->get('searchTid') ?? null;
        $this->searchDatumPocetak = session()->get('searchDatumPocetak') ?? null;
        $this->searchDatumKraj = session()->get('searchDatumKraj') ?? null;
        $this->searchComments = session()->get('searchComments') ?? null;

        if($this->role_region['role'] != 'admin') {
            $this->searchRegion = $this->role_region['region'];
        }else{
            $this->searchRegion = session()->get('searchRegion') ?? null;
        }

        $this->showFilterClearButtons();
    }

    public function updated($propertyName)
    {
        session()->put('searchProductTip', $this->searchProductTip);
        session()->put('searchStatus', $this->searchStatus);
        session()->put('searchLokacijaNaziv', $this->searchLokacijaNaziv);
        session()->put('searchMesto', $this->searchMesto);
        //session()->put('searchRegion', $this->searchRegion);
        session()->put('searchTid', $this->searchTid);
        session()->put('searchDatumPocetak', $this->searchDatumPocetak);
        session()->put('searchDatumKraj', $this->searchDatumKraj);
        session()->put('searchComments', $this->searchComments);

         if($this->role_region['role'] == 'admin') {
            session()->put('searchRegion', $this->searchRegion);
         }

        $this->showFilterClearButtons();
    }

    public function showFilterClearButtons(){
        foreach ($this->filter_clear_buttons as $key => $value) {
            //dd(session()->get('$key'));
            if(session()->get($key) != null){
                $this->filter_displayed_buttons[$key] = $value;
                if($key == 'searchStatus' && $this->searchStatus == 'Aktivan'){
                    unset($this->filter_displayed_buttons[$key]);
                }
            }else{
                unset($this->filter_displayed_buttons[$key]);
            }
        }
    }

    public function clearFilter($key){
        session()->put($key, null);
        //clear public vars
        switch ($key) {
            case 'searchProductTip':
                $this->searchProductTip = null;
                break;
            case 'searchStatus':
                $this->searchStatus = 'Aktivan';
                break;
            case 'searchLokacijaNaziv':
                $this->searchLokacijaNaziv = null;
                break;
            case 'searchMesto':
                $this->searchMesto = null;
                break;
            case 'searchRegion':
                $this->searchRegion = null;
                break;
            case 'searchTid':
                $this->searchTid = null;
                break;
            case 'searchDatumPocetak':
                $this->searchDatumPocetak = null;
                break;
            case 'searchDatumKraj':
                $this->searchDatumKraj = null;
                break;
            case 'searchComments':
                $this->searchComments = null;
        }
        $this->showFilterClearButtons();
    }

    public function read()
    {
        $search = [
            'searchProductTip' => $this->searchProductTip,
            'searchStatus' => $this->searchStatus,
            'searchLokacijaNaziv' => $this->searchLokacijaNaziv,
            'searchMesto' => $this->searchMesto,
            'searchRegion' => $this->searchRegion,
            'searchTid' => $this->searchTid,
            'searchDatumPocetak' => $this->searchDatumPocetak,
            'searchDatumKraj' => $this->searchDatumKraj,
            'searchComments' => $this->searchComments
        ];
        $builder = BankomatTiketReadActions::read($search);
        // paginate the builder
        $perPage = Config::get('global.terminal_paginate');
        $terms = $builder->paginate($perPage, ['*'], 'tiketi'); 
        return $terms;
    }
 
    public function readVanRegiona()
    {
        if($this->role_region['role'] == 'admin') return [];
        
        $search = [
            'searchProductTip' => $this->searchVanRegionaProductTip,
            'searchStatus' => $this->searchVanRegionaStatus,
            'searchLokacijaNaziv' => $this->searchVanRegionaLokacijaNaziv,
            'searchMesto' => $this->searchVanRegionaMesto,
            'serviseri' => $this->serviseri,
            'searchUsersRegion' => $this->searchRegion,
            'searchTid' => $this->searchTid,
            'searchDatumPocetak' => $this->searchDatumPocetak,
            'searchDatumKraj' => $this->searchDatumKraj,
            'searchComments' => $this->searchComments
        ];
        $builder = BankomatTiketReadActions::read($search);
        // paginate the builder
        $perPage = Config::get('global.terminal_paginate');
        $terms = $builder->paginate($perPage, ['*'], 'userVanRegiona');
        
        return $terms;
    }
    public function render()
    {
        return view('livewire.bankomati.tiketi', [
            'data' => $this->read(),
            'data_van_regiona' => $this->readVanRegiona(),
        ]);
    }
}
