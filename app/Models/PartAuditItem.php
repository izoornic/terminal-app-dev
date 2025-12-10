<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartAuditItem extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'audit_id',
        'part_type_id',
        'expected_kolicina',
        'actual_kolicina',
        'napomena',
    ];

    protected $casts = [
        'expected_kolicina' => 'integer',
        'actual_kolicina' => 'integer',
        'razlika' => 'integer',
    ];

    protected $appends = ['razlika'];

    public function audit(): BelongsTo
    {
        return $this->belongsTo(PartInventoryAudit::class, 'audit_id');
    }

    public function partType(): BelongsTo
    {
        return $this->belongsTo(PartType::class);
    }

    // Accessor za razliku
    public function getRazlikaAttribute(): int
    {
        return $this->actual_kolicina - $this->expected_kolicina;
    }

    // Da li postoji razlika
    public function hasDifference(): bool
    {
        return $this->razlika !== 0;
    }
}
