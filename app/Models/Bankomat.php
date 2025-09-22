<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bankomat extends Model
{
    use HasFactory;
    protected $fillable = [
        'bankomat_tip_id',
        'b_sn',
        'b_terminal_id',
        'komentar',
    ];
}
