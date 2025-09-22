<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankomatLocijaHirtory extends Model
{
    use HasFactory;

    protected $fillable = [
        'bankomat_lokacija_id',
        'bankomat_id',
        'blokacija_id',
        'bankomat_status_tip_id',
        'user_id'
    ];

}
