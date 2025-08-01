<?php

namespace App\Http\Livewire;

use App\Models\User;
use App\Models\Tiket;
use App\Models\Lokacija;
use App\Models\LicencaServisna;
use App\Models\TerminalLokacija;
use App\Models\TiketPrioritetTip;
use App\Models\TiketOpisKvaraTip;
use App\Models\LicenceZaTerminal;
use App\Models\LicencaNaplata;
use App\Models\LicencaDistributerCena;
use App\Models\LicencaParametarTerminal;
use App\Models\TerminalLokacijaHistory;
use App\Models\DistributerLokacijaIndex;
use App\Models\TiketAkcijaKorisnikPozicija;

use Livewire\Component;
use Livewire\WithPagination;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

use App\Http\Helpers;

use App\Ivan\CryptoSign;
use App\Ivan\TerminalHistory;
use App\Ivan\SelectedTerminalInfo;
use App\Ivan\MailToUser;

class Terminal extends Component
{
    use WithPagination;
    
    public $modalFormVisible;
    public $modalConfirmPremestiVisible;
    public $modelId;

    //promeni statys modal
    public $modalStatus;

    //premesti modal
    public $plokacijaTip;
    public $plokacija;
    public $searchPLokacijaNaziv;
    public $searchPlokacijaMesto;
    public $searchPlokacijaRegion;
    public $canMoveTerminal = 0;
    public $selectedTerminal;
    public $modalStatusPremesti;
    public $datum_premestanja_terminala;

    //Error licenca MODAL
    public $licencaError;
    public $modalErorLicencaVisible;

    //select all
    public $selectedTerminals = [];
    public $selectAll;
    public $allInPage = [];

    //search
    public $searchSB;
    public $searchKutija;
    public $searchName;
    public $searchRegion;
    public $searchTip;
    public $searchStatus;

    //multi selected
    public $multiSelected;
    public $multiSelectedInfo;

    //terminal HISTORY
    public $terminalHistoryVisible;
    public $historyData;

    //licence modal
    public $licencaModalVisible;
    public $licencaData;

    //add Tiket Modal
    public $newTiketVisible;
    public $userPozicija;
    public $prioritetTiketa;
    public $newTerminalInfo;
    public $newTiketTerminalLokacijaId;
    public $prioritetInfo;
    public $opisKvaraList;
    public $tiketStatusId;
    public $opisKvataTxt;
    public $zatvorioId;
    

    //search korisnika kome se dodeljuje tiket
    public $searchUserName;
    public $searchUserLokacija;
    public $searchUserPozicija;
    
    public $dodeljenUserId;
    public $dodeljenUserInfo;

    public $tiketAkcija;
    public $userRegion;

    private $mailToUser;

    //Servisne licence
    public $novaServisnaModalVisible;
    public $licence_za_dodavanje;
    public $distId;
    public $licence_dodate_terminalu;
    public $datum_pocetka_licence;
    public $datum_kraja_licence;
    public $datum_prekoracenja;
    public $parametri;

    //servicne koje gaze trajnu isteklu
    public $nazivZaServisnu;
    public $terminal_dist_id;
    public $terminal_dist_cena_id;

    //Pomeranje prekoracenja za privremenu licencu
    public $pomeriPrekoracenjeModalVisible;
    public $licencaZaProduzetak;
    public $selectedTerminalSn;

    //komentari
    public $modalKomentariVisible;
    public $selectedTerminalComments;
    public $selectedTerminalCommentsCount;
    public $newKoment;

    public function newTiketShowModal($tid)
    {
        $this->zatvorioId = 0;
        $this->opisKvataTxt = '';
        $this->tiketStatusId = 2;
        $this->opisKvaraList = '';
        $this->searchUserName = '';
        $this->searchUserLokacija ='';
        $this->searchUserPozicija ='';

        $this->dodeljenUserInfo = null;
        $this->dodeljenUserId = null;

        $this->prioritetTiketa = 4;
        $this->newTiketTerminalLokacijaId = TerminalLokacija::select('id')->where('terminalId', '=', $tid)->first()->id;
        $this->modelId = $tid;
        $this->newTerminalInfo = $this->selectedTerminalTiketInfo();
        $this->prioritetInfo = $this->prioritetInfo();
        $this->newTiketVisible = true;
    }
     /**
     * createCallCentar
     *
     * @param  mixed $dodela
     * @return void
     */
    public function createCallCentar($dodela)
    {
        $this->validate();
        $this->dodeljenUserId = ($dodela) ? $this->sefServisa()->id : null;
        $this->tiketStatusId = ($dodela) ? 2 : 1;
        $this->createTiket();
    }

