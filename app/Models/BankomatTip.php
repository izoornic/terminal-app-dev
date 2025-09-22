<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankomatTip extends Model
{
    use HasFactory;

    protected $fillable = [
        'model',
        'proizvodjac',
        'opis',
    ];

    public function bankomati()
    {
        return $this->hasMany(Bankomat::class);
    }

    public static function getAll()
    {
        return self::all()->keyBy('id')->pluck('model', 'id')->toArray();
    }
}
