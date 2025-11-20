<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BankomatTip extends Model
{
    use HasFactory;

    protected $fillable = [
        'bankomat_produkt_tip_id',
        'model',
        'proizvodjac',
        'opis',
    ];

    public function bankomati()
    {
        return $this->hasMany(Bankomat::class);
    }

    public static function getAllFromCategory($id)
    {
        return self::where('bankomat_produkt_tip_id', $id)->get()->pluck('model', 'id')->toArray();
    }

    public function bankomat_product_tip():HasOne
    {
        return $this->HasOne(BankomatProductTip::class, 'id', 'bankomat_produkt_tip_id');
    }
}
