<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\TerminalLokacija;

class TerminalLokacijaHistory extends Model
{
    use HasFactory;

    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = [
        'terminal_lokacijaId',
        'terminalId',
        'lokacijaId',
        'terminal_statusId',
        'terminal_campagin_id',
        'korisnikId',
        'korisnikIme',
        'created_at',
        'updated_at',
        'blacklist',
        'distributerId'
    ];

    public static function createNewHistory($id)
    {
        $current = TerminalLokacija::where('id', '=', $id)->first();
        TerminalLokacijaHistory::create([
            'terminal_lokacijaId' => $id,
            'terminalId' => $current->terminalId,
            'lokacijaId' => $current->lokacijaId,
            'terminal_statusId' => $current->terminal_statusId,
            'terminal_campagin_id' => $current->terminal_campagin_id,
            'korisnikId' => $current->korisnikId,
            'korisnikIme' => $current->korisnikIme,
            'created_at' => $current->created_at,
            'updated_at' => $current->updated_at,
            'blacklist' => $current->blacklist,
            'distributerId' => $current->distributerId
        ]);
    }
}