    public function createCallCentarClosedTiket()
    {
        $this->validate();
        $this->tiketStatusId = 3;
        $this->dodeljenUserId = null;
        $this->zatvorioId = auth()->user()->id;
        $this->createTiket(); 
    }
    /**
     * The create novi tiket function.
     *
     * @return void
     */
    public function createTiket()
    {
        $this->validate();
        //dd($this->sefServisa());
        $tik = Tiket::create($this->modelTiketData());
        
        //send email
        $this->mailToUser = new MailToUser($tik->id);
        $this->mailToUser->sendEmails('novi');

        $this->newTiketVisible = false;
    }

    /**
     * The data for the model mapped
     * in this component.
     *
     * @return void
     */
    public function modelTiketData()
    {
        return [ 
            'tremina_lokacijalId'   =>  $this->newTiketTerminalLokacijaId,
            'tiket_statusId'        =>  $this->tiketStatusId,
            'opis_kvaraId'          =>  $this->opisKvaraList,
            'korisnik_prijavaId'    =>  auth()->user()->id,
            'korisnik_dodeljenId'   =>  $this->dodeljenUserId,
            'opis'                  =>  $this->opisKvataTxt,
            'tiket_prioritetId'     =>  $this->prioritetTiketa,
            'br_komentara'          =>  0,
            'korisnik_zatvorio_id'  =>  $this->zatvorioId

        ];
    }

     /**
     * id Sefa Servisa
     *
     * @return object
     */
    private function sefServisa()
    {
        return User::select('users.id', 'users.name', 'users.tel', 'users.email')
            ->join('lokacijas', 'lokacijas.id', '=', 'users.lokacijaId')
            ->join('regions', 'regions.id', '=', 'lokacijas.regionId')
            ->where('users.pozicija_tipId', '=', 3)
            ->where('regions.id', '=',SelectedTerminalInfo::selectedTerminalInfoTerminalId($this->modelId)->rid)
            ->first();
    }

    /**
     * selectedUserInfo
     *
     * @return void
     */
    private function selectedUserInfo($user_id)
    {
        return User::select('users.id', 'users.name', 'users.email', 'lokacijas.l_naziv', 'lokacijas.mesto', 'pozicija_tips.naziv')
                    ->leftJoin('lokacijas', 'users.lokacijaId', '=', 'lokacijas.id')
                    ->leftJoin('pozicija_tips', 'users.pozicija_tipId', '=', 'pozicija_tips.id')
                    ->where('users.id', '=', $user_id)
                    ->first();
    }

    /**
     * [Description for selectedTerminalTiketInfo]
     *
     * @return [type]
     * 
     */
    private function selectedTerminalTiketInfo(){
        return TerminalLokacija::select('terminal_lokacijas.*', 'terminals.sn', 'terminals.terminal_tipId as tid', 'terminal_status_tips.ts_naziv', 'lokacijas.l_naziv', 'lokacijas.mesto', 'lokacija_kontakt_osobas.name', 'lokacija_kontakt_osobas.tel', 'regions.r_naziv', 'regions.id as rid', 'licenca_distributer_tips.distributer_naziv')
                    ->leftJoin('terminals', 'terminal_lokacijas.terminalId', '=', 'terminals.id')
                    ->leftJoin('terminal_status_tips', 'terminal_lokacijas.terminal_statusId', '=', 'terminal_status_tips.id')
                    ->leftJoin('lokacijas', 'terminal_lokacijas.lokacijaId', '=', 'lokacijas.id')
                    ->leftJoin('lokacija_kontakt_osobas', 'lokacijas.id', '=', 'lokacija_kontakt_osobas.lokacijaId')
                    ->leftJoin('regions', 'lokacijas.regionId', '=', 'regions.id')
                    ->leftJoin('licenca_distributer_tips', 'terminal_lokacijas.distributerId', '=', 'licenca_distributer_tips.id')
                    ->where('terminal_lokacijas.id', $this->newTiketTerminalLokacijaId)
                    -> first();
    }

