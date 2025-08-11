<?php

namespace App\Http\Livewire;

use App\Models\LicencaNaplata;
use App\Models\Lokacija;
use App\Models\TerminalLokacija;
use App\Models\LicencaParametar;
use App\Models\LicenceZaTerminal;
use App\Models\LicencaDistributerTip;
use App\Models\LicencaDistributerCena;
use App\Models\LicencaParametarTerminal;

use App\Http\Helpers;
use App\Ivan\CryptoSign;
use App\Ivan\SelectedTerminalInfo;

use Livewire\Component;
use Livewire\WithPagination;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LicencaNaplataExport;
use App\Actions\Lokacije\LokacijaInfo;

class DistributerTerminal extends Component
{
    use WithPagination;
    
    public $modalConfirmDeleteVisible;
    public $modelId; //ID u tabeli terminal_lokacias

    //set on MOUNT
    public $distId;
    public $dist_name;
    public $ditributer_info;
    public $broj_terminala;
    public $broj_licenci;
    
    //SEARCH MAIN
    public $searchTerminalSn;
    public $searchMesto;
    public $searchTipLicence;
    public $searchNenaplativ;
    public $searchPib;

    public $licence_dodate_terminalu = [];
    public $distrib_terminal_id;

    //DELETE MODAL
    public $deleteAction;

    //terminal info modal
    public $modalTerminalInfoVisible;
    public $terminalInfo;
    public $licenceNaziviInfo;

    // koordinate
    public $latLogVisible;
    public $latLogValue;
    public $lat_value;
    public $long_value;
    public $lokacijalId; // ID lokacije

    //komentari
    public $modalKomentariVisible;
   /*  public $selectedTerminalComments;
    public $selectedTerminalCommentsCount;
    public $newKoment;
    public $selectedTerminal; */
   /**
     * mount
     *
     * @return void
     */
    public function mount()
    {
        $this->distId = request()->query('id');
        //$this->dist_name = LicencaDistributerTip::DistributerName($this->distId);
        $this->ditributer_info = LicencaDistributerTip::where('id', '=', $this->distId)->first();
        $this->dist_name = $this->ditributer_info->distributer_naziv;
        $this->broj_terminala = $this->ditributer_info->broj_terminala;
        $this->checkAndUpdateBrojTerminala();
    }

    private function checkAndUpdateBrojTerminala()
    {
        $ter_and_lic_info = $this->prebrojLicenceITerminaleDistributera();
        $this->broj_licenci = $ter_and_lic_info['br_licenci'];
        if($ter_and_lic_info['br_terminala'] != $this->broj_terminala){
            $this->broj_terminala = $ter_and_lic_info['br_terminala'];
            LicencaDistributerTip::where('id', '=', $this->distId)->update(['broj_terminala' => $this->broj_terminala ]);
        }
    }

    /**
     * Koliko terminala ima distributer function.
     *
     * @return object
     */
    private function prebrojLicenceITerminaleDistributera()
    {
        return [
            'br_licenci' => LicencaNaplata::select()->where('distributerId', '=', $this->distId)->where('aktivna', '=', 1)->count(),
            'br_terminala' => TerminalLokacija::select()->where('distributerId', '=', $this->distId)->count()
        ];
    }

    /**
     * Shows terminal info modal.
     *
     * @return void
     */
    public function terminalInfoShowModal($terminal_lokacija_id)
    {
        $this->modelId = $terminal_lokacija_id;
        $this->licenceNaziviInfo = LicencaDistributerCena::naziviDodatihLicenci($this->licenceDodateTerminalu());
        $this->terminalInfo = SelectedTerminalInfo::selectedTerminalInfoTerminalLokacijaId($terminal_lokacija_id);
        $this->modalTerminalInfoVisible = true;
    }
   
    private function licenceDodateTerminalu()
    {
        return LicenceZaTerminal::where('terminal_lokacijaId', '=', $this->modelId)
                    ->pluck('licenca_distributer_cenaId')->all();
    }

   /**
    * Brise sve licence dodate terminalu
    *
    * @return [type]
    * 
    */
   public function deleteLicenceTerminala()
   {
        //dd($this->licenceDodateTerminalu());
        $licence_za_brisanje = LicenceZaTerminal::where('terminal_lokacijaId', '=', $this->modelId)->get();
        foreach ($licence_za_brisanje as $key => $value) {
            $key_arr = [
                'terminal_lokacijaId' => $value->terminal_lokacijaId,
                'distributerId' => $value->distributerId,
                'licenca_distributer_cenaId' => $value->licenca_distributer_cenaId
            ];
            // brisanje parametara licence
            LicencaParametarTerminal::deleteParametars($key_arr);
            $licenca_naplata = LicencaNaplata::where($key_arr)->where('aktivna', '=', 1)->first();
            //dd($licenca_naplata);
            $licenca_naplata->zaduzeno          = $licenca_naplata->zaduzeno ?? 0;
            $licenca_naplata->datum_zaduzenja   = $licenca_naplata->datum_zaduzenja ?? Helpers::datumKalendarNow();
            $licenca_naplata->razduzeno         = $licenca_naplata->razduzeno ?? 0;
            $licenca_naplata->datum_razduzenja  = $licenca_naplata->datum_razduzenja ?? Helpers::datumKalendarNow();
            $licenca_naplata->aktivna = 0;
            //dd($licenca_naplata);
            $licenca_naplata->save();

            // brisanje licence
            $value->delete();
        }

        foreach ($this->licenceDodateTerminalu() as $key => $value) { 
            
        }
        $this->modalTerminalInfoVisible = false;
   }

