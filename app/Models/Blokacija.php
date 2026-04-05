<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\BlokacijaKontaktOsoba;
use App\Models\BankomatLokacija;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Blokacija extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
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

    public function region()
    {
        return $this->belongsTo(BankomatRegion::class);
    }

    public function parent()
    {
        return $this->belongsTo(Blokacija::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Blokacija::class, 'parent_id');
    }

    public function bankomatLokacija()
    {
        return $this->belongsTo(BankomatLokacija::class, 'blokacija_id');
    }
}
