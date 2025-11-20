<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankomatRegion extends Model
{
    use HasFactory;
    protected $fillable = ['r_naziv'];

    public static function getAll()
    {
        return self::all()->keyBy('id')->pluck('r_naziv', 'id')->toArray();
    }
}
