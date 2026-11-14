<?php

namespace App\Actions\TerminalAcq;

use App\Models\TerminalAcq;
use App\Models\TerminalAcqComment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Komentari ACQ TID-a (kao TerminalCommentActions za uređaj): dodavanje, meko brisanje
 * i brojač na terminal_acq. Komentar briše autor, ili korisnik sa pravom upravljanja.
 */
class TerminalAcqKomentari
{
    public function dodaj(TerminalAcq $acq, string $tekst, User $korisnik): TerminalAcqComment
    {
        return DB::transaction(function () use ($acq, $tekst, $korisnik): TerminalAcqComment {
            $komentar = TerminalAcqComment::create([
                'terminal_acqId' => $acq->id,
                'userId' => $korisnik->id,
                'comment' => trim($tekst),
            ]);
            $this->azurirajBroj($acq->id, $korisnik);

            return $komentar;
        });
    }

    /**
     * @throws RuntimeException kad korisnik nije autor, a nema pravo upravljanja
     */
    public function obrisi(TerminalAcqComment $komentar, User $korisnik, bool $mozeUpravljati): void
    {
        if (! self::mozeObrisati($komentar, $korisnik, $mozeUpravljati)) {
            throw new RuntimeException('Komentar može da obriše samo autor, Admin ili Call centar.');
        }

        DB::transaction(function () use ($komentar, $korisnik): void {
            $komentar->update(['is_active' => false, 'deleted_at' => now()]);
            $this->azurirajBroj($komentar->terminal_acqId, $korisnik);
        });
    }

    public static function mozeObrisati(TerminalAcqComment $komentar, User $korisnik, bool $mozeUpravljati): bool
    {
        return $komentar->is_active && ($mozeUpravljati || $komentar->userId === $korisnik->id);
    }

    /**
     * Brojač se menja upitom, bez snimanja modela, da izmena komentara ne izgleda kao izmena TID-a.
     */
    private function azurirajBroj(int $terminalAcqId, User $korisnik): void
    {
        TerminalAcq::query()->whereKey($terminalAcqId)->update([
            'br_komentara' => TerminalAcqComment::query()->where('terminal_acqId', $terminalAcqId)->where('is_active', true)->count(),
            'last_comment_userId' => $korisnik->id,
            'last_comment_at' => now(),
        ]);
    }
}
