<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankomatTiketKomantar extends Model
{
    use HasFactory;

    protected $fillable = [
        'bankomat_tiket_id',
        'user_id',
        'komentar',
    ];

}