     /**
     * Pronadji korisnika kome dodeljujes tiket
     *
     * @return void
     */
    public function searchUser()
    {
        return User::select('users.id', 'users.name', 'lokacijas.l_naziv', 'pozicija_tips.naziv')
                    ->leftJoin('lokacijas', 'users.lokacijaId', '=', 'lokacijas.id')
                    ->leftJoin('pozicija_tips', 'users.pozicija_tipId', '=', 'pozicija_tips.id')
                    ->leftJoin('regions', 'regions.id', '=', 'lokacijas.regionId')
                    ->where('name', 'like', '%'.$this->searchUserName.'%')
                    ->where('l_naziv', 'like', '%'.$this->searchUserLokacija.'%')
                    ->where('naziv', 'like', '%'.$this->searchUserPozicija.'%')
                    ->when($this->tiketAkcija[1] == "region", function ($rtval){
                        return $rtval->where('regions.id', '=', $this->userRegion);
                    })
                    ->paginate(Config::get('global.modal_search'), ['*'], 'usersp');
    }    

     /**
     * prioritetInfo
     *
     * @return void
     */
    private function prioritetInfo()
    {
        return TiketPrioritetTip::where('id', '=', $this->prioritetTiketa)->first();
    }

    /**
     * Put your custom public properties here!
     */
    public function mount()
    {
        $this->userPozicija = auth()->user()->pozicija_tipId;

        $akcija = TiketAkcijaKorisnikPozicija::select('tiket_akcija_tips.id as akcijaid', 'tiket_akcija_tips.tiket_akcija', 'tiket_akcija_vrednost_tips.id as vrednostId', 'tiket_akcija_vrednost_tips.akcija_vrednost_opis')
        ->leftJoin('tiket_akcija_tips', 'tiket_akcija_tips.id', '=', 'tiket_akcija_korisnik_pozicijas.tiket_akcijaId')
        ->leftJoin('tiket_akcija_vrednost_tips', 'tiket_akcija_vrednost_tips.id', '=', 'tiket_akcija_korisnik_pozicijas.tiket_akcijavrednostId')
        ->where('tiket_akcija_korisnik_pozicijas.korisnik_pozicijaId', '=', auth()->user()->pozicija_tipId)
        ->get();
        foreach ($akcija as $value){
            $this->tiketAkcija[$value->akcijaid] = $value->akcija_vrednost_opis;
        }

        $region = Lokacija::select('regions.id as rid')
                                ->leftJoin('regions', 'regions.id', '=', 'lokacijas.regionId')
                                ->where('lokacijas.id', '=', auth()->user()->lokacijaId)
                                ->first();
        $this->userRegion = $region->rid;
    }
    /**
     * The validation rules
     *
     * @return void
     */
    public function rules()
    {
        if($this->newTiketVisible){
            return [
                'opisKvaraList' => 'required' 
            ];
        }else{
            return [

            ];
        }
        
    }

    /**
     * The read function.
     *
     * @return void
     */
    public function read()
    {
        $this->allInPage = [];

        $terms =  TerminalLokacija::select(
                        'lokacijas.*', 
                        'terminals.id as tid', 
                        'terminals.sn', 
                        'terminals.broj_kutije', 
                        'terminal_tips.model', 
                        'lokacija_tips.lt_naziv', 
                        'regions.r_naziv', 
                        'terminal_status_tips.ts_naziv', 
                        'terminal_status_tips.id as statusid', 
                        'terminal_lokacijas.id as tlid', 
                        'terminal_lokacijas.blacklist', 
                        'terminal_lokacijas.distributerId',
                        'terminal_lokacijas.br_komentara')
        ->leftJoin('lokacijas', 'terminal_lokacijas.lokacijaId', '=', 'lokacijas.id')
        ->leftJoin('terminals', 'terminal_lokacijas.terminalId', '=', 'terminals.id')
        ->leftJoin('terminal_tips', 'terminals.terminal_tipId', '=', 'terminal_tips.id')
        ->leftJoin('lokacija_tips', 'lokacijas.lokacija_tipId', '=', 'lokacija_tips.id')
        ->leftJoin('regions', 'regions.id', '=', 'lokacijas.regionId')
        ->leftJoin('terminal_status_tips','terminal_lokacijas.terminal_statusId', '=', 'terminal_status_tips.id')
        ->where('terminals.sn', 'like', '%'.$this->searchSB.'%')
        ->where('terminals.broj_kutije', 'like', '%'.$this->searchKutija.'%')
        ->where('lokacijas.l_naziv', 'like', '%'.$this->searchName.'%')
        ->where('lokacijas.regionId', ($this->searchRegion > 0) ? '=' : '<>', $this->searchRegion)
        ->where('lokacijas.lokacija_tipId', ($this->searchTip > 0) ? '=' : '<>', $this->searchTip)
        ->where('terminal_status_tips.id', ($this->searchStatus > 0) ? '=' : '<>', $this->searchStatus)
        ->paginate(Config::get('global.terminal_paginate'), ['*'], 'terminali');
        
        $terms->each(function ($item, $key){
           // da li terminal ima licencu i da li je regularna ili servisna
            $licenca = LicenceZaTerminal::where('terminal_lokacijaId', '=', $item->tlid)->first();
            $item->tzlid = ($licenca) ?  $licenca->licenca_poreklo : 0;
            
            //ovo sluzi za check ALL box
            array_push($this->allInPage,  $item->tid);
        });
        
        return $terms;
    }


