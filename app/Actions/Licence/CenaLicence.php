<?php

namespace App\Actions\Licence;

use App\Http\Helpers;
use App\Models\KursEvra;
use App\Models\LicencaDistributerCena;
use Livewire\Wireable;

class CenaLicence implements Wireable
{
    public $zeta_cena_eur;
    public $zeta_cena_din;

    public $dist_cena_eur;
    public $dist_cena_din;

    public $cena_evra;

    public function __construct($licenca_distributer_cenas_id = null, $datum_pocetak = null, $datum_kraj = null, $cena_evr_input = 0)
    {
        if ($licenca_distributer_cenas_id !== null) {
            $this->izracunajCenuLicence($licenca_distributer_cenas_id, $datum_pocetak, $datum_kraj, $cena_evr_input);
        }
    }

    public function toLivewire(): array
    {
        return [
            'zeta_cena_eur' => $this->zeta_cena_eur,
            'zeta_cena_din' => $this->zeta_cena_din,
            'dist_cena_eur' => $this->dist_cena_eur,
            'dist_cena_din' => $this->dist_cena_din,
            'cena_evra'     => $this->cena_evra,
        ];
    }

    /** @param array{zeta_cena_eur: float, zeta_cena_din: float, dist_cena_eur: float, dist_cena_din: float, cena_evra: float} $value */
    public static function fromLivewire($value): static
    {
        $instance = new static();
        $instance->zeta_cena_eur = $value['zeta_cena_eur'];
        $instance->zeta_cena_din = $value['zeta_cena_din'];
        $instance->dist_cena_eur = $value['dist_cena_eur'];
        $instance->dist_cena_din = $value['dist_cena_din'];
        $instance->cena_evra     = $value['cena_evra'];
        return $instance;
    }

    /**
     * [Description for izracunajCenuLicence]
     *
     * @param mixed $licenca_distributer_cenas_id
     * @param mixed $datum_pocetak
     * @param mixed $datum_kraj
     *
     * @return [type]
     *
     */
    public function izracunajCenuLicence($licenca_distributer_cenas_id, $datum_pocetak, $datum_kraj, $cena_evr_input=0)
    {
        $licenca_cena_row = LicencaDistributerCena::where('id', '=', $licenca_distributer_cenas_id)->first();
        //$kurs_evra_row = KursEvra::latest()->first();
        $cena_evra_calac = $cena_evr_input ?: KursEvra::latest()->first()->srednji_kurs;
        $this->cena_evra = round( \floatval($cena_evra_calac), 2);

        $zeta_cena = \floatval($licenca_cena_row->licenca_zeta_cena);
        $dist_cena = \floatval($licenca_cena_row->licenca_dist_cena);

        $datum_pocetak_meseca = $datum_pocetak;
        $days_till_end = 0;
        $broj_dana_u_mesecu = Helpers::noOfDaysInMounth($datum_pocetak);
        //da li je licenca od pocetka meseca ili od polovine
        if( Helpers::equalGraterOrLessThan(Helpers::firstDayOfMounth($datum_pocetak), $datum_pocetak) == 'lt'){
            //pocetak je na polovini meseca
            //broj dana do karaja meseca
            $days_till_end = Helpers::numberOfDaysBettwen($datum_pocetak,Helpers::lastDayOfManth($datum_pocetak));
            //pocetak sledeceg meseca
            $datum_pocetak_meseca = Helpers::firstDayOfMounth(Helpers::addMonthsToDate($datum_pocetak, 1));
        }
        $broj_celih_meseci = Helpers::numberOfMounthsBettwen($datum_pocetak_meseca, $datum_kraj) ?: 0;

        //cena licence u evrima ZETA
        $this->zeta_cena_eur = ($days_till_end) ? ($zeta_cena / $broj_dana_u_mesecu) * $days_till_end : 0;
        $this->zeta_cena_eur = round($this->zeta_cena_eur, 2);
        $this->zeta_cena_eur += ($broj_celih_meseci) ? $broj_celih_meseci * $zeta_cena : 0;

        //cena licence u evrima Distributer
        $this->dist_cena_eur = ($days_till_end) ? ($dist_cena / $broj_dana_u_mesecu) * $days_till_end : 0;
        $this->dist_cena_eur = round($this->dist_cena_eur, 2);
        $this->dist_cena_eur += ($broj_celih_meseci) ? $broj_celih_meseci * $dist_cena : 0;

        //cena u dinarima
        $this->zeta_cena_din = round($this->zeta_cena_eur * $this->cena_evra, 2);
        $this->dist_cena_din = round($this->dist_cena_eur * $this->cena_evra, 2);
    }
}
