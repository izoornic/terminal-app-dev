<?php

namespace App\Actions\Tiket;

use App\Models\Tiket;
use App\Models\TiketHistory;
use App\Models\TiketKomentar;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Vraća zatvoren tiket u otvoren (tiket_status_tips: 1 Otvoren, 2 Dodeljen, 3 Zatvoren).
 *
 * Tiket se vraća u status koji odgovara dodeli: ako je tiket dodeljen korisniku ostaje
 * "Dodeljen", inače postaje "Otvoren". Pre izmene se u tiket_histories upisuje snapshot
 * zatvorenog tiketa, a korisnik koji je otvorio tiket ostaje zabeležen kroz komentar.
 */
class TiketPonovoOtvori
{
    /**
     * Pozicije (pozicija_tips.id) koje smeju ponovo da otvore tiket: Admin, Call centar.
     *
     * @var list<int>
     */
    public const DOZVOLJENE_POZICIJE = [1, 2];

    private const STATUS_OTVOREN = 1;

    private const STATUS_DODELJEN = 2;

    private const STATUS_ZATVOREN = 3;

    public static function mozeDaOtvori(User $user): bool
    {
        return in_array((int) $user->pozicija_tipId, self::DOZVOLJENE_POZICIJE, true);
    }

    /**
     * @return int novi tiket_statusId
     *
     * @throws AuthorizationException korisnik nema pravo da otvori tiket
     * @throws ValidationException tiket nije zatvoren ili terminal već ima drugi otvoren tiket
     */
    public static function otvori(int $tiketId, User $user, ?string $razlog = null): int
    {
        if (! self::mozeDaOtvori($user)) {
            throw new AuthorizationException('Nemate pravo da ponovo otvorite tiket.');
        }

        $tiket = Tiket::findOrFail($tiketId);

        if ((int) $tiket->tiket_statusId !== self::STATUS_ZATVOREN) {
            throw ValidationException::withMessages([
                'ponovoOtvori' => 'Tiket #'.$tiket->id.' nije zatvoren.',
            ]);
        }

        $otvorenTiket = Tiket::daliTerminalImaOtvorenTiket($tiket->tremina_lokacijalId);
        if ($otvorenTiket) {
            throw ValidationException::withMessages([
                'ponovoOtvori' => 'Terminal već ima otvoren tiket #'.$otvorenTiket->id.'. Zatvorite ga pre ponovnog otvaranja ovog tiketa.',
            ]);
        }

        $noviStatus = $tiket->korisnik_dodeljenId ? self::STATUS_DODELJEN : self::STATUS_OTVOREN;

        $komentar = 'Tiket je ponovo otvoren.';
        if (trim((string) $razlog) !== '') {
            $komentar .= ' Razlog: '.trim($razlog);
        }

        DB::transaction(function () use ($tiket, $user, $noviStatus, $komentar): void {
            TiketHistory::create([
                'tiketId' => $tiket->id,
                'tremina_lokacijalId' => $tiket->tremina_lokacijalId,
                'tiket_statusId' => $tiket->tiket_statusId,
                'opis_kvaraId' => $tiket->opis_kvaraId,
                'korisnik_prijavaId' => $tiket->korisnik_prijavaId,
                'korisnik_dodeljenId' => $tiket->korisnik_dodeljenId,
                'opis' => $tiket->opis,
                'created_at' => $tiket->created_at,
                'updated_at' => $tiket->updated_at,
                'tiket_prioritetId' => $tiket->tiket_prioritetId,
                'br_komentara' => $tiket->br_komentara,
                'korisnik_zatvorio_id' => $tiket->korisnik_zatvorio_id,
            ]);

            TiketKomentar::create([
                'tiketId' => $tiket->id,
                'komentar' => $komentar,
                'korisnikId' => $user->id,
            ]);

            $tiket->update([
                'tiket_statusId' => $noviStatus,
                'korisnik_zatvorio_id' => 0,
                'br_komentara' => $tiket->br_komentara + 1,
            ]);
        });

        return $noviStatus;
    }
}
