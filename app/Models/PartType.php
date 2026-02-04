<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PartType extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'sifra',
        'naziv',
        'opis',
        'category_id',
        'cena',
        'jedinica_mere',
        'min_kolicina',
        'aktivan',
    ];

    protected $casts = [
        'cena' => 'decimal:2',
        'aktivan' => 'boolean',
        'min_kolicina' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(TerminalTip::class, 'category_id');
    }

    public function stock(): HasMany
    {
        return $this->hasMany(PartStock::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(PartMovement::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(PartReservation::class);
    }

    // Scope za aktivne delove
    public function scopeActive($query)
    {
        return $query->where('aktivan', true);
    }

    // Ukupna dostupna količina na svim lokacijama
    public function getTotalAvailableAttribute(): int
    {
        return $this->stock()->sum('kolicina_dostupna');
    }

    // Da li je zaliha niska na bilo kojoj lokaciji
    public function getIsLowStockAttribute(): bool
    {
        return $this->stock()
            ->where('kolicina_dostupna', '<=', $this->min_kolicina)
            ->exists();
    }

}
