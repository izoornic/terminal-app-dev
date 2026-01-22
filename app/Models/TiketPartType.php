<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TiketPartType extends Model
{
    use HasFactory;

    protected $table = 'tiket_part_types';

    protected $fillable = [ 
        'tiket_id',
        'part_type_id',
        'part_location_id',
        'user_id',
        'terminal_lokacija_id',
        'quantity',
    ];

    public function partType()
    {
        return $this->belongsTo(PartType::class);
    }
}
