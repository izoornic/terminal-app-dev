<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankomatTiketPrioritetTip extends Model
{
    use HasFactory;

    protected $fillable = [
        'btpt_naziv',
        'btpt_opis',
        'btn_collor',
        'btn_hover_collor',
        'tr_bg_collor',
    ];

    public static function prList()
    {
        return  self::all();
    }

    public static function prioritetInfo($id)
    {
        return self::where('id', $id)->first();
    }

}
