<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\LicencaParametar;

class LicencaParametarTerminal extends Model
{
    use HasFactory;

    protected $fillable = [
        'terminal_lokacijaId',
        'distributerId',
        'licenca_distributer_cenaId',
        'licenca_distributer_terminalId',
        'licenca_parametarId'
    ];

    /**
     * Dodavanje parametara novoj licenci
     * @param key_arr = [   'terminal_lokacijaId' => $this->modelId,
     *                      'distributerId' => $this->distId,
     *                      'licenca_distributer_cenaId' => $lc ];
     *
     * @param licenca_tip_id  = id licence iz tabele licenca_tips
     * 
     * @param selectedParametars = niz sa selektovanim parametrima iz checkbox-a
     * 
     * @return void
     * 
     */
    public static function addParametarsToLicence($key_arr, $licenca_tip_id, $selectedParametars)
    {
        // izdvoj iz niza izabranih parametara samo parametre koji su za licencu
        $parametars_for_selected_licence = array_intersect($selectedParametars, LicencaParametar::where('licenca_tipId', '=', $licenca_tip_id)->pluck('id')->all());
        foreach($parametars_for_selected_licence as $parametar){
            $key_arr['licenca_parametarId'] = $parametar;
            self::create($key_arr);
        };
    }

    
    /**
     * Update parametara iz dve funkcije
     * @param key_arr = [   'terminal_lokacijaId' => $this->modelId,
     *                      'distributerId' => $this->distId,
     *                      'licenca_distributer_cenaId' => $lc ];
     *
     * @param licenca_tip_id  = id licence iz tabele licenca_tips
     * 
     * @param selectedParametars = niz sa selektovanim parametrima iz checkbox-a
     * 
     * @return void
     * 
     */
    public static function updateParametars($key_arr, $licenca_tip_id, $selectedParametars)
    {
       self::where('terminal_lokacijaId', '=', $key_arr['terminal_lokacijaId'])
            ->where('licenca_distributer_cenaId', '=', $key_arr['licenca_distributer_cenaId'])
            ->where('distributerId', '=', $key_arr['distributerId'])
            ->delete();
        if(count($selectedParametars) == 0) return;
        //novi patrametri iz checkbox-a
        foreach($selectedParametars as $parametar){
            $key_arr['licenca_parametarId'] = $parametar;
            self::create($key_arr);
        };
    }

    public static function deleteParametars($key_arr)
    {
        self::where('terminal_lokacijaId', '=', $key_arr['terminal_lokacijaId'])
            ->where('licenca_distributer_cenaId', '=', $key_arr['licenca_distributer_cenaId'])
            ->where('distributerId', '=', $key_arr['distributerId'])
            ->delete();
    }

    public static function deleteAllParametarsForTerminal($terminal_lokacijaId)
    {
        self::where('terminal_lokacijaId', '=', $terminal_lokacijaId)
            ->delete();
    }
    
}
