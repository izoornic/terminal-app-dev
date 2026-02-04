<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartTransferItem extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'transfer_id',
        'part_type_id',
        'kolicina',
    ];

    protected $casts = [
        'kolicina' => 'integer',
        'created_at' => 'datetime',
    ];

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(PartTransfer::class);
    }

    public function partType(): BelongsTo
    {
        return $this->belongsTo(PartType::class);
    }
}
