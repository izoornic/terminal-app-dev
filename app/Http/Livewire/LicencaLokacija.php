<?php

namespace App\Http\Livewire;

use Auth;
use App\Models\User;
use App\Models\Lokacija;
use App\Models\TerminalLokacija;
use App\Models\LokacijaKontaktOsoba;
use App\Models\TerminalLokacijaHistory;

use Livewire\Component;
use Livewire\WithPagination;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

use App\Ivan\TerminalBacklist;
use App\Ivan\SelectedTerminalInfo;

use App\Http\Helpers;

use App\Actions\Lokacije\LokacijaInfo;

class LicencaLokacija extends Component
{
    use WithPagination;
    
    public $modalFormVisible;
    public $modalConfirmDeleteVisible;
    public $modelId;

    //MODEL DATA
    public $l_naziv;
    public $mesto;
    public $adresa;
    public $latitude;
    public $longitude;
    public $regionId;
    public $lokacija_tipId;
    public $pib;

    //pretraga
    public $searchName;
    public $searchMesto;
    public $searchTip;
    public $searchRegion = 0;
    public $searchPib;

    //order
    public $orderBy;

    //INFO mdal
    public $odabranaLokacija;
    public $deletePosible;
    public $brTerminala;
    public $terminaliList;

    //kontakt osoba u prodavnici
    public $kontaktOsobaVisible;
    public $kontaktOsobaInfo;

    //Blacklist MODAL
    public $modalBlacklistVisible;
    public $canBlacklist;
    public $selectedTerminal;
    public $canBlacklistErorr;
    public $terLocId;

    // koordinate
    public $latLogVisible;
    public $latLogValue;
    public $lat_value;
    public $long_value;

    /**
     * mount
     *
     * @return void
     */
    public function mount()
    {
        if (session('searchTip') == null ){
            session(['searchTip' => 3]);
        };
        $this->searchTip = session('searchTip');
        $this->modalBlacklistVisible = false;
    }


    /**
     * The read function.
     *
     * @return void
     */
    public function read()
    {
        $order = 'id';
        switch($this->orderBy){
            case 'uid':
                $order = 'id';
            break;
            case 'name':
                $order = 'l_naziv';
            break;
            case 'mesto':
                $order = 'mesto';
            break;
            case 'region':
                $order = 'regionId';
            break;
            case 'tip':
                $order = 'lokacija_tipId';
            break;
        };
       
        return Lokacija::select('lokacijas.*', 'lokacija_tips.lt_naziv', 'regions.r_naziv', 'lokacija_kontakt_osobas.name as kontakt', 'lokacija_tips.id as tipid')
        ->leftJoin('regions', 'regions.id', '=', 'lokacijas.regionId')
        ->leftJoin('lokacija_tips', 'lokacijas.lokacija_tipId', '=', 'lokacija_tips.id')
        ->leftJoin('lokacija_kontakt_osobas', 'lokacijas.id', '=', 'lokacija_kontakt_osobas.lokacijaId')
        ->where('lokacijas.l_naziv', 'like', '%'.$this->searchName.'%')
        ->where('lokacijas.mesto', 'like', '%'.$this->searchMesto.'%')
        ->when($this->searchPib, function ($rtval){
            return $rtval->where('lokacijas.pib', 'like', '%'.$this->searchPib.'%');
        } )
        ->where('lokacijas.regionId', ($this->searchRegion > 0) ? '=' : '<>', $this->searchRegion)
        ->where('lokacijas.lokacija_tipId', ($this->searchTip > 0) ? '=' : '<>', $this->searchTip)
        ->orderBy($order)
        ->paginate(Config::get('global.paginate'), ['*'], 'lokacije');
    }
    

    /**
     * The data for the model mapped
     * in this component.
     *
     * @return void
     */
    public function modelData()
    {
        return [  
            'l_naziv'          => $this->l_naziv,
            'mesto'            => $this->mesto,
            'adresa'           => $this->adresa,
            'latitude'         => ($this->latitude == '') ? NULL : $this->latitude,
            'longitude'        =>($this->longitude == '') ? NULL : $this->longitude,
            'regionId'         => $this->regionId,
            'lokacija_tipId'   => $this->lokacija_tipId,
            'pib'              =>$this->pib,   
        ];
    }

