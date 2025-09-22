<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\BlokacijaKontaktOsoba;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Blokacija extends Model
{
    use HasFactory;

    protected $fillable = [
        'is_duplicate',
        'bankomat_region_id',
        'blokacija_tip_id',
        'bl_naziv',
        'bl_naziv_sufix',
        'bl_adresa',
        'bl_mesto',
        'pib',
        'mb',
        'email',
        'latitude',
        'longitude',
    ];

    public static function lokacijeTipa($tip_id = 1)
    {
        return self::where('blokacija_tip_id', $tip_id)->get();
    }

    public static function lokacijeTipLista($tip_id = 1)
    {
        return self::where('blokacija_tip_id', $tip_id)->pluck('bl_naziv', 'id')->toArray();
    }

    public function kontaktOsobe():HasMany
    {
        return $this->hasMany(BlokacijaKontaktOsoba::class);
    }
}
