<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LicencaServisna extends Model
{
    use HasFactory;

    protected $fillable = [
        'userId',
        'terminal_lokacijaId',
        'distributerId',
        'licenca_distributer_cenaId',
        'datum_pocetka_licence',
        'datum_kraj_licence',
        'datum_isteka_prekoracenja'
    ];
}