    /**
     * Shows STATUS update modal
     *
     * @param  mixed $id
     * @return void
     */
    public function statusShowModal($id, $status)
    {
        $this->multiSelected = false;
        $this->modelId = $id;
        $this->modalStatus = $status;
        $this->selectedTerminal = SelectedTerminalInfo::selectedTerminalInfoTerminalId($this->modelId);
        //$this->resetValidation();
        //$this->reset();
        $this->modalFormVisible = true;
    }

    /**
     * SHows STATUS update Selected terminals
     *
     * @return void
     */
    public function statusSelectedShowModal()
    {
        $this->multiSelected = true;
        $this->multiSelectedInfo = $this->multiSelectedTInfo();
        $this->modalFormVisible = true;
    }

    private function multiSelectedTInfo()
    {
        return TerminalLokacija::whereIn('terminalId', $this->selectedTerminals )
        ->leftJoin('terminals', 'terminal_lokacijas.terminalId', '=', 'terminals.id')
        ->leftJoin('terminal_status_tips', 'terminal_lokacijas.terminal_statusId', '=', 'terminal_status_tips.id')
        ->leftJoin('lokacijas', 'terminal_lokacijas.lokacijaId', '=', 'lokacijas.id')
        ->orderBy('lokacijaId')
        ->get();
    }

     /**
     * Status update function
     *
     * @return void
     */
    public function statusUpdate()
    {

        if(!$this->multiSelected) $this->selectedTerminals[0] = $this->modelId;
        
        foreach($this->selectedTerminals as $item){
            DB::transaction(function()use($item){
                //terminal
                $cuurent = TerminalLokacija::where('terminalId', $item) -> first();
                //insert to history table
                TerminalLokacijaHistory::create(['terminal_lokacijaId' => $cuurent['id'], 'terminalId' => $cuurent['terminalId'], 'lokacijaId' => $cuurent['lokacijaId'], 'terminal_statusId' => $cuurent['terminal_statusId'], 'korisnikId' => $cuurent['korisnikId'], 'korisnikIme' => $cuurent['korisnikIme'], 'created_at' => $cuurent['created_at'], 'updated_at' => $cuurent['updated_at'], 'blacklist' => $cuurent['blacklist'], 'distributerId' => $cuurent['distributerId']]);
                //update current
                TerminalLokacija::where('terminalId', $item)->update(['terminal_statusId'=> $this->modalStatus, 'korisnikId'=>auth()->user()->id, 'korisnikIme'=>auth()->user()->name ]);
            });
        }

        $this->selectedTerminals=[];
        $this->modalFormVisible = false;
    }
    
    /**
     * Shows the premesti modal.
     *
     * @param  mixed $id
     * @return void
     */
    public function premestiShowModal($id, $status)
    {
        $this->multiSelected = false;
        $this->modelId = $id;
        $this->plokacijaTip = 0;
        $this->plokacija = 0;
        $this->canMoveTerminal = 0;
        $this->modalStatusPremesti = $status;

        $this->searchPLokacijaNaziv ='';
        $this->searchPlokacijaMesto ='';
        $this->searchPlokacijaRegion = 0;
        $this->licencaError = '';
        //podaci o terminalu koji se premesta
        $this->selectedTerminal = SelectedTerminalInfo::selectedTerminalInfoTerminalId($this->modelId);
        //dd($this->selectedTerminal);
        $this->modalConfirmPremestiVisible = true;

        $this->datum_premestanja_terminala = Helpers::datumKalendarNow();
    }    

