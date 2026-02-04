<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Lokacija;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartMovement extends Model
{
    use HasFactory;

    const UPDATED_AT = null; // Movements ne ažuriraju se

    protected $fillable = [
        'part_type_id',
        'lokacija_id',
        'tip_kretanja',
        'kolicina',
        'povezana_lokacija_id',
        'korisnik_id',
        'dokument',
        'napomena',
    ];

    protected $casts = [
        'kolicina' => 'integer',
        'created_at' => 'datetime',
    ];

    // Tipovi kretanja
    const TIP_ULAZ = 'ULAZ';
    const TIP_IZLAZ = 'IZLAZ';
    const TIP_TRANSFER_OUT = 'TRANSFER_OUT';
    const TIP_TRANSFER_IN = 'TRANSFER_IN';
    const TIP_REZERVACIJA = 'REZERVACIJA';
    const TIP_POVRAT = 'POVRAT';

    public function partType(): BelongsTo
    {
        return $this->belongsTo(PartType::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Lokacija::class, 'lokacija_id');
    }

    public function connectedLocation(): BelongsTo
    {
        return $this->belongsTo(Lokacija::class, 'povezana_lokacija_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'korisnik_id');
    }

    // Scope za transfere
    public function scopeTransfers($query)
    {
        return $query->whereIn('tip_kretanja', [self::TIP_TRANSFER_OUT, self::TIP_TRANSFER_IN]);
    }

    // Scope po tipu
    public function scopeOfType($query, string $type)
    {
        return $query->where('tip_kretanja', $type);
    }

    // Scope po datumu
    public function scopeBetweenDates($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    // Da li je transfer
    public function isTransfer(): bool
    {
        return in_array($this->tip_kretanja, [self::TIP_TRANSFER_OUT, self::TIP_TRANSFER_IN]);
    }
}
