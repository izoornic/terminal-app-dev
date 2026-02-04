<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PartCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'naziv',
        'opis',
        'parent_id',
    ];

    // Self-referencing relationship
    public function parent(): BelongsTo
    {
        return $this->belongsTo(PartCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(PartCategory::class, 'parent_id');
    }

    public function partTypes(): HasMany
    {
        return $this->hasMany(PartType::class, 'category_id');
    }

    // Rekurzivno dobijanje svih descendants
    public function allChildren(): HasMany
    {
        return $this->children()->with('allChildren');
    }
}
