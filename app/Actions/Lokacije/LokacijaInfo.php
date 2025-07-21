<?php

namespace App\Actions\Lokacije;
use App\Models\Lokacija;

class LokacijaInfo
{
    public static function getInfo($lokacijaId)
    {
        return Lokacija::select('lokacijas.*', 'lokacija_tips.lt_naziv', 'regions.r_naziv')
        ->leftJoin('lokacija_tips', 'lokacijas.lokacija_tipId', '=', 'lokacija_tips.id')
        ->leftJoin('regions', 'regions.id', '=', 'lokacijas.regionId')
        ->where('lokacijas.id', '=', $lokacijaId)
        ->first();
    }
}