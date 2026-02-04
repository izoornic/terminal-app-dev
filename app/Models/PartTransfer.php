<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Lokacija;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PartTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_broj',
        'source_lokacija_id',
        'destination_lokacija_id',
        'status',
        'kreirao_korisnik_id',
        'odobrio_korisnik_id',
        'napomena',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    const STATUS_PENDING = 'PENDING';
    const STATUS_COMPLETED = 'COMPLETED';
    const STATUS_CANCELLED = 'CANCELLED';

    public function sourceLocation(): BelongsTo
    {
        return $this->belongsTo(Lokacija::class, 'source_lokacija_id');
    }

    public function destinationLocation(): BelongsTo
    {
        return $this->belongsTo(Lokacija::class, 'destination_lokacija_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kreirao_korisnik_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'odobrio_korisnik_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PartTransferItem::class, 'transfer_id');
    }

    // Scope za pending transfere
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    // Da li je završen
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    // Da li može biti otkazan
    public function canBeCancelled(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    // Generisanje broja transfera
    public static function generateTransferNumber(): string
    {
        $prefix = 'TRN';
        $date = now()->format('Ymd');
        $sequence = static::whereDate('created_at', today())->count() + 1;
        
        return sprintf('%s-%s-%04d', $prefix, $date, $sequence);
    }
}
