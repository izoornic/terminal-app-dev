<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankomatProductTip extends Model
{
    use HasFactory;

    protected $fillable = [
        'bp_tip_naziv',
    ];

    public static function getAll()
    {
        return self::all()->keyBy('id')->pluck('bp_tip_naziv', 'id')->toArray();
    }

    public static function getName($id)
    {
        return self::where('id', $id)->first()->bp_tip_naziv;
    } 

}
