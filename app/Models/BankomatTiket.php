<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class BankomatTiket extends Model
{
    use HasFactory;

    protected $fillable = [
        'bankoamt_lokacija_id',
        'status',
        'bankomat_tiket_kvar_tip_id',
        'bankomat_tiket_prioritet_id',
        'opis',
        'user_prijava_id',
        'user_dodeljen_id',
        'user_zatvorio_id',
        'br_komentara',
    ];

    public function komentari()
    {
        return $this->hasMany(BankomatTiketKomantar::class);
    }

    public function lokacija() :HasOneThrough
    {
        return $this->hasOneThrough(
            Blokacija::class, 
            BankomatLokacija::class, 
            'id',                       // Foreign key on the BankomatLokacija table
            'id',                       // Foreign key on the Blokacija table
            'bankoamt_lokacija_id',     // Local key on the BankomatTiket table..
            'blokacija_id'              // Local key on the BankomatLokacija table
        );
    }

    public function kvarTip()
    {
        return $this->belongsTo(TiketOpisKvaraTip::class);
    }

    public function prioritet()
    {
        return $this->belongsTo(TiketPrioritetTip::class);
    }
}
