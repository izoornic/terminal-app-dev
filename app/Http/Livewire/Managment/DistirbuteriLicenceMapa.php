<?php

namespace App\Http\Livewire\Managment;

use Livewire\Component;

use App\Models\LicencaDistributerTip;
use App\Models\LicencaNaplata;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class DistirbuteriLicenceMapa extends Component
{
    public $pins = [];
    //public $pin_colors = [];
    public $pin_numbers = [];

    //public $terminal_count_colors_ico;
    //public $terminal_count_numbers_ico;
    public $fiterMeseci;
    public $filterMeseciValue = '-3 months'; // Default filter value

    /**
     * Mount the component and initialize properties.
     */
    public function mount()
    {
        if(session('filterMeseciValue')) {
            $this->filterMeseciValue = session('filterMeseciValue');
        }
        // Initialize the pin colors array
        //$this->pin_colors = Config::get('global.pin_colors'); 
        // Initialize the terminal count pin icons with specific colors 
        $this->pin_numbers = Config::get('global.pin_red_numbers');

       /*  $this->terminal_count_numbers_ico = [
            1 => $this->pin_numbers['lightyellow'],
            2 => $this->pin_numbers['gray'],
            3 => $this->pin_numbers['yellow'],
            4 => $this->pin_numbers['lightred'],
            5 => $this->pin_numbers['lightgreen'],
            10 => $this->pin_numbers['lightblue'],
            15 => $this->pin_numbers['orange'],
            20 => $this->pin_numbers['red'],
            25 => $this->pin_numbers['green'],
            30 => $this->pin_numbers['blue'],
            40 => $this->pin_numbers['pink'],
            50 => $this->pin_numbers['brown'],
            100 => $this->pin_numbers['purple'],
        ]; */

        /*  $this->terminal_count_colors_ico = [
            1 => $this->pin_colors['lightyellow'],
            2 => $this->pin_colors['gray'],
            3 => $this->pin_colors['yellow'],
            4 => $this->pin_colors['lightred'],
            5 => $this->pin_colors['lightgreen'],
            10 => $this->pin_colors['lightblue'],
            15 => $this->pin_colors['orange'],
            20 => $this->pin_colors['red'],
            25 => $this->pin_colors['green'],
            30 => $this->pin_colors['blue'],
            40 => $this->pin_colors['pink'],
            50 => $this->pin_colors['brown'],
            100 => $this->pin_colors['purple'],
        ]; */

        $this->fiterMeseci = [
            '-3 months' => '3 meseca',
            '-6 months' => '6 meseci',
            '-9 months' => '9 meseci',
            '-12 months' => '12 meseci',
            '-18 months' => '18 meseci',
        ];
        //$this->read(); // Call the read method to populate the pins array
    }

    /**
     * updated
     *
     * @param  mixed $key
     * @param  mixed $value
     * @return void
     */
    public function updated($key, $value)
    {
        // Handle updates to the component properties
        if ($key === 'filterMeseciValue') {
            if(session('filterMeseciValue') !== $value) {
                session(['filterMeseciValue' => $value]); // Store the value in the session
                return redirect(request()->header('Referer'));
            }
        }
    }

    /**
     * Read data and populate the pins array.
     *
     * This method should be called to fetch and process the data needed for the map pins.
     */
    public function read()
    {
        $this->pins = []; // Initialize the pins array
         // This method should be implemented to fetch and populate the pins array
        // with data from the database or any other source.
        // Example:
        // $this->pins = LicencaNaplata::all();
       $ldat = LicencaDistributerTip::select(
            'licenca_distributer_tips.distributer_naziv',
            'licenca_distributer_tips.id',
            'lokacijas.l_naziv',
            'lokacijas.mesto',
            'lokacijas.adresa',
            'lokacijas.id as lokacija_id',
            'lokacijas.latitude as lat',
            'lokacijas.longitude as long',
            DB::raw('COUNT(licenca_naplatas.id) as nove_count'),
        )
            ->leftJoin('distributer_lokacija_indices', 'licenca_distributer_tips.id', '=', 'distributer_lokacija_indices.licenca_distributer_tipsId')
            ->leftJoin('lokacijas', 'distributer_lokacija_indices.lokacijaId', '=', 'lokacijas.id')
            ->leftJoin('licenca_naplatas', 'licenca_distributer_tips.id', '=', 'licenca_naplatas.distributerId')
            ->where('licenca_naplatas.nova_licenca', '=', 1)
            //->where('licenca_naplatas.aktivna', '=', 1)
            ->where('licenca_naplatas.licenca_naziv', '=', DB::raw('"esir"'))
            ->where('licenca_naplatas.datum_pocetka_licence', '>=', now()->modify($this->filterMeseciValue)->format('Y-m-01'))
            //->where('licenca_naplatas.datum_pocetka_licence', '<', now()->modify('-8 months')->format('Y-m-01'))
            ->where('licenca_distributer_tips.id', '!=', 2) // Example condition, adjust as needed
            ->groupBy('licenca_distributer_tips.id')
            ->get();



        $ldatAll = LicencaDistributerTip::select(
            'licenca_distributer_tips.distributer_naziv',
            'licenca_distributer_tips.id',
            'lokacijas.l_naziv',
            'lokacijas.mesto',
            'lokacijas.adresa',
            'lokacijas.id as lokacija_id',
            'lokacijas.latitude as lat',
            'lokacijas.longitude as long'
        )
            ->leftJoin('distributer_lokacija_indices', 'licenca_distributer_tips.id', '=', 'distributer_lokacija_indices.licenca_distributer_tipsId')
            ->leftJoin('lokacijas', 'distributer_lokacija_indices.lokacijaId', '=', 'lokacijas.id')
            ->where('licenca_distributer_tips.id', '!=', 2)
            ->get()
            // Process the fetched data to populate the pins array
            ->each(function ($pin) use( $ldat) {
                // Ensure that the pin is valid
                if(!$pin->lat || !$pin->long) {
                    return; // Skip invalid pins
                }
                $nove_count = $ldat->where('id', $pin->id)->first();
                if($nove_count) {
                    $pin->nove_count = $nove_count->nove_count;
                } else {
                    $pin->nove_count = 0; // Default to 0 if no count found
                }
                $this->pins[] = [
                    'lat' => $pin->lat,
                    'long' => $pin->long,
                    'icon' => $this->GetMyPinNumberIcon($pin->nove_count),
                    'terminal_count' => $pin->nove_count, 
                    //'terminal_sn' => [1,2,3], // Example terminal serial numbers, adjust as needed
                    'info' => json_encode([
                                'p_name' => $pin->distributer_naziv,
                                'address' => $pin->adresa.' '.$pin->mesto,
                                'ko_tel' => ' ',
                                'ko_name' => 'Ukupno novih licenci: '.$pin->nove_count,
                            ])
                    ];
            });

            //dd($this->pins); // Debugging line to check the pins data
    }

    /* private function GetMyPinIcon($count)
    {
       foreach ($this->terminal_count_colors_ico as $key => $icon) {
            if ($count <= $key) {
                return $icon; // Return the icon for the first matching count
            }
        }
        return $this->pin_colors['red']; // Default icon if no match found
    } */

    private function GetMyPinNumberIcon($count)
    {
       foreach ($this->pin_numbers as $key => $icon) {
            if ($count === $key) {
                return $icon; // Return the icon for the first matching count
            }
        }
        if($count > 15) {
            return $this->pin_numbers[16]; // Return red icon for counts greater than 15
        }
        return $this->pin_numbers[0]; // Default icon if no match found
    }


    public function render()
    {
        return view('livewire.managment.distirbuteri-licence-mapa', [
            'data' => $this->read(),
        ]);
    }
}
