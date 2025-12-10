<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Lokacija;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartReservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'part_type_id',
        'lokacija_id',
        'kolicina',
        'korisnik_id',
        'status',
        'radni_nalog_id',
        'rezervisano_do',
    ];

    protected $casts = [
        'kolicina' => 'integer',
        'rezervisano_do' => 'datetime',
    ];

    const STATUS_AKTIVNA = 'AKTIVNA';
    const STATUS_ISKORISCENA = 'ISKORISCENA';
    const STATUS_OTKAZANA = 'OTKAZANA';

    public function partType(): BelongsTo
    {
        return $this->belongsTo(PartType::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Lokacija::class, 'lokacija_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'korisnik_id');
    }

    // Scope za aktivne rezervacije
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_AKTIVNA);
    }

    // Scope za istekle
    public function scopeExpired($query)
    {
        return $query->where('status', self::STATUS_AKTIVNA)
            ->where('rezervisano_do', '<', now());
    }

    // Da li je aktivna
    public function isActive(): bool
    {
        return $this->status === self::STATUS_AKTIVNA 
            && ($this->rezervisano_do === null || $this->rezervisano_do->isFuture());
    }
}