   public function downloadExcel()
    {
        return Excel::download(new LicencaNaplataExport($this->distId), 'licenca_naplata_ml.xlsx');
    }

     /**
     * The read function. searchTipLicence
     *
     * @return void
     */
    public function read()
    {
        
        return TerminalLokacija::select(
            'terminal_lokacijas.id', 
            'terminal_lokacijas.br_komentara',
            'terminals.sn', 
            'lokacijas.l_naziv', 
            'lokacijas.mesto', 
            'lokacijas.adresa', 
            'lokacijas.latitude', 
            'lokacijas.longitude',
            'lokacijas.pib',
            'lokacijas.id as lokid',
            'licenca_naplatas.id as lnid', 
            'licenca_naplatas.datum_pocetka_licence', 
            'licenca_naplatas.datum_kraj_licence',
            'licenca_naplatas.nenaplativ',
            'licenca_naplatas.zaduzeno', 
            'licenca_naplatas.razduzeno',
            'licenca_naplatas.nenaplativ',
            'licenca_tips.licenca_naziv', 
            'licenca_tips.id as ltid', 
            'licenca_tips.broj_parametara_licence')
    ->leftJoin('licenca_naplatas', function($join)
        {
            $join->on('licenca_naplatas.terminal_lokacijaId', '=', 'terminal_lokacijas.id');
            $join->on('licenca_naplatas.aktivna', '=', DB::raw("1"));
        })
    ->leftJoin('terminals', 'terminal_lokacijas.terminalId', '=', 'terminals.id')
    ->leftJoin('lokacijas', 'terminal_lokacijas.lokacijaId', '=', 'lokacijas.id')
    ->leftJoin('licenca_distributer_cenas', 'licenca_naplatas.licenca_distributer_cenaId', '=', 'licenca_distributer_cenas.id')
    ->leftJoin('licenca_tips', 'licenca_distributer_cenas.licenca_tipId', '=', 'licenca_tips.id')
    ->where('terminal_lokacijas.distributerId', '=', $this->distId)
    ->where('terminals.sn', 'like', '%'.$this->searchTerminalSn.'%')
    ->when($this->searchMesto > 0, function ($rtval){
        return $rtval->where('lokacijas.mesto', 'like', '%'.$this->searchMesto.'%')
            ->orWhere('lokacijas.adresa', 'like', '%'.$this->searchMesto.'%')
            ->orWhere('lokacijas.l_naziv', 'like', '%'.$this->searchMesto.'%');
    })
    ->when($this->searchTipLicence > 0, function ($rtval){
        return $rtval->where('licenca_distributer_cenas.id', '=', ($this->searchTipLicence == 1000) ? null : $this->searchTipLicence);
    })
    ->when($this->searchNenaplativ > 0, function ($rtval){
        return $rtval->where('licenca_naplatas.nenaplativ', '=', 1);
    })
    ->when($this->searchPib, function ($query) {
        return $query->where('lokacijas.pib', 'like', '%'.$this->searchPib.'%');
    })
    ->orderBy(\DB::raw("COALESCE(licenca_naplatas.datum_kraj_licence, '9999-12-31')", 'ASC'))
    ->orderBy('terminal_lokacijas.id')
    ->orderBy('licenca_distributer_cenas.licenca_tipId')
    ->paginate(Config::get('terminal_paginate'), ['*'], 'terminali');
        
    }

    public function showLatLogModal($id)
    {
        $this->resetValidation();
        $this->lokacijalId = $id;
        $this->odabranaLokacija = LokacijaInfo::getInfo($this->lokacijalId);
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
        //dd(Lokacija::find($this->modelId));
        Lokacija::find($this->lokacijalId)->update(['latitude' => $exp[0], 'longitude' => $exp[1]]);
        $this->latLogVisible = false;
    }

    public function removeLatLog()
    {
        Lokacija::find($this->lokacijalId)->update(['latitude' => NULL, 'longitude' => NULL]);
        $this->latLogVisible = false;
    }   

    public function commentsShowModal($id)
    {
        
        $this->resetErrorBag();
        $this->modelId = $id; //ovo je id terminal lokacija tabele
        $this->selectedTerminal = SelectedTerminalInfo::selectedTerminalInfoTerminalLokacijaId($this->modelId);
        $this->modalKomentariVisible = true;
    }

    public function render()
    {
        return view('livewire.distributer-terminal', [
            'data' => $this->read()
        ]);
    }
}