    public function premestiSelectedShowModal(){
        
        $this->multiSelected = true;
        $this->multiSelectedInfo = $this->multiSelectedTInfo();

        //status na listi se setuje prema prvom izabranom terminalu
        $this->modalStatusPremesti = TerminalLokacija::where('terminalId', $this->selectedTerminals[0])->first()->terminal_statusId;
        //dd($this->modalStatusPremesti);

        $this->plokacijaTip = 0;
        $this->plokacija = 0;
        $this->canMoveTerminal = 0;

        $this->searchPLokacijaNaziv ='';
        $this->searchPlokacijaMesto ='';
        $this->searchPlokacijaRegion = 0;
        $this->licencaError = '';

        $this->modalConfirmPremestiVisible = true;

        $this->datum_premestanja_terminala = Helpers::datumKalendarNow();
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
        return Lokacija::select('lokacijas.*', 'regions.r_naziv')
            ->where('lokacija_tipId', '=', $tipId)
            ->leftJoin('regions', 'lokacijas.regionId', '=', 'regions.id')
            ->where('l_naziv', 'like', '%'.$this->searchPLokacijaNaziv.'%')
            ->where('mesto', 'like', '%'.$this->searchPlokacijaMesto.'%')
            ->where('lokacijas.regionId', ($this->searchPlokacijaRegion > 0) ? '=' : '<>', $this->searchPlokacijaRegion)
            ->paginate(Config::get('global.modal_search'), ['*'], 'loc');
    }
    
    /**
     * Prikazuje naziv lokacije na koju se premesta terminal
     *
     * @return void
     */
    public function novaLokacija()
    {
        return Lokacija::select('lokacijas.*', 'regions.r_naziv')
                            ->leftJoin('lokacija_tips', 'lokacijas.lokacija_tipId', '=', 'lokacija_tips.id')
                            ->leftJoin('regions', 'lokacijas.regionId', '=', 'regions.id')
                            ->where('lokacijas.id', '=', $this->plokacija)
                            ->first();
    }
        
    /**
     * Premesta terminal na novu lokaciju
     *
     * @return void
     */
    public function moveTerminal(){

        if(!(bool)strtotime($this->datum_premestanja_terminala)) $this->datum_premestanja_terminala = Helpers::datumKalendarNow();
        $this->datum_premestanja_terminala.= ' '.Helpers::vremeKalendarNow();

        //da li se terminal dodaje Distributeru?
        $distributer_tip_id = ($this->plokacijaTip == 4) ? DistributerLokacijaIndex::where('lokacijaId', '=', $this->plokacija)->first()->licenca_distributer_tipsId : null;

        if(!$this->multiSelected) $this->selectedTerminals[0] = $this->modelId;

        //Da li terminal ima aktivnu licencu  Brisu se servisne licence ako ih ima
        foreach($this->selectedTerminals as $item){
            if(SelectedTerminalInfo::terminalImaLicencu($item)){
                $this->licencaError = 'multi';
                $this->modalErorLicencaVisible = true;
                $this->selectedTerminals=[];
                $this->modalConfirmPremestiVisible = false;
                return;
            }
        }
        
        //PREMESTI TERMINALE
        $terminali_premesteni = TerminalLokacija::premestiTerminale($this->selectedTerminals, $this->plokacija, $this->datum_premestanja_terminala, $this->modalStatusPremesti, $distributer_tip_id);
       
        if(!$terminali_premesteni){
            $this->licencaError = 'db';
            $this->modalErorLicencaVisible = true;
            $this->selectedTerminals=[];
            $this->modalConfirmPremestiVisible = false;
            return;
        }


        $this->selectedTerminals=[];
        $this->modalConfirmPremestiVisible = false;
    }

