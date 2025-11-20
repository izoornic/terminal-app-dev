<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankomatsHistory extends Model
{
    use HasFactory;

    protected $table = 'bankomats_histories';

    protected $fillable = [
        'bankomat_id',
        'bankomat_tip_id',
        'b_sn',
        'b_terminal_id',
        'komentar',
        'created_at',
        'updated_at',
        'vlasnik_uredjaja',
    ];
}
