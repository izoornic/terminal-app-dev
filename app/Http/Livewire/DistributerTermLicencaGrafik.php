<?php

namespace App\Http\Livewire;

use Livewire\Component;

use App\Models\LicencaDistributerTip;
use App\Models\LicencaNaplata;
use App\Models\LicenceZaTerminal;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

use App\Http\Helpers;

class DistributerTermLicencaGrafik extends Component
{
    //set on MOUNT
    public $distId;
    public $dist_name;
    public $ditributer_info;
    public $broj_terminala;
    public $broj_istekilh;
    public $broj_aktivnih;
    public $broj_privremenih;

    public $broj_istekilh_2;
    public $broj_aktivnih_2;
    public $broj_privremenih_2;

    /**
     * mount
     *
     * @return void
     */
    public function mount()
    {
        $this->distId = request()->query('id');
        $this->ditributer_info = LicencaDistributerTip::where('id', '=', $this->distId)->first();
        $this->dist_name = $this->ditributer_info->distributer_naziv;
        $this->broj_terminala = $this->ditributer_info->broj_terminala;
        $this->broj_aktivnih = 0;
        $this->broj_istekilh = 0;
        $this->broj_privremenih = 0;

        $this->broj_aktivnih_2 = 0;
        $this->broj_istekilh_2 = 0;
        $this->broj_privremenih_2 = 0;
    }

    public function read()
    {
        $privremene_licence = LicencaNaplata::select(DB::raw('count(id) as `data`'), DB::raw('YEAR(datum_kraj_licence) year, MONTH(datum_kraj_licence) month'))
                ->where('distributerId', $this->distId)
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
                ->where('distributerId', $this->distId)
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

    public function read2()
    {
        $privremena_data = LicenceZaTerminal::select(DB::raw('count(id) as `data`'), DB::raw('YEAR(datum_kraj) year, MONTH(datum_kraj) month'))
                ->where('distributerId', $this->distId)
                ->where('licence_za_terminals.licenca_poreklo', '=', DB::raw("3"))
                ->groupby('year','month')
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc')
                ->get();

        $lic_data = LicenceZaTerminal::select(DB::raw('count(id) as `data`'), DB::raw('YEAR(datum_kraj) year, MONTH(datum_kraj) month'))
                ->where('distributerId', $this->distId)
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

            $this->broj_istekilh_2 += $item->istekla ? 0 : $item->data;
            $this->broj_aktivnih_2 += $item->istekla ? $item->data : 0;
            $this->broj_privremenih_2 += $item->privremene;
        });

        return $lic_data;

    }

    public function render()
    {
        //dd($this->read2());
        return view('livewire.distributer-term-licenca-grafik', [
            'data' => $this->read(),
            'data2' => $this->read2()
        ]);
    }
}
