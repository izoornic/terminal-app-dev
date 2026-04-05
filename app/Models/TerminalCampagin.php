<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TerminalCampagin extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "terminal_campagins";

    protected $fillable = [
        'distributer_id',
        'campagin_name',
        'campagin_description',
        'active',
    ];
}
