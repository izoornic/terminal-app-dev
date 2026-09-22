<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TiketAkcijaKorisnikPozicija extends Model
{
    use HasFactory;

    /**
     * tiket_akcija_tips.id akcije "kreira tiket"
     */
    public const AKCIJA_KREIRA_TIKET = 2;

    /**
     * Vrednosti akcije "kreira tiket" koje dozvoljavaju otvaranje novog tiketa
     *
     * @var list<string>
     */
    public const VREDNOSTI_KOJE_KREIRAJU = ['sve', 'region'];

    /**
     * Vrednost akcije (sve, region, dodeljen, ne...)
     */
    public function vrednost(): BelongsTo
    {
        return $this->belongsTo(TiketAkcijaVrednostTip::class, 'tiket_akcijavrednostId');
    }

    /**
     * Da li pozicija korisnika sme da otvori novi tiket
     * (isti uslov kao $tiketAkcija[2] u Livewire Tikets komponenti)
     */
    public static function daliPozicijaMozeKreiratiTiket(?int $pozicijaTipId): bool
    {
        if ($pozicijaTipId === null) {
            return false;
        }

        return static::query()
            ->where('korisnik_pozicijaId', $pozicijaTipId)
            ->where('tiket_akcijaId', self::AKCIJA_KREIRA_TIKET)
            ->whereHas('vrednost', fn (Builder $query) => $query->whereIn('akcija_vrednost_opis', self::VREDNOSTI_KOJE_KREIRAJU))
            ->exists();
    }
}
