<?php

namespace App\Http\Livewire\Statistika;

use Livewire\Component;
use App\Models\LicencaNaplata;
use Illuminate\Support\Facades\DB;
use App\Http\Helpers;

class LicenceGrafik extends Component
{
    public $distId;
    public $grafikNaslov;
    //public $dist_name;
    
    public $broj_istekilh;
    public $broj_aktivnih;
    public $broj_privremenih;

    public function read()
    {
        $privremene_licence = LicencaNaplata::select(DB::raw('count(id) as `data`'), DB::raw('YEAR(datum_kraj_licence) year, MONTH(datum_kraj_licence) month'))
                ->when($this->distId, function ($query) {
                    return $query->where('distributerId', $this->distId);
                })
                ->where('licenca_naplatas.aktivna', '=', DB::raw("1"))
                ->whereNull('licenca_naplatas.razduzeno')
                ->groupby('year','month')
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc')
                ->get();
        
       /*  $privremene_licence->each(function ($item, $key) {
            $item->month = Helpers::nameOfTheMounth($item->year.'-'.$item->month.'-01');
        }); */

        $l_data = LicencaNaplata::select(DB::raw('count(id) as `data`'), DB::raw('YEAR(datum_kraj_licence) year, MONTH(datum_kraj_licence) month'))
                ->when($this->distId, function ($query) {
                    return $query->where('distributerId', $this->distId);
                })
                ->where('licenca_naplatas.aktivna', '=', DB::raw("1"))
                ->groupby('year','month')
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc')
                ->get();
        
        $l_data->each(function ($item, $key) use ($privremene_licence) {
            $item->privremene = $privremene_licence->where('year', $item->year)->where('month', $item->month)->first()->data ?? 0;
            if($item->privremene > 0){
                $item->data = $item->data - $item->privremene;
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
        return view('livewire.statistika.licence-grafik', [
            'data' => $this->read()
        ]);
    }
}
