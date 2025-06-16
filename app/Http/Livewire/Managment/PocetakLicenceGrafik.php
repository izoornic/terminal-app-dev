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
    PUBLIC $broj_aktivnih;


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
        /* //Tabela sa novim licencama koja sadrži samo tri polja koja su kljuc
        $nove_licence_index = LicencaNaplata::select(DB::raw('count(*) as `ctn`'), 'terminal_lokacijaId', 'distributerId', 'licenca_distributer_cenaId')
                ->when($this->distId, function ($query) {
                    return $query->where('distributerId', $this->distId);
                })
                ->groupby('terminal_lokacijaId','distributerId', 'licenca_distributer_cenaId')
                ->having('ctn', '=', 1)
                ->get();
        
        //Tabela sa novim licencama full
        $nove_licence_sinle = LicencaNaplata::select('licenca_naplatas.*')
                ->leftJoin('licenca_distributer_cenas', 'licenca_naplatas.licenca_distributer_cenaId', '=', 'licenca_distributer_cenas.id')
                ->leftJoin('licenca_tips', 'licenca_distributer_cenas.licenca_tipId', '=', 'licenca_tips.id')
                ->where('licenca_tips.id', '=', 1) // samo licence tipa ESIR
                ->whereIn('terminal_lokacijaId', $nove_licence_index->pluck('terminal_lokacijaId'))
                ->whereIn('licenca_distributer_cenaId', $nove_licence_index->pluck('licenca_distributer_cenaId'))
                ->when($this->distId, function ($query) {
                    return $query->where('licenca_naplatas.distributerId', $this->distId);
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
                ->get(); */
        
        //Tabela sa novim licencama grupisana po mesecima
        $nove_licence = LicencaNaplata::select(DB::raw('count(id) as `data`'), DB::raw('YEAR(datum_pocetka_licence) year, MONTH(datum_pocetka_licence) month'))
                ->where('nova_licenca', '=', DB::raw("1"))
                ->where('licenca_naziv', '=', DB::raw('"esir"'))
                ->when($this->distId, function ($query) {
                    return $query->where('distributerId', $this->distId);
                })
                ->where('aktivna', '=', DB::raw("1"))
                ->groupby('year','month')
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc')
                ->get();
        
       //Tabela sa svim licencama grupisana po mesecima
        $l_data = LicencaNaplata::select(DB::raw('count(id) as `data`'), DB::raw('YEAR(datum_pocetka_licence) year, MONTH(datum_pocetka_licence) month'))
                ->when($this->distId, function ($query) {
                    return $query->where('distributerId', $this->distId);
                })
                ->where('licenca_naziv', '=', DB::raw('"esir"'))
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
                ->where('licenca_naziv', '=', DB::raw('"esir"'))
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

            //Proizvedene licence
            $item->produzene = ($item->nove > 0) ? $item->data - $item->nove : $item->data;
            
            //Istekle licence
            $item->istekla = $l_data_istek->where('year', $item->year)->where('month', $item->month)->first()->data ?? 0;

            //Labele na X osi
            $item->month = Helpers::nameOfTheMounth($item->year.'-'.$item->month.'-01');
            $item->label = $item->month.' - '. $item->year .' ('.$item->data.')'; //dodavanje broja licenci u labelu

            //Broj licenci
            $this->broj_produzenih += $item->produzene;
            $this->broj_novih += $item->nove;
            $this->broj_istekilh += $item->istekla;
            $this->broj_aktivnih += $item->data;
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
