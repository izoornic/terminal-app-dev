<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TerminalComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'terminal_lokacijaId',
        'userId',
        'is_active',
        'comment',
        'deleted_at',
    ];
}
