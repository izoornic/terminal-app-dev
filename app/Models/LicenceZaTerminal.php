<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Http\Helpers;

use App\Models\LicencaNaplata;

class LicenceZaTerminal extends Model
{
    use HasFactory;

    protected $fillable = [
        'terminal_lokacijaId',
        'distributerId',
        'licenca_distributer_cenaId',
        'naziv_licence',
        'mesecId',
        'terminal_sn',
        'datum_pocetak',
        'datum_kraj',
        'datum_prekoracenja',
        'licenca_poreklo',
        'signature'
    ];

    public static function sveAktivneLicenceTerminala($terminal_lokacija_id)
    {
        $retval = LicenceZaTerminal::select(
                                'licence_za_terminals.distributerId',
                                'licence_za_terminals.licenca_distributer_cenaId',
                                'naziv_licence',
                                'datum_pocetak',
                                'datum_kraj',
                                'datum_prekoracenja',
                                'licenca_poreklo',
                                'zaduzeno',
                                'razduzeno',
                                'datum_kraj_licence',
                                'datum_isteka_prekoracenja',
                                'dist_zaduzeno',
                                'dist_razduzeno'
                                )
                        ->where('licence_za_terminals.terminal_lokacijaId', '=', $terminal_lokacija_id)
                        ->leftJoin('licenca_naplatas', function($join)
                        {
                            $join->on('licence_za_terminals.terminal_lokacijaId', '=', 'licenca_naplatas.terminal_lokacijaId');
                            $join->on('licence_za_terminals.distributerId', '=', 'licenca_naplatas.distributerId');
                            $join->on('licence_za_terminals.licenca_distributer_cenaId', '=', 'licenca_naplatas.licenca_distributer_cenaId');
                            $join->on('licenca_naplatas.aktivna', '=', DB::raw("1"));
                        })
                        ->get();

        
        return $retval;
    }

    public static function deleteExpiredServiceLicences()
    {
        //$istekLimit = Helpers::addDaysToDate(Helpers::datumKalendarNow(), -3);
        // Brise se dan posle isteka na ne 3 dana kasnije...
        $istekLimit = Helpers::datumKalendarNow();

        $retval = LicenceZaTerminal::where('datum_prekoracenja', '<', $istekLimit)
                                    ->where('licenca_poreklo', '=', 2)
                                    ->delete();
        return $retval;
    }

}

