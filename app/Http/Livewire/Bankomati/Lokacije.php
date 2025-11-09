<?php

namespace App\Http\Livewire\Bankomati;

use Livewire\Component;
use Livewire\WithPagination;

use App\Actions\Bankomati\BankomatiLokacijeReadActions;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

use App\Models\BlokacijaKontaktOsoba;
use App\Models\PozicijaKategoriy;
use App\Models\BankomatLokacija;
use App\Models\BlokacijaTip;
use App\Models\Blokacija;
use App\Models\User;


class Lokacije extends Component
{
    use WithPagination;

    protected $listeners = ['newLocation', 'sortClick'];

    //OREDER BY
    public $orderBy = 'blokacijas.id';
    public $orderDirection = 'desc';

    //MODAL NEW EDIT
    public $modelId;
    public $is_duplicate;
    public $modalNewEditVisible;
    public $is_edit;
    public $bl_naziv;
    public $bl_naziv_sufix;
    public $bl_adresa;
    public $bl_mesto;
    public $blokacija_tip_id;
    public $bankomat_region_id;
    public $pib;
    public $mb;
    public $email;
    public $latitude;  
    public $longitude;
    public $blokacija_tip;

    public $old_pib;
    public $old_naziv;

    public $kontakt_name;
    public $kontakt_tel;
    public $kontakt_email;

    public $pib_count = false;
    public $email_is_set = false;

    //kontakt osoba
    public $kontaktOsobaVisible;
    public $kontaktOsobaInfo;
    public $odabranaLokacija;

    //kontakt osoba modal
    public $modalNewKontaktOsobaVisible = false;

    //lat log modal
    public $latLogVisible = false;

    //lokacija info modal
    public $modalLokacijaInfoVisible = false;
    public $deletePosible = false;
    public $usersOfLocation;
    public $lokacijaSadrzi;

    //nova edit podlokacija
    public $is_sublocation;

    //SEARCH
    public $searchName;
    public $searchMesto;
    public $searchRegion;
    public $searchTip;
    public $searchPib;

    //ROLES
    public $role_region;

    public function mount()
    {
        $this->role_region =auth()->user()->userBankmatPositionAndRegion();
         $this->resetInputFields();
        /* 
        9 	Admin bankomata
        10 	Šef servisa bankomata
        11 	Serviser bankomata 
        */
    }
    /*
    // Event lisener for new location
    */
    public function newLocation()
    {
        $this->addNewLocation();
    }

    public function sortClick($field)
    {
        if ($this->orderBy === $field) {
            $this->orderDirection = $this->orderDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->orderBy = $field;
            $this->orderDirection = 'desc';
            $this->emit('fieldChange', $field);
        }
        $this->emit('sortChange', $this->orderDirection);
    }

    public function rules()
    {
        return [
            'bl_naziv' => 'required|string|max:255',
            'bl_naziv_sufix' => 'nullable|string|max:125',
            'bl_adresa' => 'required|string|max:255',
            'bl_mesto' => 'nullable|string|max:255',
            'blokacija_tip_id' => 'required|integer|exists:blokacija_tips,id',
            'bankomat_region_id' => 'required|integer|exists:bankomat_regions,id',
            'pib' => ($this->is_edit || $this->is_sublocation) ? ['digits_between:8,16'] : ["required", 'digits_between:8,16', 'unique:blokacijas,pib'],
            'mb' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'latitude' => ['regex:/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/', 'nullable'],             
            'longitude' => ['regex:/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/', 'nullable'],
        ];
    }

