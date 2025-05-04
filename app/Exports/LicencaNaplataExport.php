<?php

namespace App\Exports;

use App\Models\LicencaNaplata;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Http\Helpers;

class LicencaNaplataExport implements FromCollection, WithHeadings
{
    private $distributer_id;

    public function __construct($distributer_id)
    {
        $this->distributer_id = $distributer_id;
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection() 
    {
        $datum_filter = Helpers::datumKalendarNow();
        $datum_filter = date('Y-m-d', strtotime($datum_filter . ' + 1 month'));
        
        return LicencaNaplata::select(
                                        'terminals.sn', 
                                        'lokacijas.l_naziv', 
                                        'lokacijas.pib',
                                        'lokacijas.adresa', 
                                        'lokacijas.mesto',  
                                        'licenca_tips.licenca_naziv',
                                        'licenca_naplatas.datum_pocetka_licence', 
                                        'licenca_naplatas.datum_kraj_licence', 
                                        'licenca_naplatas.dist_zaduzeno',
                                        'licenca_naplatas.dist_razduzeno',
                                        'licenca_naplatas.zaduzeno',
                                        'licenca_naplatas.razduzeno'
                                )
                                ->join('terminal_lokacijas', 'terminal_lokacijas.id', '=', 'licenca_naplatas.terminal_lokacijaId')
                                ->join('terminals', 'terminals.id', '=', 'terminal_lokacijas.terminalId')
                                ->join('lokacijas', 'lokacijas.id', '=', 'terminal_lokacijas.lokacijaId')
                                ->join('licenca_distributer_cenas', 'licenca_distributer_cenas.id', '=', 'licenca_naplatas.licenca_distributer_cenaId')
                                ->join('licenca_tips', 'licenca_tips.id', '=', 'licenca_distributer_cenas.licenca_tipId')
                                ->where('licenca_naplatas.distributerId', '=', $this->distributer_id)
                                ->where('licenca_naplatas.datum_kraj_licence', '<=', $datum_filter)
                                ->where('aktivna', '=', '1')
                                ->orderBy('licenca_naplatas.datum_kraj_licence', 'asc')
                                ->get();
    }

    public function headings(): array
    {
        return [
            'SN',
            'Korisnik',
            'Pib',
            'Adresa',
            'Mesto',
            'Licenca',
            'Početak licence',
            'Kraj licence',
            'Distributer zadužen',
            'Distributer razdužen',
            'Zeta zadužeo',
            'Zeta razduženo'
        ];
    }
}