    /**
     * [Description for BlacklistShowModal]
     *
     * @param mixed $tlid
     * 
     * @return [type]
     * 
     */
    public function BlacklistShowModal($tlid)
    {
        $this->modalConfirmDeleteVisible = false;
        
        $this->terLocId = $tlid;

        $this->canBlacklist = true;
        $this->selectedTerminal = SelectedTerminalInfo::selectedTerminalInfoTerminalLokacijaId($this->terLocId);
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

        $this->modalBlacklistVisible = true;
    }

     /**
     * The update function
     *
     * @return void
     */
    public function blacklistUpdate()
    {
        if(TerminalBacklist::AddRemoveBlacklist($this->terLocId)){
            TerminalBacklist::CreateBlacklistFile();
        }
        $this->terLocId = 0;
        $this->modalBlacklistVisible = false;
    }
   
    /**
     * Shows the IFO  modal.
     *
     * @param  mixed $id
     * @return void
     */
    public function deleteShowModal($id)
    {
        $this->modelId = $id;
        $this->odabranaLokacija = LokacijaInfo::getInfo($this->modelId); 
       
        $this->deletePosible = false;
        
        //check if lokacija zakacena za nekog
        $data = User::where('lokacijaId', $id)->first();
        if($data === NULL){
            $this->deletePosible = true;
        };//else if($data)

        //check if lokacija ima terminale 
        $this->brTerminala = TerminalLokacija::brojTerminalaNalokaciji($id);
        if($this->brTerminala){
            $this->deletePosible = false;
        };

        //prikazi terminale na lokaciji koja je korisnik
        
        if($this->odabranaLokacija->lokacija_tipId == 3){
            $this->terminaliList = $this->terminalsAtLocation();
        }

        $this->modalConfirmDeleteVisible = true;
    }    

    private function terminalsAtLocation()
    {
        return TerminalLokacija::select('terminal_status_tips.ts_naziv', 'terminals.sn', 'terminals.terminal_tipId', 'terminals.broj_kutije', 'terminal_lokacijas.blacklist', 'terminal_lokacijas.id as tlid')
                ->leftJoin('terminals', 'terminal_lokacijas.terminalId', '=', 'terminals.id')
                ->leftJoin('terminal_status_tips', 'terminal_lokacijas.terminal_statusId', '=', 'terminal_status_tips.id')
                ->where('lokacijaId', $this->modelId )
                ->get();
    }
    
    /**
     * Creates Gmap link
     *
     * @param  mixed $lat
     * @param  mixed $log
     * @return void
     */
    public static function createGmapLink($lat, $log)
    {
        return 'https://www.google.com/maps/search/?api=1&query='.$lat.','.$log;
    }
    
    /**
     * Lists all rows in all tables that use particular location
     *
     * @param  mixed $id
     * @return void
     */
    public function locationUsers($id)
    {
        $retval = [];
        $retval['users'] = [];
        foreach(User::where('lokacijaId', $id)->get() as $row){
            array_push($retval['users'], $row['name']);
        };
        
        if(TerminalLokacija::brojTerminalaNalokaciji($id)){
            $retval['terminal'] = [];
            array_push($retval['terminal'], TerminalLokacija::brojTerminalaNalokaciji($id));
        } 
        //dd($retval); 
       return $retval;
    }
    
    
    /**
     * Prikazuje Kontakt osoba Modal
     *
     * @param  mixed $id
     * @return void
     */
    public function showKontaktOsobaModal($id)
    {
        $this->modelId = $id;
        $this->odabranaLokacija = LokacijaInfo::getInfo($this->modelId);
        $this->kontaktOsobaInfo = $this->kontaktOsobaGetInfo();
        $this->kontaktOsobaVisible = true;
    }

    private function kontaktOsobaGetInfo()
    {
        return LokacijaKontaktOsoba::where('lokacijaId', '=', $this->modelId)
                                ->first();
    }

    public function showLatLogModal($id)
    {
        $this->resetValidation();
        $this->modelId = $id;
        $this->odabranaLokacija = LokacijaInfo::getInfo($this->modelId);
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
        
        Lokacija::find($this->modelId)->update(['latitude' => $exp[0], 'longitude' => $exp[1]]);
        $this->latLogVisible = false;
    }

    public function removeLatLog()
    {
        Lokacija::find($this->modelId)->update(['latitude' => NULL, 'longitude' => NULL]);
        $this->latLogVisible = false;
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
        session(['searchTip' =>  $this->searchTip]);
    }

    public function render()
    {
        return view('livewire.licenca-lokacija', [
            'data' => $this->read(),
        ]);
    }
}