    private function resetInputFields()
    {
        $this->modelId = null;
        $this->is_duplicate = false;
        $this->bl_naziv = '';
        $this->dl_naziv_sufix = '';
        $this->bl_adresa = '';
        $this->bl_mesto = '';
        $this->blokacija_tip_id = '';
        $this->blokacija_tip = '';
        $this->bankomat_region_id = ($this->role_region['role'] == 'admin') ? '' : $this->role_region['region'];
        $this->pib = '';
        $this->mb = '';
        $this->email = '';
        $this->latitude = '';  
        $this->longitude = '';

        $this->kontakt_name = '';
        $this->kontakt_tel = '';
        $this->kontakt_email = '';
        $this->pib_count = false;
        $this->is_sublocation = false;

         $this->old_pib = '';
        $this->old_naziv = '';
    }

    public function editLocation($id)
    {
        $this->resetValidation();
        $this->resetInputFields();
        $this->modelId = $id;
        //dd($id,  $this->modeId);
        $this->is_edit = true;
        $this->loadModel();
        $this->modalNewEditVisible = true;
    }

    public function addNewLocation()
    {
        $this->is_edit = false;
        $this->resetValidation();
        $this->resetInputFields();
        $this->modalNewEditVisible = true;
    }

    public function dodajPodlokaciju()
    {
        $this->modalNewEditVisible = true;
        $this->is_edit = false;
        $this->is_sublocation = true;
        $this->resetValidation();
        //reset some input fields
        $this->bl_adresa = '';
        $this->bl_mesto = '';
        //$this->mb = '';
        $this->email = '';
        $this->latitude = '';  
        $this->longitude = '';

        $this->kontakt_name = '';
        $this->kontakt_tel = '';
        $this->kontakt_email = '';

        $this->modalNewEditVisible = true;
    }

    /**
     * Validates and saves a new location to the database.
     *
     * @return void
     */
    public function saveNewLocation()
    {
        $this->validate($this->rules());
        if($this->kontakt_name){
             $this->validate(
                [
                    'kontakt_name' => 'required|string|min:3|max:255',
                    //'kontakt_tel' => 'nullable|phone:INTERNATIONAL',
                    //'kontakt_tel' => 'nullable|digits_between:7,11',
                    'kontakt_email' => 'nullable|email|max:255'
                ]
             );
        }
        //dd($this->modelData());
        // Save logic here, e.g., create or update the location in the database
        $new_location = Blokacija::create($this->modelData());
        if($this->kontakt_name)
        {
            $kontakt_osoba = new BlokacijaKontaktOsoba();
            $kontakt_osoba->ime = $this->kontakt_name;
            $kontakt_osoba->blokacija_id = $new_location->id;
            $kontakt_osoba->telefon = $this->kontakt_tel ?: null;
            $kontakt_osoba->email = $this->kontakt_email ?: null;
            
            $kontakt_osoba->save();
        }

        $this->modalNewEditVisible = false;
        $this->resetInputFields();
        // Optionally, emit an event to notify other components of the change
        $this->emit('locationSaved');
    }

/**
 * Validates and updates an existing location in the database.
 *
 * @return void
 */
    public function updateLocation()
    {
        $this->validate($this->rules());
        if($this->kontakt_name){
             $this->validate(
                [
                    'kontakt_name' => 'required|string|min:3|max:255',
                    //'kontakt_tel' => 'nullable|phone:INTERNATIONAL',
                    //'kontakt_tel' => 'nullable|digits_between:7,11',
                    'kontakt_email' => 'nullable|email|max:255'
                ]
             );
        }
        $location = Blokacija::find($this->modelId);
        $location->update($this->modelData());

        if($this->pib_count > 1 && !$this->is_duplicate){
            if($this->pib != $this->old_pib || $this->bl_naziv != $this->old_naziv){
                Blokacija::where('pib', $this->old_pib)->where('id', '<>', $this->modelId)->update(['bl_naziv' => $this->bl_naziv, 'pib' => $this->pib] );
            }
        }

        if($this->kontakt_name)
        {
            $kontakt_osoba = BlokacijaKontaktOsoba::where('blokacija_id', '=', $this->modelId)->first();
            if(!$kontakt_osoba) {
                $kontakt_osoba = new BlokacijaKontaktOsoba();
                $kontakt_osoba->blokacija_id = $this->modelId;
            }
            $kontakt_osoba->ime = $this->kontakt_name;
            $kontakt_osoba->telefon = $this->kontakt_tel ?: null;
            $kontakt_osoba->email = $this->kontakt_email ?: null;
            $kontakt_osoba->save();
        }
        $this->modalNewEditVisible = false;
    }

