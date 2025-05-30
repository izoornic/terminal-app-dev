<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LicencaNaplata extends Model
{
    use HasFactory;

    protected $fillable = [
        'terminal_lokacijaId',
        'distributerId',
        'licenca_distributer_cenaId',
        'nova_licenca',
        'mesecId',
        'licenca_naziv',
        'terminal_sn',
        'zaduzeno',
        'datum_zaduzenja',
        'razduzeno',
        'datum_razduzenja',
        'datum_pocetka_licence',
        'datum_kraj_licence',
        'datum_isteka_prekoracenja',
        'dist_zaduzeno',
        'dist_datum_zaduzenja',
        'dist_razduzeno',
        'dist_datum_razduzenja',
        'aktivna',
        'nenaplativ'
    ];

    protected $casts = [
        'datum_pocetka_licence' => 'datetime:d-m-Y',
        'datum_kraj_licence' => 'datetime:d-m-Y',
    ];
}