    /**
     * updated
     *
     * @param  mixed $key
     * @param  mixed $value
     * @return void
     */
    public function updated($key, $value)
    {
        $exp = Str::of($key)->explode(delimiter: '.');
        if($exp[0] === 'selectAll' && is_numeric($value)){
           foreach($this->allInPage as $termid){
               if(!in_array($termid, $this->selectedTerminals)){
                array_push($this->selectedTerminals, $termid);
               }  
           }
        }elseif($exp[0] === 'selectAll' && empty($value)){
            $this->selectedTerminals = array_diff($this->selectedTerminals, $this->allInPage);
        }

        if($this->modalConfirmPremestiVisible || $this->modalFormVisible){
            $this->selectedTerminal = SelectedTerminalInfo::selectedTerminalInfoTerminalId($this->modelId);
        }

        if($this->multiSelected && ($this->modalFormVisible || $this->modalConfirmPremestiVisible)){
            $this->multiSelectedInfo = $this->multiSelectedTInfo();
        }

        if($this->newTiketVisible){
            $this->newTerminalInfo = $this->selectedTerminalTiketInfo();
            $this->prioritetInfo = $this->prioritetInfo();

            if($this->dodeljenUserId){
                $this->dodeljenUserInfo = $this->selectedUserInfo($this->dodeljenUserId);
            }
        }
    }

        
    /**
     * History MODAL
     *
     * @return void
     */
    public function terminalHistoryShowModal($id)
    {
        $this->historyData = null;
        $this->modelId = $id; //ovo je id terminal lokacija tabele
        $this->selectedTerminal = SelectedTerminalInfo::selectedTerminalInfoTerminalLokacijaId($this->modelId);
        $this->historyData = TerminalHistory::terminalHistoryData($this->modelId);
        //dd($this->historyData);

        $this->terminalHistoryVisible = true;
    }

    /**
     * Prikaz aktivnih licenci
     *
     * @param mixed $tlid
     * 
     * @return [type]
     * 
     */
    public function licencaShowModal($id)
    {
        //dd($tpl);        
        $this->modelId = $id; //ovo je id terminal lokacija tabele
        $this->selectedTerminal = SelectedTerminalInfo::selectedTerminalInfoTerminalLokacijaId($this->modelId);
        $this->licencaData = LicenceZaTerminal::sveAktivneLicenceTerminala($id);
        //dd($this->licencaData);
        $this->licencaModalVisible = true;
    }
    
    public function novaServisnaShowwModal($id, $naziv='', $dist_id=0, $dist_cena_id=0)
    {
        /* if($naziv){
            dd(LicencaDistributerCena::nazivServisneKojaGaziTrajnu(2, $naziv), $dist_id, $dist_cena_id);
        } */
        $this->nazivZaServisnu = $naziv;
        $this->terminal_dist_id = $dist_id;
        $this->terminal_dist_cena_id = $dist_cena_id;
        $this->parametri = [];
        $this->distId = 2;
        $this->licence_dodate_terminalu = [];
        $this->datum_pocetka_licence = Helpers::datumKalendarNow();
        $this->datum_kraja_licence = Helpers::addDaysToDate($this->datum_pocetka_licence, 4);
        $this->datum_prekoracenja = Helpers::addDaysToDate($this->datum_pocetka_licence, 5);
        $this->datum_prekoracenja = Helpers::moveWikendToMonday($this->datum_prekoracenja);
        $this->modelId = $id; //ovo je id terminal lokacija tabele
        $this->selectedTerminal = SelectedTerminalInfo::selectedTerminalInfoTerminalLokacijaId($this->modelId);
        $this->licence_za_dodavanje = [];
        $this->novaServisnaModalVisible = true;
    }

