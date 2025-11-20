<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankomatStatusTip extends Model
{
    use HasFactory;
    
    protected $fillable = ['status_naziv'];

    public function bankomati()
    {
        return $this->hasMany(Bankomat::class);
    }

    public static function getAll()
    {
        return self::all()->keyBy('id')->pluck('status_naziv', 'id')->toArray();
    }
}
