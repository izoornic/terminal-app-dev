<?php

namespace App\Http\Livewire\Managment;

use Livewire\Component;
use App\Models\LicencaNaplata;
use Illuminate\Support\Facades\DB;
use App\Http\Helpers;

class PocetakLicenceGrafik extends Component
{
    public $distId;
    public $grafikNaslov;
    //public $dist_name;
    
    public $broj_produzenih;
    public $broj_novih;

    public $broj_istekilh;


    /**
     * mount
     *
     * @return void
     */
    public function mount()
    {
        $this->distId = request()->query('id') ?? 0;
        $this->broj_produzenih = 0;
        $this->broj_novih = 0;
        $this->broj_istekilh = 0;
    }

    public function read()
    {
        //Tabela sa novim licencama koja sadrži samo tri polja koja su kljuc
        $nove_licence_index = LicencaNaplata::select(DB::raw('count(*) as `ctn`'), 'terminal_lokacijaId', 'distributerId', 'licenca_distributer_cenaId')
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
                ->where('licenca_naplatas.aktivna', '=', DB::raw("1"))
                ->get();
        
        //Tabela sa novim licencama grupisana po mesecima
        $nove_licence = LicencaNaplata::select(DB::raw('count(id) as `data`'), DB::raw('YEAR(datum_pocetka_licence) year, MONTH(datum_pocetka_licence) month'))
                ->whereIn('id', $nove_licence_sinle->pluck('id'))
                ->when($this->distId, function ($query) {
                    return $query->where('distributerId', $this->distId);
                })
                ->where('licenca_naplatas.aktivna', '=', DB::raw("1"))
                ->groupby('year','month')
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc')
                ->get();
        
       //Tabela sa svim licencama grupisana po mesecima
        $l_data = LicencaNaplata::select(DB::raw('count(id) as `data`'), DB::raw('YEAR(datum_pocetka_licence) year, MONTH(datum_pocetka_licence) month'))
                ->when($this->distId, function ($query) {
                    return $query->where('distributerId', $this->distId);
                })
                ->where('licenca_naplatas.aktivna', '=', DB::raw("1"))
                ->groupby('year','month')
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc')
                ->get();
        
        //Tabela sa isteklim licencama grupisana po mesecima
        $l_data_istek = LicencaNaplata::select(DB::raw('count(id) as `data`'), DB::raw('YEAR(datum_pocetka_licence) year, MONTH(datum_pocetka_licence) month'))
                ->when($this->distId, function ($query) {
                    return $query->where('distributerId', $this->distId);
                })
                ->where('licenca_naplatas.aktivna', '=', DB::raw("1"))
                ->where('datum_kraj_licence', '<', DB::raw("now()"))
                ->groupby('year','month')
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc')
                ->get();
        //dd($l_data_istek);
        //Objedinjena tabela sa svim licencama grupisana po mesecima
        $l_data->each(function ($item, $key) use ($nove_licence, $l_data_istek) {
            //Nove licence
            $item->nove = $nove_licence->where('year', $item->year)->where('month', $item->month)->first()->data ?? 0;
            if($item->nove > 0){
                $item->data = $item->data - $item->nove;
            }

            //Istekle licence
            $item->istekla = $l_data_istek->where('year', $item->year)->where('month', $item->month)->first()->data ?? 0;

            //Labele na X osi
            $item->month = Helpers::nameOfTheMounth($item->year.'-'.$item->month.'-01');

            //Broj licenci
            $this->broj_produzenih += $item->data;
            $this->broj_novih += $item->nove;
            $this->broj_istekilh += $item->istekla;
        });

        return $l_data;

    }

    public function render()
    {
        return view('livewire.managment.pocetak-licence-grafik', [
            'data' => $this->read()
        ]);
    }
}
