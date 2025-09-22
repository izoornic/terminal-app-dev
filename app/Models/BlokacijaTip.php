<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlokacijaTip extends Model
{
    use HasFactory;
    protected $fillable = ['bl_naziv'];

    public static function getAll()
    {
        return self::all()->keyBy('id')->pluck('bl_tip_naziv', 'id')->toArray();
    }
}