    public function dodajServisnueLicence()
    {
        $this->selectedTerminal = SelectedTerminalInfo::selectedTerminalInfoTerminalLokacijaId($this->modelId);
        if($this->nazivZaServisnu){
            //Servisna gazi trajnu
            //Ocisti Licenca_naplatas tabelu
            
            foreach($this->licence_za_dodavanje as $lc){
                DB::transaction(function(){
                    LicencaNaplata::where([
                        'terminal_lokacijaId' => $this->modelId,
                        'distributerId' => $this->terminal_dist_id,
                        'licenca_distributer_cenaId' =>$this->terminal_dist_cena_id
                    ])->update(['aktivna' => 0]);
                    LicenceZaTerminal::where([
                        'terminal_lokacijaId' => $this->modelId,
                        'distributerId' => $this->terminal_dist_id,
                        'licenca_distributer_cenaId' =>$this->terminal_dist_cena_id
                    ])->delete();
                });
            }

        }
        foreach($this->licence_za_dodavanje as $lc){
            
            DB::transaction(function()use($lc) {

                $licenceInf = LicencaDistributerCena::licencaCenaIdInfo($lc);
                $nazivLicence = $licenceInf->licenca_naziv;
                $licenca_tip_id = $licenceInf->id;

                $key_arr = [
                    'terminal_lokacijaId' => $this->modelId,
                    'distributerId' => $this->distId,
                    'licenca_distributer_cenaId' => $lc,
                ];

                $signature_vals = [
                    'mesecId'=> 0,
                    'terminal_sn' => $this->selectedTerminal->sn,
                    'datum_pocetak' => $this->datum_pocetka_licence,
                    'datum_kraj' => $this->datum_kraja_licence,
                    'datum_prekoracenja' => $this->datum_prekoracenja,
                    'naziv_licence' => $nazivLicence
                ];

                LicencaServisna::create([
                    'userId' => auth()->user()->id,
                    'terminal_lokacijaId' => $this->modelId,
                    'distributerId' => $this->distId,
                    'licenca_naziv' => $nazivLicence,
                    'terminal_sn' => $this->selectedTerminal->sn,
                    'licenca_distributer_cenaId' => $lc,
                    'datum_pocetka_licence' => $this->datum_pocetka_licence,
                    'datum_kraj_licence' => $this->datum_kraja_licence,
                    'datum_isteka_prekoracenja' => $this->datum_prekoracenja
                ]);
                LicenceZaTerminal::create([
                    'terminal_lokacijaId' => $this->modelId,
                    'distributerId' => $this->distId,
                    'licenca_distributer_cenaId' => $lc,
                    'naziv_licence' => $nazivLicence,
                    'mesecId' => 0,
                    'terminal_sn' => $this->selectedTerminal->sn,
                    'datum_pocetak' => $this->datum_pocetka_licence,
                    'datum_kraj' => $this->datum_kraja_licence,
                    'datum_prekoracenja' => $this->datum_prekoracenja,
                    'licenca_poreklo' => 2,
                    'signature' => CryptoSign::criptSignature($signature_vals)
                ]);

                if(count($this->parametri)) LicencaParametarTerminal::addParametarsToLicence($key_arr, $licenca_tip_id, $this->parametri);
            });

        }
        $this->licence_za_dodavanje=[];
        $this->novaServisnaModalVisible = false;

        //dd($values, $signature_vals, $model_za_lic);
    }

   

    public function novaServisnaIzPregleda($naziv, $dist_id, $dist_cena_id)
    {
        $this->licencaModalVisible = false;
        //dd($naziv, $dist_id, $dist_cena_id);
        $this->novaServisnaShowwModal($this->modelId, $naziv, $dist_id, $dist_cena_id);
    }

    public function pomeriPrekoracenjePrivremenoj($naziv, $dist_id, $dist_cena_id)
    {
        $this->licencaModalVisible = false;
        //dd($naziv, $dist_id, $dist_cena_id);
        $this->pomeriPrekoracenjeShowwModal($naziv, $dist_id, $dist_cena_id);
    }
    
    /**
     * Pomeri prekoracenje
     *
     * @param mixed $id
     * @param mixed $naziv
     * @param mixed $dist_id
     * @param mixed $dist_cena_id
     * 
     * @return [type]
     * 
     */
    
    public function pomeriPrekoracenjeShowwModal($naziv, $dist_id=0, $dist_cena_id=0)
    {
        $this->nazivZaServisnu = $naziv;
        $this->terminal_dist_id = $dist_id;
        $this->terminal_dist_cena_id = $dist_cena_id;
        $this->parametri = [];
        $this->licencaData = LicenceZaTerminal::sveAktivneLicenceTerminala($this->modelId);
        $this->licencaZaProduzetak = $this->licencaData->where('licenca_distributer_cenaId', '=', $dist_cena_id)->first();
        $this->datum_pocetka_licence = $this->licencaZaProduzetak->datum_pocetak;
        $this->datum_kraja_licence = $this->licencaZaProduzetak->datum_kraj;
        //dd($this->licencaZaProduzetak);
        $this->datum_prekoracenja = Helpers::moveWikendToMonday(Helpers::addDaysToDate(Helpers::datumKalendarNow(), 5));
        $this->selectedTerminal = SelectedTerminalInfo::selectedTerminalInfoTerminalLokacijaId($this->modelId);
        $this->selectedTerminalSn = $this->selectedTerminal->sn;

        $this->pomeriPrekoracenjeModalVisible = true;
    }

