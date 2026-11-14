<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Komentar ACQ TID-a; obrisan komentar ostaje u tabeli sa is_active = 0.
 */
class TerminalAcqComment extends Model
{
    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = [
        'terminal_acqId',
        'userId',
        'is_active',
        'comment',
        'deleted_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    public function terminalAcq(): BelongsTo
    {
        return $this->belongsTo(TerminalAcq::class, 'terminal_acqId');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'userId');
    }
}
