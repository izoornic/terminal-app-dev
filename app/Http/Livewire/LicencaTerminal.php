<?php

namespace App\Http\Livewire;

use App\Models\Lokacija;
use App\Models\Terminal;
use App\Models\TerminalLokacija;
use App\Models\LicenceZaTerminal;
use App\Models\LicencaDistributerTip;
use App\Models\DistributerLokacijaIndex;
use App\Models\TerminalLokacijaHistory;
use App\Models\LicencaParametarTerminal;

use Livewire\Component;
use Livewire\WithPagination;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

use App\Http\Helpers;

use App\Ivan\TerminalHistory;
use App\Ivan\TerminalBacklist;
use App\Ivan\SelectedTerminalInfo;

use App\Actions\Terminali\TerminaliReadActions;

class LicencaTerminal extends Component
{
    use WithPagination;
    
    public $modelId;

    //premesti modal
    public $modalConfirmPremestiVisible;

    public $plokacijaTip;
    public $plokacija;
    public $searchPLokacijaNaziv;
    public $searchPlokacijaMesto;
    public $searchPlokacijaRegion;
    public $canMoveTerminal = 0;
    public $modalStatusPremesti;
    public $datum_premestanja_terminala;

    public $selectedTerminal;

    //LICENCA GRESKA MODAL
    public $licencaError;
    public $modalErorLicencaVisible;
   
    //BLACKLIST
    public $modalFormVisible;
    public $canBlacklist;
    public $canBlacklistErorr;

    //select all
    public $selectedTerminals = [];
    public $selectAll;
    public $allInPage = [];

    //search
    public $searchSB;
    public $searchKutija;
    public $searchNazivLokacije;
    public $searchRegion;
    public $searchTipLokacije;
    public $searchStatus;
    public $searchBlackist;
    public $searchPib;

    //multi selected
    public $multiSelected;
    public $multiSelectedInfo;

    //terminal HISTORY
    public $terminalHistoryVisible;
    public $historyData;

    //licence modal
    public $licencaModalVisible;
    public $licencaData;

    //novi terminal
    public $modalNoviTerminalVisible;
    public $datum_dodavanja_terminala;
    public $errAddMsg;
    public $noviSN;
    public $noviKutijaNO;
    public $new_terminal_tip;
    public $t_status;
   
    //komentari
    public $modalKomentariVisible;
    /* public $selectedTerminalComments;
    public $selectedTerminalCommentsCount;
    public $newKoment; */

    /**
     * [Description for mount]
     *
     * @return [type]
     * 
     */
    public function mount()
    {
        $this->searchBlackist = 0;
    }

    /**
     * The validation rules
     *
     * @return void
     */
    public function rules()
    {
       
    }

    /**
     * The read function.
     *
     * @return void
     */
    public function read()
    {
        //dd($this->searchBlackist);
        $this->allInPage = [];
        //get the builder with filters applied
        $search = [
            'searchSB' => $this->searchSB,
            'searchKutija' => $this->searchKutija,
            'searchNazivLokacije' => $this->searchNazivLokacije,
            'searchRegion' => $this->searchRegion,
            'searchTipLokacije' => $this->searchTipLokacije,
            'searchStatus' => $this->searchStatus,
            'searchBlackist' => $this->searchBlackist,
            'searchPib' => $this->searchPib,
        ];

        $builder = TerminaliReadActions::TerminaliRead($search);
        
        // paginate the builder
        $perPage = Config::get('global.terminal_paginate');
        $terms = $builder->paginate($perPage, ['*'], 'terminali');

        // enrich paginated items and collect ids present on the current page
        $terms->getCollection()->transform(function ($item) {
            $licenca = LicenceZaTerminal::where('terminal_lokacijaId', $item->tlid)->first();
            $item->tzlid = $licenca ? $licenca->licenca_poreklo : 0;
            $this->allInPage[] = $item->tlid;
            return $item;
        });
        
        return $terms;
    }
    
    public function noviTerminalShowModal()
    {
        $this->noviSN = '';
        $this->noviKutijaNO = '';
        $this->new_terminal_tip = 0;
        $this->t_status = 0;
        $this->errAddMsg = '';
        $this->datum_dodavanja_terminala = Helpers::datumKalendarNow();
        $this->modalNoviTerminalVisible = true;
    }

    public function noviTerminalAdd()
    {
        $this->validate([
            'noviSN' => 'required',
            'noviKutijaNO' => 'required',
        ]);

        if($this->t_status < 1 || $this->new_terminal_tip < 1){
            $this->errAddMsg = 'Niste izabrali status ili tip terminala';
            return;
        }
        
        if(Terminal::where('sn', 'like', $this->noviSN)->first()){
            $this->errAddMsg = 'Terminal sa serijskim brojem koji ste uneli već postoji!';
            return;
        }
        
        $this->errAddMsg = '';
        DB::transaction(function(){
            //add to terminal table
            $newTerminal = Terminal::create(['sn' => $this->noviSN, 'terminal_tipId' => $this->new_terminal_tip, 'broj_kutije' => $this->noviKutijaNO]);
            TerminalLokacija::create(['terminalId' => $newTerminal->id, 'lokacijaId' => 3, 'terminal_statusId'=> $this->t_status, 'korisnikId'=>auth()->user()->id, 'korisnikIme'=>auth()->user()->name, 'updated_at'=>$this->datum_dodavanja_terminala ]);
        });
        $this->modalNoviTerminalVisible = false; 
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
        $this->modelId = $id; //ovo je id terminal lokacija tabele
        $this->plokacijaTip = 0;
        $this->plokacija = 0;
        $this->canMoveTerminal = 0;
        $this->modalStatusPremesti = $status;
        $this->licencaError = '';

        $this->searchPLokacijaNaziv ='';
        $this->searchPlokacijaMesto ='';
        $this->searchPlokacijaRegion = 0;
        
        $this->modalConfirmPremestiVisible = true;

        $this->datum_premestanja_terminala = Helpers::datumKalendarNow();
    } 