    private function loadModel()
    {
        $blokacija = Blokacija::where('id', '=', $this->modelId)->first();
        //dd($this->modelId, $blokacija);
        $this->is_duplicate = ($blokacija->is_duplicate == 1) ? true : false;
        $this->bl_naziv             = $blokacija->bl_naziv;
        $this->bl_naziv_sufix       = $blokacija->bl_naziv_sufix;
        $this->bl_adresa            = $blokacija->bl_adresa;
        $this->bl_mesto             = $blokacija->bl_mesto;
        $this->blokacija_tip_id     = $blokacija->blokacija_tip_id;
        $this->bankomat_region_id   = $blokacija->bankomat_region_id;
        $this->pib                  = $blokacija->pib;
        $this->mb                   = $blokacija->mb;
        $this->email                = $blokacija->email;
        $this->latitude             = $blokacija->latitude;
        $this->longitude            = $blokacija->longitude;

        $this->pib_count = Blokacija::where('pib', '=', $this->pib)->count();
        //$this->email_is_set = Blokacija::where('email', '=', $this->email)->where('id', '!=', $this->modelId)->exists();
        $this->blokacija_tip = BlokacijaTip::find($this->blokacija_tip_id)->bl_tip_naziv;

        if($this->pib_count > 1 && !$this->is_duplicate){
            $this->old_pib = $this->pib;
            $this->old_naziv = $this->bl_naziv;
        }

        $bkontak = BlokacijaKontaktOsoba::where('blokacija_id', '=', $this->modelId)->first();
        if($bkontak){
            $this->kontakt_name     = $bkontak->ime;
            $this->kontakt_tel      = $bkontak->telefon;
            $this->kontakt_email    = $bkontak->email;
        }
    }

    public function setFilterTip($id)
    {
        $this->searchTip = $id ?? null; //
    }

    private function modelData()
    {
        return [
            'is_duplicate' => ($this->is_duplicate || $this->is_sublocation) ? 1 : 0,
            'bl_naziv' => $this->bl_naziv,
            'bl_naziv_sufix' =>  $this->bl_naziv_sufix ?: null,
            'bl_adresa' => $this->bl_adresa,
            'bl_mesto' => $this->bl_mesto ?: null,
            'blokacija_tip_id' => $this->blokacija_tip_id,
            'bankomat_region_id' => $this->bankomat_region_id,
            'pib' => $this->pib,
            'mb' => $this->mb ?: null,
            'email' => $this->email ?: null,
            'latitude' => $this->latitude ?: null,  
            'longitude' => $this->longitude ?: null
        ];
    }

    /**
     * Prikazuje Kontakt osoba Modal
     *
     * @param  mixed $id
     * @return void
     */
    public function showKontaktOsobaModal($id)
    {
        $this->resetValidation();
        $this->modelId = $id;
        $this->kontaktOsobaVisible = true;
    }

    public function showLatLogModal($id)
    {
        $this->resetValidation();
        $this->modelId = $id;
        $this->odabranaLokacija = Blokacija::where('id', '=', $this->modelId)->first();//LokacijaInfo::getInfo($this->modelId);
        //dd($this->odabranaLokacija);
        $this->latLogValue = Str::of($this->odabranaLokacija->latitude . ', ' . $this->odabranaLokacija->longitude);
        $this->latLogValue = ($this->latLogValue == ', ') ? '' : $this->latLogValue;
        $this->latLogVisible = true;
    }

