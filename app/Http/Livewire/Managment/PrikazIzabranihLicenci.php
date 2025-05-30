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
            $this->filterDate = preg_replace('/\s+/', '', $rowData[1]).'-'.Helpers::monthNameToNumber(preg_replace('/\s+/', '', $rowData[0])).'-01';
            ($this->filterDate == '2000-01-01') ? $this->filterDate = null : $this->filterDate;
            $this->vrstaLicence = (int)$rowData[2]; 
            ($this->distId == '0') ? $this->distId = null : $this->distId;
        }else{
            $this->vrstaLicence = 100;
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
            $this->heading .= ' za: '.$rowData[0].' '.$rowData[1].'.';
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
           if($this->vrstaLicence == 1 || $this->vrstaLicence == 0){
                //Tabela sa novim licencama koja sadrži samo tri polja koja su kljuc
                $nove_licence_index =  LicencaNaplata::select(DB::raw('count(*) as `ctn`'), 'terminal_lokacijaId', 'distributerId', 'licenca_distributer_cenaId')
                    ->when($this->distId, function ($query) {
                        return $query->where('distributerId', $this->distId);
                    })
                    ->groupby('terminal_lokacijaId','distributerId', 'licenca_distributer_cenaId')
                    ->having('ctn', '=', 1)
                    ->get();

                    
                //Tabela sa novim licencama full
                $nove_licence_sinle = LicencaNaplata::whereIn('terminal_lokacijaId', $nove_licence_index->pluck('terminal_lokacijaId'))
                    ->whereIn('licenca_distributer_cenaId', $nove_licence_index->pluck('licenca_distributer_cenaId'))
                    ->when($this->distId, function ($query) {
                        return $query->where('distributerId', $this->distId);
                    })
                    ->when($this->filterDate, function ($query) {
                        return $query->where('datum_pocetka_licence', '>=', $this->filterDate)
                            ->where('datum_pocetka_licence', '<=', Helpers::lastDayOfManth($this->filterDate));
                    })
                    ->where('licenca_naplatas.aktivna', '=', DB::raw("1"))
                    ->get();

                    
                //Tabela sa produzenim licencama
                $produzene_licence_sinle = LicencaNaplata::when($this->distId, function ($query) {
                        return $query->where('distributerId', $this->distId);
                    })
                    ->when($this->filterDate, function ($query) {
                        return $query->where('datum_pocetka_licence', '>=', $this->filterDate)
                            ->where('datum_pocetka_licence', '<=', Helpers::lastDayOfManth($this->filterDate));
                    })
                    ->whereNotIn('id', $nove_licence_sinle->pluck('id'))
                    ->where('licenca_naplatas.aktivna', '=', DB::raw("1"))
                    ->get();

            }else if($this->vrstaLicence == 2){
                //Tabela sa isteklima
                 $l_data_istek = LicencaNaplata::
                when($this->distId, function ($query) {
                    return $query->where('distributerId', $this->distId);
                })
                ->where('licenca_naplatas.aktivna', '=', DB::raw("1"))
                ->where('datum_kraj_licence', '<', DB::raw("now()"))
                ->when($this->filterDate, function ($query) {
                        return $query->where('datum_pocetka_licence', '>=', $this->filterDate)
                            ->where('datum_pocetka_licence', '<=', Helpers::lastDayOfManth($this->filterDate));
                    })
                ->get();
            }
            
            return TerminalLokacija::select(
                'terminal_lokacijas.id', 
                'terminals.sn', 
                'lokacijas.l_naziv', 
                'lokacijas.mesto', 
                'lokacijas.adresa', 
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
        ->where('licenca_tips.id', '=', 1) // samo licence tipa ESIR
        ->when($this->distId, function ($query) {
                    return $query->where('terminal_lokacijas.distributerId', '=', $this->distId);
                })
        ->when(($this->vrstaLicence == 1), function ($query)use($nove_licence_sinle) {
            return $query->whereIn('licenca_naplatas.id', $nove_licence_sinle->pluck('id'));
        })
        ->when(($this->vrstaLicence == 0), function ($query)use($produzene_licence_sinle) {
            return $query->whereIn('licenca_naplatas.id', $produzene_licence_sinle->pluck('id'));
        })
        ->when(($this->vrstaLicence == 2), function ($query)use($l_data_istek) {
            return $query->whereIn('licenca_naplatas.id', $l_data_istek->pluck('id'));
        })
        ->when($this->searchTerminalSn, function ($query) {
            return $query->where('terminals.sn', 'like', '%'.$this->searchTerminalSn.'%');
        })
        ->when($this->searchMesto, function ($query) {
            return $query->where('lokacijas.mesto', 'like', '%'.$this->searchMesto.'%');
        })
        
        ->orderBy(\DB::raw("COALESCE(licenca_naplatas.datum_kraj_licence, '9999-12-31')", 'ASC'))
        ->orderBy('terminal_lokacijas.id')
        ->orderBy('licenca_distributer_cenas.licenca_tipId')
        ->paginate(Config::get('terminal_paginate'), ['*'], 'terminali');

    }
        return TerminalLokacija::where('id', -1)
        ->paginate(Config::get('terminal_paginate'), ['*'], 'terminali');
    }

    public function render()
    {
        return view('livewire.managment.prikaz-izabranih-licenci', [
            'data' => $this->read()
        ]);
    }
}
