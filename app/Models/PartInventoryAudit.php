<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Lokacija;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PartInventoryAudit extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'lokacija_id',
        'korisnik_id',
        'status',
        'started_at',
        'completed_at',
        'napomena',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    const STATUS_IN_PROGRESS = 'IN_PROGRESS';
    const STATUS_COMPLETED = 'COMPLETED';

    public function location(): BelongsTo
    {
        return $this->belongsTo(Lokacija::class, 'lokacija_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'korisnik_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PartAuditItem::class, 'audit_id');
    }

    // Scope za trenutne inventure
    public function scopeInProgress($query)
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    // Da li ima razlike
    public function hasDifferences(): bool
    {
        return $this->items()->where('razlika', '!=', 0)->exists();
    }

    // Ukupna razlika
    public function getTotalDifferenceAttribute(): int
    {
        return $this->items()->sum('razlika');
    }
}