     public function addOrUpdateLatLog()
    {
        $this->latLogValue = preg_replace('/\s+/', '', $this->latLogValue);
        $exp = Str::of($this->latLogValue)->explode(delimiter: ',');
        if(count($exp) < 2 || count($exp) > 2){
            $this->addError('latLogValue', 'Unesite ispravne koordinate u formatu: "latitude, longitude"');
            return;
        }
        // Check if the latitude and longitude values are numeric
        if(!is_numeric($exp[0]) || !is_numeric($exp[1])){
            $this->addError('latLogValue', 'Unesite ispravne koordinate u formatu: "latitude, longitude"');
            return;
        }
        $this->lat_value = $exp[0];
        $this->long_value = $exp[1];
        
        // Validate the latitude and longitude values
        $this->validate([
            'lat_value' => ['regex:/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/', 'nullable'],
            'long_value' => ['regex:/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/', 'nullable'],
        ]);
        
        Blokacija::find($this->modelId)->update(['latitude' => $exp[0], 'longitude' => $exp[1]]);
        $this->latLogVisible = false;
    }

     public function removeLatLog()
    {
        Blokacija::find($this->modelId)->update(['latitude' => NULL, 'longitude' => NULL]);
        $this->latLogVisible = false;
    }   

    public function infoShowModal($id)
    {
        $this->lokacijaSadrzi=[];
        $this->deletePosible = true;
        $this->modelId = $id; // id blokacije
        
        //korisnici koji su povezani sa lokacijom
        $pozicijeIds = PozicijaKategoriy::find(4)->pozicije->pluck('id');
        $usersOfLocation = User::where('lokacijaId', '=', $this->modelId)->whereIn('pozicija_tipId', $pozicijeIds)->orderBy('name')->pluck('name')->toArray();
        
        if($usersOfLocation){
            $this->deletePosible = false;
            //array_push($this->lokacijaSadrzi, ['korisnici'=>$usersOfLocation]);
            $this->lokacijaSadrzi['korisnici'] = $usersOfLocation;
        }

        //bankomati koji su povezani sa lokacijom
        $bankomatiOfLocation = BankomatLokacija::where('blokacija_id', '=', $this->modelId)->pluck('id')->toArray();
        if(count($bankomatiOfLocation) > 0){
            $this->deletePosible = false;
            //array_push($this->lokacijaSadrzi, ['bankomati'=>$bankomatiOfLocation]);
            $this->lokacijaSadrzi['bankomati'] = [count($bankomatiOfLocation)];
        }

        //dd($this->lokacijaSadrzi);
        $this->odabranaLokacija = Blokacija::where('id', '=', $this->modelId)->first();
        $this->lokacijaTerminals = BankomatLokacija::where('blokacija_id', '=', $this->modelId)->pluck('id')->toArray();
        $this->modalLokacijaInfoVisible = true;
    }

    public function deleteLocation()
    {
        if(! $this->deletePosible) return; 
        $this->odabranaLokacija = Blokacija::where('id', '=', $this->modelId)->first();
        $this->odabranaLokacija->delete();
        $this->modalLokacijaInfoVisible = false;
    }
    
    /**
     * Read all Bankomati lokacije.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator  
     */
    public function read()
    {
        $searchParams=[
            'naziv' => $this->searchName,
            'adresa' => $this->searchMesto,
            'region' => ($this->role_region['role'] == 'admin') ? $this->searchRegion : $this->role_region['region'],
            'tip_lokacije' => $this->searchTip,
            'pib' => $this->searchPib
        ];
        $builder = BankomatiLokacijeReadActions::blokacijeRead($searchParams, $this->orderBy, $this->orderDirection);
        // paginate the builder
        $perPage = Config::get('global.terminal_paginate');
        $terms = $builder->paginate($perPage, ['*'], 'lokacije');
        return $terms;}

    public function render()
    {
        return view('livewire.bankomati.lokacije', [
            'data' => $this->read(),
        ]);
    }
}
