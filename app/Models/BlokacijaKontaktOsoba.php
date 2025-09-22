<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlokacijaKontaktOsoba extends Model
{
    use HasFactory;
    protected $fillable = [
        'blokacija_id',
        'ime',
        'telefon',
        'email',
    ];
}
