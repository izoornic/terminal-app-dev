<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartStock extends Model
{
    use HasFactory;
    protected $table = 'part_stocks';

    protected $fillable = [
        'part_type_id',
        'lokacija_id',
        'kolicina_ukupno',
        'kolicina_rezervisana',
    ];

    protected $casts = [
        'kolicina_ukupno' => 'integer',
        'kolicina_rezervisana' => 'integer',
        'kolicina_dostupna' => 'integer',
    ];

    protected $appends = ['kolicina_dostupna'];

    public function partType(): BelongsTo
    {
        return $this->belongsTo(PartType::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Lokacija::class, 'lokacija_id');
    }

    // Accessor za dostupnu količinu (ako nije stored computed column)
    public function getKolicinaDostupnaAttribute(): int
    {
        return $this->kolicina_ukupno - $this->kolicina_rezervisana;
    }

    // Scope za pretragu po lokaciji
    public function scopeByLocation($query, $locationId)
    {
        return $query->where('lokacija_id', $locationId);
    }

    // Scope za delove sa niskim stanjem
    public function scopeLowStock($query)
    {
        return $query->whereHas('partType', function ($q) {
            $q->whereRaw('part_stocks.kolicina_dostupna <= part_types.min_kolicina');
        });
    }

    // Scope za dostupne delove
    public function scopeAvailable($query)
    {
        return $query->where('kolicina_dostupna', '>', 0);
    }

    // Provera da li je dovoljno dostupno
    public function hasAvailable(int $quantity): bool
    {
        return $this->kolicina_dostupna >= $quantity;
    }
}
