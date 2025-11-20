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
            //dd($this->serviseri);
        }
        $this->searchStatus = 'Aktivan';
    }

    public function read()
    {
        $search = [
            'searchProductTip' => $this->searchProductTip,
            'searchStatus' => $this->searchStatus,
            'searchLokacijaNaziv' => $this->searchLokacijaNaziv,
            'searchMesto' => $this->searchMesto,
            'searchRegion' => $this->searchRegion,
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
            'searchUsersRegion' => $this->searchRegion
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
