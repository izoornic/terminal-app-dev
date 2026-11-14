<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Opcija ACQ TID-a (šifarnik, stranica acq-opcije); ne briše se nego deaktivira.
 */
class TerminalAcqOpcija extends Model
{
    public const VEZA = 'terminal_acq_opcija_tid';

    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = [
        'naziv',
        'opis',
        'redosled',
        'aktivna',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'aktivna' => 'boolean',
        'redosled' => 'integer',
    ];

    /**
     * TID-ovi na kojima je opcija uključena.
     */
    public function terminali(): BelongsToMany
    {
        return $this->belongsToMany(TerminalAcq::class, self::VEZA, 'terminal_acq_opcijaId', 'terminal_acqId')
            ->wherePivot('ukljucena', true);
    }

    /**
     * @param  Builder<TerminalAcqOpcija>  $query
     * @return Builder<TerminalAcqOpcija>
     */
    public function scopePoRedosledu(Builder $query): Builder
    {
        return $query->orderBy('redosled')->orderBy('id');
    }
}
