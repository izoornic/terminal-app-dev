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
    
    public $broj_istekilh;
    public $broj_aktivnih;
    public $broj_privremenih;

    public function read()
    {
        $nove_licence_index = LicencaNaplata::select(DB::raw('count(*) as `ctn`'), 'terminal_lokacijaId', 'distributerId', 'licenca_distributer_cenaId')
                ->when($this->distId, function ($query) {
                    return $query->where('distributerId', $this->distId);
                })
                ->groupby('terminal_lokacijaId','distributerId', 'licenca_distributer_cenaId')
                ->having('ctn', '=', 1)
                ->get();
        
        $nove_licence_sinle = LicencaNaplata::whereIn('terminal_lokacijaId', $nove_licence_index->pluck('terminal_lokacijaId'))
                ->whereIn('licenca_distributer_cenaId', $nove_licence_index->pluck('licenca_distributer_cenaId'))
                ->when($this->distId, function ($query) {
                    return $query->where('distributerId', $this->distId);
                })
                ->where('licenca_naplatas.aktivna', '=', DB::raw("1"))
                ->get();
        

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
        
       //dd($nove_licence);

        $l_data = LicencaNaplata::select(DB::raw('count(id) as `data`'), DB::raw('YEAR(datum_pocetka_licence) year, MONTH(datum_pocetka_licence) month'))
                ->when($this->distId, function ($query) {
                    return $query->where('distributerId', $this->distId);
                })
                ->where('licenca_naplatas.aktivna', '=', DB::raw("1"))
                ->groupby('year','month')
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc')
                ->get();
        
        $l_data->each(function ($item, $key) use ($nove_licence) {
            $item->nove = $nove_licence->where('year', $item->year)->where('month', $item->month)->first()->data ?? 0;
            if($item->nove > 0){
                $item->data = $item->data - $item->nove;
            }
            $item->istekla = Helpers::dateGratherOrEqualThan($item->year.'-'.$item->month.'-01', Helpers::datumKalendarNow());
            $item->month = Helpers::nameOfTheMounth($item->year.'-'.$item->month.'-01');

            $this->broj_istekilh += $item->istekla ? 0 : $item->data;
            $this->broj_aktivnih += $item->istekla ? $item->data : 0;
            $this->broj_privremenih += $item->privremene;
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
