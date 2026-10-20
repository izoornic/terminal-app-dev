<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TerminalStatusTip extends Model
{
    use HasFactory;

    public const ZA_INSTALACIJU = 'Za instalaciju';

    public const DEINSTALIRAN = 'Deinstaliran';

    public const PREKONFIGURISAN = 'Prekonfigurisan';

    public const OPOZVAN = 'Opozvan';

    public const CEKA_PREKONFIGURACIJU = 'Čeka prekonfiguraciju';

    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = [
        'ts_naziv',
        'ts_za_uredjaj',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'ts_za_uredjaj' => 'boolean',
    ];

    /**
     * Statusi uređaja za dropdown-ove. Statusi koji postoje samo za TID
     * (ts_za_uredjaj = 0) se ne nude.
     *
     * @return array<int, string>
     */
    public static function tipoviList(): array
    {
        $status_list = [];
        foreach (TerminalStatusTip::where('ts_za_uredjaj', true)->get() as $status) {
            $status_list[$status->id] = $status->ts_naziv;
        }

        return $status_list;
    }

    /**
     * Svi statusi, uključujući i one samo za TID (HALK evidencija).
     *
     * @return array<int, string>
     */
    public static function tidStatusList(): array
    {
        return TerminalStatusTip::orderBy('id')->pluck('ts_naziv', 'id')->all();
    }

    public static function idPoNazivu(string $naziv): ?int
    {
        return TerminalStatusTip::where('ts_naziv', $naziv)->value('id');
    }
}
