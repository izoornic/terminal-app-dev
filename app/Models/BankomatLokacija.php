<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Bankomat;
use App\Models\Blokacija;
use App\Models\User;

class BankomatLokacija extends Model
{
    use HasFactory;
    protected $fillable = [
        'bankomat_id',
        'blokacija_id',
        'bankomat_status_tip_id',
        'user_id',
        'naplata',
        'created_at',
        'updated_at'
    ];

    public function bankomat():HasOne
    {
        return $this->hasOne(Bankomat::class, 'id', 'bankomat_id');
    }

    public function blokacija():HasOne
    {
        return $this->hasOne(Blokacija::class);
    }

    public function user():HasOne
    {
        return $this->hasOne(User::class);
    }
}