    public function premestiSelectedShowModal(){
        
        $this->multiSelected = true;

        //status na listi se setuje prema prvom izabranom terminalu
        $this->modalStatusPremesti = TerminalLokacija::where('id', $this->selectedTerminals[0])->first()->terminal_statusId;
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
     * Premesta terminal na novu lokaciju 
     *
     * @return void
     */
    public function moveTerminal(){
        //dodam datum ako ne valja
        if(!(bool)strtotime($this->datum_premestanja_terminala)) $this->datum_premestanja_terminala = Helpers::datumKalendarNow();
        //zalepim vreme za timestamp
        $this->datum_premestanja_terminala.= ' '.Helpers::vremeKalendarNow();

        //da li se terminal dodaje Distributeru?
        $distributer_tip_id = ($this->plokacijaTip == 4) ? DistributerLokacijaIndex::where('lokacijaId', '=', $this->plokacija)->first()->licenca_distributer_tipsId : null;
       
        if(!$this->multiSelected) $this->selectedTerminals[0] = $this->modelId;

        //Da li terminal ima aktivnu licencu Brisu se servisne licence ako ih ima
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
     * Shows blacklist update modal - multi or sigle
     *
     * @return void
     */
    public function blacklistShowModal($id=0)
    {
        $this->canBlacklistErorr = '';
        $this->canBlacklist = true;
        $this->multiSelected = false;
        $this->modelId = $id;
        $this->selectedTerminal = SelectedTerminalInfo::selectedTerminalInfoTerminalLokacijaId($this->modelId);
        if($this->selectedTerminal->blacklist == 1){
            $this->canBlacklistErorr = 'Da li ste sigurni da želite da uklonite terminal sa Blackliste?';
        }else{
            $this->canBlacklistErorr = 'Da li ste sigurni da želite da dodate terminal na Blacklistu?';
        }
        if($this->selectedTerminal->lokacija_tipId != 3){
            $this->canBlacklist = false;
            $this->canBlacklistErorr = 'Samo terminali koji su instalirani korisnicima mogu se dodavti na Blacklistu!';
        }
        if($this->selectedTerminal->ts_naziv != 'Instaliran'){
            $this->canBlacklist = false;
            $this->canBlacklistErorr = 'Samo terminali sa statsom "Instaliran" se mogu dodavti na Blacklistu!';
        }
            
        
        $this->modalFormVisible = true;
    }

     /**
     * The update function
     *
     * @return void
     */
    public function blacklistUpdate()
    {
        if(TerminalBacklist::AddRemoveBlacklist($this->modelId)){
            TerminalBacklist::CreateBlacklistFile();
        }
        $this->selectedTerminals=[];
        $this->canBlacklistErorr = '';
        $this->modalFormVisible = false;
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
        //$this->selectedTerminal = SelectedTerminalInfo::selectedTerminalInfoTerminalLokacijaId($this->modelId);
        $this->historyData = TerminalHistory::terminalHistoryData($this->modelId);

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
        $this->modelId = $id; //ovo je id terminal lokacija tabele
        //$this->selectedTerminal = SelectedTerminalInfo::selectedTerminalInfoTerminalLokacijaId($this->modelId);
        $this->licencaData = LicenceZaTerminal::sveAktivneLicenceTerminala($id);
        //dd($this->licencaData);
        $this->licencaModalVisible = true;
    }

    /**
     * updated
     *
     * @return void
     */
    public function updated($key, $value)
    {
        
        session(['searchTipLokacije' =>  $this->searchTipLokacije]);
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

        /* if($this->modalConfirmPremestiVisible || $this->modalFormVisible){
            $this->selectedTerminal = SelectedTerminalInfo::selectedTerminalInfoTerminalId($this->modelId);
        } */

        /* if($this->modalFormVisible){
            //blacklist modal
            $this->selectedTerminal = SelectedTerminalInfo::selectedTerminalInfoTerminalId($this->modelId);
        } */

    }

    public function commentsShowModal($id)
    {
        $this->newKoment = '';
        $this->resetErrorBag();
        $this->modelId = $id; //ovo je id terminal lokacija tabele
        //$this->selectedTerminal = SelectedTerminalInfo::selectedTerminalInfoTerminalLokacijaId($this->modelId);
        $this->modalKomentariVisible = true;
    }

   

    public function render()
    {
        return view('livewire.licenca-terminal', [
            'data' => $this->read(),
        ]);
    }
}
