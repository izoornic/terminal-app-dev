<?php

namespace App\Http\Livewire\Statistika;

use Livewire\Component;
use App\Models\LicenceZaTerminal;
use Illuminate\Support\Facades\DB;
use App\Http\Helpers;

class LicenceGrafikApi extends Component
{
    public $distId;
    public $grafikNaslov;
    //public $dist_name;
    
    public $broj_istekilh;
    public $broj_aktivnih;
    public $broj_privremenih;

    public function read()
    {
        $privremena_data = LicenceZaTerminal::select(DB::raw('count(id) as `data`'), DB::raw('YEAR(datum_kraj) year, MONTH(datum_kraj) month'))
                ->when($this->distId, function ($query) {
                    return $query->where('distributerId', $this->distId);
                })
                ->where('licence_za_terminals.licenca_poreklo', '=', DB::raw("3"))
                ->groupby('year','month')
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc')
                ->get();

        $lic_data = LicenceZaTerminal::select(DB::raw('count(id) as `data`'), DB::raw('YEAR(datum_kraj) year, MONTH(datum_kraj) month'))
                ->when($this->distId, function ($query) {
                    return $query->where('distributerId', $this->distId);
                })
                ->where('licence_za_terminals.licenca_poreklo', '<>', DB::raw("2"))
                ->groupby('year','month')
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc')
                ->get();

        $lic_data->each(function ($item, $key) use ($privremena_data) {
            $item->privremene = $privremena_data->where('year', $item->year)->where('month', $item->month)->first()->data ?? 0;
            if($item->privremene > 0){
                $item->data = $item->data - $item->privremene;
            }
            $item->istekla = Helpers::dateGratherOrEqualThan($item->year.'-'.$item->month.'-01', Helpers::datumKalendarNow());
            $item->month = Helpers::nameOfTheMounth($item->year.'-'.$item->month.'-01');

            $this->broj_istekilh += $item->istekla ? 0 : $item->data;
            $this->broj_aktivnih += $item->istekla ? $item->data : 0;
            $this->broj_privremenih += $item->privremene;
        });

        return $lic_data;

    }

    public function render()
    {
        return view('livewire.statistika.licence-grafik-api', [
            'data' => $this->read()
        ]);
    }
}
