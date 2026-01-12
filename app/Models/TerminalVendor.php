<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TerminalVendor extends Model
{
    use HasFactory;

    protected $table = 'terminal_vendors';

    protected $fillable = [
        'name',
    ];

    public static function allList()
    {
        foreach(Self::all() as $item){
            $item_list[$item->id] = $item->name;
        }
        return  $item_list;
    }
}
