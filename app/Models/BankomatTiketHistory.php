<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankomatTiketHistory extends Model
{
    use HasFactory;

     protected $fillable = [
        'bankomat_tiket_id',
        'bankoamt_lokacija_id',
        'status',
        'bankomat_tiket_kvar_tip_id',
        'bankomat_tiket_prioritet_id',
        'opis',
        'user_prijava_id',
        'user_dodeljen_id',
        'user_zatvorio_id',
        'br_komentara',
    ];
}