    public function produziPrekoracenjePrivremenoj()
    {
        $this->validate([
            'datum_prekoracenja' => 'required'
        ]);
       
        $key_arr = [
            'terminal_lokacijaId' => $this->modelId,
            'distributerId' => $this->terminal_dist_id,
            'licenca_distributer_cenaId' => $this->terminal_dist_cena_id,
        ];

        $signature_vals = [
            'mesecId'=> 0,
            'terminal_sn' => $this->selectedTerminalSn,
            'datum_pocetak' => $this->datum_pocetka_licence,
            'datum_kraj' => $this->datum_kraja_licence,
            'datum_prekoracenja' => $this->datum_prekoracenja,
            'naziv_licence' => $this->nazivZaServisnu
        ];

        LicencaServisna::create([
            'userId' => auth()->user()->id,
            'terminal_lokacijaId' => $this->modelId,
            'distributerId' => $this->terminal_dist_id,
            'licenca_naziv' => $this->nazivZaServisnu,
            'terminal_sn' => $this->selectedTerminalSn,
            'licenca_distributer_cenaId' => $this->terminal_dist_cena_id,
            'datum_pocetka_licence' => $this->datum_pocetka_licence,
            'datum_kraj_licence' => $this->datum_kraja_licence,
            'datum_isteka_prekoracenja' => $this->datum_prekoracenja,
            'tip' => 2
        ]);
        LicenceZaTerminal::updateOrCreate($key_arr,
        [
            'terminal_lokacijaId' => $this->modelId,
            'distributerId' => $this->terminal_dist_id,
            'licenca_distributer_cenaId' => $this->terminal_dist_cena_id,
            'naziv_licence' => $this->nazivZaServisnu,
            'mesecId' => 0,
            'terminal_sn' => $this->selectedTerminalSn,
            'datum_pocetak' => $this->datum_pocetka_licence,
            'datum_kraj' => $this->datum_kraja_licence,
            'datum_prekoracenja' => $this->datum_prekoracenja,
            'licenca_poreklo' => 3,
            'signature' => CryptoSign::criptSignature($signature_vals)
        ]);



        $this->pomeriPrekoracenjeModalVisible = false;
    }

    public function commentsShowModal($id)
    {
        $this->newKoment = '';
        $this->resetErrorBag();
        $this->selectedTerminalComments = [];
        $this->modelId = $id; //ovo je id terminal lokacija tabele
        $this->selectedTerminal = SelectedTerminalInfo::selectedTerminalInfoTerminalLokacijaId($this->modelId);
        $this->selectedTerminalCommentsCount = $this->selectedTerminal->br_komentara;
        $this->selectedTerminalComments = TerminalLokacija::find($this->modelId)->comments()->get();
        //dd($this->selectedTerminalComments);
        $this->modalKomentariVisible = true;
    }

    public function posaljiKomentar()
    {
        $this->selectedTerminal = SelectedTerminalInfo::selectedTerminalInfoTerminalLokacijaId($this->modelId);
        $this->selectedTerminalCommentsCount = $this->selectedTerminal->br_komentara;
        
        $this->selectedTerminalComments = TerminalLokacija::find($this->modelId)->comments()->get();
        
        $this->validate([
            'newKoment' => 'required|min:3|max:1000',
        ]);

        TerminalLokacija::find($this->modelId)->comments()
            ->create([
                'comment' => $this->newKoment,
                'userId' => auth()->user()->id,
            ]);

        $this->selectedTerminalComments = TerminalLokacija::find($this->modelId)->comments()->get();
        
        if($this->selectedTerminalComments->count() != $this->selectedTerminalCommentsCount){
            $this->selectedTerminalCommentsCount = $this->selectedTerminalComments->count();
            TerminalLokacija::where('id', $this->modelId)
                ->update([
                    'br_komentara'          => $this->selectedTerminalCommentsCount, 
                    'last_comment_userId'   => auth()->user()->id, 
                    'last_comment_at'       => now()
                ]);
        }
        
        $this->newKoment = '';
    } 
    
    /**
     * Obrisi komentar
     *
     * @param mixed $id
     * 
     * @return [type]
     * 
     */
    public function obrisiKomentar($id)
    {
        $this->selectedTerminal = SelectedTerminalInfo::selectedTerminalInfoTerminalLokacijaId($this->modelId);
        $this->selectedTerminalCommentsCount = $this->selectedTerminal->br_komentara;
        $komentar = TerminalLokacija::find($this->modelId)->comments()->find($id);
        if($komentar){
            $komentar->update(['is_active' => false, 'deleted_at' => now()]);
            $this->selectedTerminalCommentsCount--;
            TerminalLokacija::where('id', $this->modelId)->update(['br_komentara' => $this->selectedTerminalCommentsCount]);
            $this->selectedTerminalComments = TerminalLokacija::find($this->modelId)->comments()->get();
        }
    }

    public function render()
    {
        return view('livewire.terminal', [
            'data' => $this->read(),
        ]);
    }
}