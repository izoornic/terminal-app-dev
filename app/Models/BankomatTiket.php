<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\HasOneThrough;

use App\Models\BankomatTiketKomantar;
use App\Models\BankomatLokacija;
use App\Models\Blokacija;
use App\Models\BankomatTiketKvarTip;
use App\Models\BankomatTiketPrioritetTip;
use App\Models\BankomatTiketHistory;

class BankomatTiket extends Model
{
    use HasFactory;

    protected $fillable = [
        'bankomat_lokacija_id',
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
            'bankomat_lokacija_id',     // Local key on the BankomatTiket table..
            'blokacija_id'              // Local key on the BankomatLokacija table
        );
    }

    public function kvarTip()
    {
        return $this->belongsTo(BankomatTiketKvarTip::class, 'bankomat_tiket_kvar_tip_id', 'id');
    }

    public function prioritet()
    {
        return $this->belongsTo(BankomatTiketPrioritetTip::class, 'bankomat_tiket_prioritet_id', 'id');
    }

    public function newHistroy($action) {
        $new_histroy = [
            'bankomat_tiket_id' => $this->id,
            'bankomat_lokacija_id' => $this->bankomat_lokacija_id,
            'status' => $this->status,
            'bankomat_tiket_kvar_tip_id' => $this->bankomat_tiket_kvar_tip_id,
            'bankomat_tiket_prioritet_id' => $this->bankomat_tiket_prioritet_id,
            'opis' => $this->opis,
            'user_prijava_id' => $this->user_prijava_id,
            'user_dodeljen_id' => $this->user_dodeljen_id,
            'user_zatvorio_id' => $this->user_zatvorio_id,
            'br_komentara' => $this->br_komentara,
            'action_id' => $action,
        ];
        BankomatTiketHistory::create($new_histroy);
    }
}
