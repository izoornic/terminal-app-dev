<?php

namespace App\Actions\Tiket;

use App\Models\Tiket;
use App\Models\TiketHistory;
use App\Models\TiketKomentar;
use App\Models\TiketOpisKvaraTip;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Menja vrstu kvara (tikets.opis_kvaraId) na tiketu koji nije zatvoren.
 *
 * Pre izmene se u tiket_histories upisuje snapshot tiketa, a promena (stara -> nova vrsta)
 * ostaje zabeležena kao komentar korisnika koji ju je napravio. Dodela tiketa se ne menja.
 */
class TiketPromeniVrstuKvara
{
    /**
     * Pozicije (pozicija_tips.id) koje smeju da menjaju vrstu kvara: Admin, Call centar.
     *
     * @var list<int>
     */
    public const DOZVOLJENE_POZICIJE = [1, 2];

    private const STATUS_ZATVOREN = 3;

    public static function mozeDaPromeni(User $user): bool
    {
        return in_array((int) $user->pozicija_tipId, self::DOZVOLJENE_POZICIJE, true);
    }

    /**
     * @throws AuthorizationException korisnik nema pravo da menja vrstu kvara
     * @throws ValidationException tiket je zatvoren, vrsta kvara ne postoji ili je ista kao postojeća
     */
    public static function promeni(int $tiketId, User $user, int $opisKvaraId): void
    {
        if (! self::mozeDaPromeni($user)) {
            throw new AuthorizationException('Nemate pravo da menjate vrstu kvara.');
        }

        $tiket = Tiket::findOrFail($tiketId);

        if ((int) $tiket->tiket_statusId === self::STATUS_ZATVOREN) {
            throw ValidationException::withMessages([
                'opisKvara' => 'Tiket #'.$tiket->id.' je zatvoren. Ponovo ga otvorite da biste promenili vrstu kvara.',
            ]);
        }

        $noviKvar = TiketOpisKvaraTip::find($opisKvaraId);
        if (! $noviKvar) {
            throw ValidationException::withMessages([
                'opisKvara' => 'Izaberite vrstu kvara iz liste.',
            ]);
        }

        if ((int) $tiket->opis_kvaraId === $noviKvar->id) {
            throw ValidationException::withMessages([
                'opisKvara' => 'Tiket već ima vrstu kvara "'.$noviKvar->tok_naziv.'".',
            ]);
        }

        $stariNaziv = TiketOpisKvaraTip::find($tiket->opis_kvaraId)?->tok_naziv ?? '---';

        DB::transaction(function () use ($tiket, $user, $noviKvar, $stariNaziv): void {
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
                'komentar' => 'Promenjena vrsta kvara: '.$stariNaziv.' -> '.$noviKvar->tok_naziv,
                'korisnikId' => $user->id,
            ]);

            $tiket->update([
                'opis_kvaraId' => $noviKvar->id,
                'br_komentara' => $tiket->br_komentara + 1,
            ]);
        });
    }
}
