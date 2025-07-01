<?php

namespace App\Http\Livewire\Managment;

use Livewire\Component;
use App\Models\LicencaDistributerTip;
use App\Models\LicencaNaplata;
use Illuminate\Support\Facades\DB;

class DistributerTerminaliMapa extends Component
{
    //set on MOUNT
    public $distId;
    public $dist_name;

    public $pins = [];
    public $pin_colors = [];

    public function mount()
    {
        $this->distId = request()->query('id') ?? 0;
        $this->dist_name = LicencaDistributerTip::where('id', '=', $this->distId)->first()->distributer_naziv ?? 'Sve licence';

        // Initialize the pin colors array with some example data
        $this->pin_colors = [
            'red'           => 'img/map-pins/red-dot.png',
            'blue'          => 'img/map-pins/blue-dot.png',
            'green'         => 'img/map-pins/green-dot.png',
            'orange'        => 'img/map-pins/orange-dot.png',
            'purple'        => 'img/map-pins/purple-dot.png',
            'brown'         => 'img/map-pins/brown-dot.png',
            'gray'          => 'img/map-pins/gray-dot.png',
            'yellow'        => 'img/map-pins/yellow-dot.png',
            'pink'          => 'img/map-pins/pink-dot.png',
            'lightblue'     => 'img/map-pins/lightblue-dot.png',
            'lightred'      => 'img/map-pins/lightred-dot.png',
            'lightgreen'    => 'img/map-pins/lightgreen-dot.png',
            'lightyellow'   => 'img/map-pins/lightyellow-dot.png',
        ];

        $this->read(); // Call the read method to populate the pins array
    }  

    public function read()
    {
        $l_pins = LicencaNaplata::select(  
                'licenca_naplatas.*',
                'lokacijas.l_naziv', 
                'lokacijas.mesto', 
                'lokacijas.adresa',
                'lokacijas.id as lokacija_id',
                'lokacijas.latitude as lat',
                'lokacijas.longitude as long',
                'lokacija_kontakt_osobas.tel',
                'lokacija_kontakt_osobas.name as ko_name',
                DB::raw('CONCAT(lokacijas.adresa, ", ", lokacijas.mesto) as full_address'),
            )
            ->LeftJoin('terminal_lokacijas', 'licenca_naplatas.terminal_lokacijaId', '=', 'terminal_lokacijas.id')
            ->leftJoin('lokacijas', 'terminal_lokacijas.lokacijaId', '=', 'lokacijas.id')
            ->leftJoin('lokacija_kontakt_osobas', 'lokacijas.id', '=', 'lokacija_kontakt_osobas.lokacijaId')
            ->where('licenca_naplatas.aktivna', '=', DB::raw("1"))
            ->where('licenca_naplatas.licenca_naziv', '=', DB::raw('"esir"'))
            ->when($this->distId, function ($query) {
                    return $query->where('licenca_naplatas.distributerId', '=', $this->distId);
                })
            ->orderBy('lokacijas.id', 'asc')
            ->get();
        //dd($l_pins); // Debugging line to check the pins data

        $this->pins = []; // Reset the pins array
        $p_lokacija_id = null; // Initialize the location ID variable
        $terminal_count = 1; // Initialize terminal count
        $l_pin = [];
        // Loop through each pin and extract the necessary data
        foreach ($l_pins as $pin) {
            // Each pin can have a latitude, longitude, icon, and optional info
            if(!$pin->lat || !$pin->long) {
                continue; // Skip pins without latitude or longitude
            }
            // If the pin's location ID is the same as the previous one
            if($p_lokacija_id != $pin->lokacija_id) {
                if($l_pin) {
                    // Set the icon based on the terminal count
                    if($l_pin['terminal_count'] > 1) {
                        $l_pin['icon'] = $this->pin_colors['blue']; // Change icon if multiple terminals at the same location
                    } else {
                        $l_pin['icon'] = $this->pin_colors['red']; // Default icon for single terminal
                    }

                    $l_pin['terminal_sn'] = array_unique($l_pin['terminal_sn']); // Ensure terminal_sn is unique
                    $l_pin['terminal_sn'] = implode(', ', $l_pin['terminal_sn']); // Convert terminal_sn array to a comma-separated string
                    $l_pin['info']['terminal_sn'] = $l_pin['terminal_sn']; // Add terminal_sn to info
                    unset($l_pin['terminal_sn']); // Remove terminal_sn from the pin array
                    
                    $l_pin['info']['terminal_count'] = $l_pin['terminal_count']; // Add terminal count to info
                    unset($l_pin['terminal_count']); // Remove terminal_count from the pin array
                    // Convert info to JSON string
                    $l_pin['info'] = json_encode($l_pin['info']); // Convert info to JSON string
                    // Add the pin to the pins array
                    $this->pins[] = $l_pin;
                    $l_pin = []; // Reset the pin array for the next pin
                }
                //novi pin
                $terminal_count = 1; // Reset terminal count for new location
                // Create a new pin with the current location's data
                $l_pin = [
                    'lat' => $pin->lat,
                    'long' => $pin->long,
                    'icon' => $this->pin_colors['red'], // You can customize the icon
                    'terminal_count' => 1, // Reset terminal count for new location
                    'terminal_sn' => [$pin->terminal_sn],
                    'info' => [
                                'p_name' => $pin->l_naziv,
                                'address' => $pin->full_address,
                                'ko_tel' => $pin->tel ?? '',
                                'ko_name' => $pin->ko_name ?? '',
                            ]
                    ];
                
            }else{
                // If you want to keep track of how many terminals are at the same location, you can do so here
                $terminal_count++;
                $l_pin['terminal_count'] = $terminal_count;
                // Add the terminal serial number to the pin's terminal_sn array
                array_push($l_pin['terminal_sn'], $pin->terminal_sn); // Add the terminal serial number to the array
            }
            $p_lokacija_id = $pin->lokacija_id;
        }
        // After the loop, if there's a pin left, push it to the pins array
        if($l_pin) {
            // If we have a previous pin, push it to the pins array
                    // Set the icon based on the terminal count
                    if($l_pin['terminal_count'] > 1) {
                        $l_pin['icon'] = $this->pin_colors['blue']; // Change icon if multiple terminals at the same location
                    } else {
                        $l_pin['icon'] = $this->pin_colors['red']; // Default icon for single terminal
                    }

                    $l_pin['terminal_sn'] = array_unique($l_pin['terminal_sn']); // Ensure terminal_sn is unique
                    $l_pin['terminal_sn'] = implode(', ', $l_pin['terminal_sn']); // Convert terminal_sn array to a comma-separated string
                    $l_pin['info']['terminal_sn'] = $l_pin['terminal_sn']; // Add terminal_sn to info
                    unset($l_pin['terminal_sn']); // Remove terminal_sn from the pin array
                    
                    $l_pin['info']['terminal_count'] = $l_pin['terminal_count']; // Add terminal count to info
                    unset($l_pin['terminal_count']); // Remove terminal_count from the pin array
                    // Convert info to JSON string
                    $l_pin['info'] = json_encode($l_pin['info']); // Convert info to JSON string
                    // Add the pin to the pins array
                    $this->pins[] = $l_pin;
                    $l_pin = []; // Reset the pin array for the next pin
        }
        //dd($this->pins); // Debugging line to check the pins data
    }

    
    public function render()
    {
        return view('livewire.managment.distributer-terminali-mapa');
    }
}
