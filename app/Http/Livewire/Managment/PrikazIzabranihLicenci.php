<?php

namespace App\Http\Livewire\Managment;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\LicencaNaplata;
use App\Models\TerminalLokacija;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

use App\Http\Helpers;

class PrikazIzabranihLicenci extends Component
{
    use WithPagination;

    public $distId;
    public $dataClicked;

    public $vrstaLicence;
    public $filterDate;

    public $searchTerminalSn;
    public $searchMesto;
    public $searchTipLicence;

    public $heading = '';

    /*
     * @vrstaLicence int 
     * 0 = Produžene licence
     * 1 = Nove licence
     * 2 = Istekle licence
     */
    public function mount()
    {
        if($this->dataClicked){
            $rowData = explode('-', $this->dataClicked);
            $year =  explode('(', $rowData[1])[0];
            $this->filterDate = preg_replace('/\s+/', '', $year).'-'.Helpers::monthNameToNumber(preg_replace('/\s+/', '', $rowData[0])).'-01';
            //dd($this->filterDate);
            ($this->filterDate == '2000-01-01') ? $this->filterDate = null : $this->filterDate;
            $this->vrstaLicence = (int)$rowData[2]; 
            ($this->distId == '0') ? $this->distId = null : $this->distId;
        }else{
            $this->vrstaLicence = -1; // -1 = Nema odabranih licenci
        }

        if($this->vrstaLicence == 0){
            $this->heading = 'Produžene licence';
        }else if($this->vrstaLicence == 1){
            $this->heading = 'Nove licence';
        }else if($this->vrstaLicence == 2){
            $this->heading = 'Istekle licence';
        }else{
            $this->heading = 'Nema odabranih licenci';
        }

        if($this->filterDate){
            $this->heading .= ' za: '.$rowData[0].' '.$year.'.';
        }else{
            $this->heading .= ' - sve';
        }

    }

     /**
     * The read function. searchTipLicence
     *
     * @return void
     */
    public function read()
    {
        $l_data_istek = null;
        $nove_licence_index = null;
        $nove_licence_sinle = null;
        $produzene_licence_sinle = null;
        if($this->vrstaLicence > -1){
            // Ako je vrsta licence 0 ili 1, onda se traže licence koje su produžene ili nove 
            // Ako je vrsta licence 2, onda se traže licence koje su istekle
            // Ako je vrsta licence -1, onda se ne traže licence
            return LicencaNaplata::select(
                'licenca_naplatas.*',
                'lokacijas.l_naziv', 
                'lokacijas.mesto', 
                'lokacijas.adresa'
            )
            ->LeftJoin('terminal_lokacijas', 'licenca_naplatas.terminal_lokacijaId', '=', 'terminal_lokacijas.id')
            ->leftJoin('lokacijas', 'terminal_lokacijas.lokacijaId', '=', 'lokacijas.id')
            ->where('licenca_naplatas.aktivna', '=', DB::raw("1"))
            ->where('licenca_naplatas.licenca_naziv', '=', DB::raw('"esir"'))
            ->when($this->distId, function ($query) {
                    return $query->where('licenca_naplatas.distributerId', '=', $this->distId);
                })
            ->when($this->vrstaLicence == 0 || $this->vrstaLicence == 1, function ($query) {
                    return $query->where('licenca_naplatas.nova_licenca', '=', $this->vrstaLicence);
            })
            ->when(($this->vrstaLicence == 2), function ($query) {
                    return $query->where('licenca_naplatas.datum_kraj_licence', '<', DB::raw("now()"));
            })
            ->when($this->filterDate, function ($query) {
                    return $query->where('licenca_naplatas.datum_pocetka_licence', '>=', $this->filterDate)
                                  ->where('licenca_naplatas.datum_pocetka_licence', '<=', Helpers::lastDayOfManth($this->filterDate));
            })
            ->when($this->searchTerminalSn, function ($query) {
                return $query->where('licenca_naplatas.terminal_sn', 'like', '%'.$this->searchTerminalSn.'%');
            })
            ->when($this->searchMesto, function ($query) {
                return $query->where('lokacijas.mesto', 'like', '%'.$this->searchMesto.'%')
                             ->orWhere('lokacijas.l_naziv', 'like', '%'.$this->searchMesto.'%');
            })
            ->orderBy(\DB::raw("COALESCE(licenca_naplatas.datum_kraj_licence, '9999-12-31')", 'ASC'))
            ->paginate(Config::get('terminal_paginate'), ['*'], 'terminali');

        }

        return LicencaNaplata::where('id', -1)
        ->paginate(Config::get('terminal_paginate'), ['*'], 'terminali');
    }

    public function render()
    {
        return view('livewire.managment.prikaz-izabranih-licenci', [
            'data' => $this->read()
        ]);
    }
}
