<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LicencaSignLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'terminal_sn',
        'signature'
    ];
}
