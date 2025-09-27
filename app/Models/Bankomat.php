<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Bankomat extends Model
{
    use HasFactory;
    protected $fillable = [
        'bankomat_tip_id',
        'b_sn',
        'b_terminal_id',
        'komentar',
    ];

    public function bankomat_tip()
    {
        return $this->belongsTo(BankomatTip::class);
    }

    public function bankomat_produkt_tip():HasOneThrough
    {
        return $this->hasOneThrough(
            BankomatProductTip::class, 
            BankomatTip::class, 
            'id',                       // Foreign key on the BankomatTip table
            'id',                       // Foreign key on the BankomatProductTip table
            'bankomat_tip_id',          // Local key on the Bankomat table..
            'bankomat_produkt_tip_id'   // Local key on the BankomatTip table
        );
    }

}
