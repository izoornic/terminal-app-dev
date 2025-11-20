<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankomatTiketKvarTip extends Model
{
    use HasFactory;

    protected $fillable = [
        'list_id',
        'bakomat_product_tip_id',
        'btkt_naziv',
    ];

    public static function getAll($productTipId)
    {
        return self::where('bakomat_product_tip_id', $productTipId)->pluck('btkt_naziv', 'id')->toArray();
    }
}
