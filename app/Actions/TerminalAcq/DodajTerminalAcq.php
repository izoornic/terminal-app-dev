<?php

namespace App\Actions\TerminalAcq;

use App\Models\Lokacija;
use App\Models\TerminalAcq;
use App\Models\TerminalLokacija;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Novi aktivni TID uz uređaj ("Dodaj terminal", halk:uvoz, uvoz zahteva sa SN-om).
 *
 * Pravila: TID je oblika PNxxxxxx i jedinstven; uređaj ima red u terminal_lokacijas
 * i nije pod drugim aktivnim TID-om. Status i lokacija TID-a se nasleđuju od uređaja,
 * a polja lokacije se dopunjuju samo kad su prazna (lokacijas nema istoriju).
 */
class DodajTerminalAcq
{
    /**
     * Polja lokacije koja TID sme da dopuni kad su prazna.
     */
    public const DOPUNA_LOKACIJE = ['acq_mid', 'mcc', 'ptt', 'pib', 'precizna_lokacija'];

    /**
     * @param  array<string, mixed>  $podaci  kolone terminal_acq (bez terminalId, statusa, lokacije i autora)
     * @param  array<string, ?string>  $dopunaLokacije  vrednosti za prazna polja lokacije uređaja (ključevi iz DOPUNA_LOKACIJE)
     *
     * @throws RuntimeException kad TID ili uređaj ne zadovoljavaju pravila
     */
    public function saUredjajem(array $podaci, int $terminalId, User $korisnik, array $dopunaLokacije = []): TerminalAcq
    {
        $tid = $this->proveriTid($podaci['acq_tid'] ?? null);

        return DB::transaction(function () use ($podaci, $tid, $terminalId, $korisnik, $dopunaLokacije): TerminalAcq {
            $terminalLokacija = TerminalLokacija::query()->where('terminalId', $terminalId)->first();
            if ($terminalLokacija === null) {
                throw new RuntimeException('Uređaj nema red u terminal_lokacijas.');
            }

            $aktivni = TerminalAcq::query()->where('terminalId', $terminalId)->value('acq_tid');
            if ($aktivni !== null) {
                throw new RuntimeException("Uređaj je već pod aktivnim TID-om {$aktivni}.");
            }

            $acq = TerminalAcq::create([
                ...$podaci,
                'acq_tid' => $tid,
                'terminalId' => $terminalId,
                'terminal_statusId' => null,
                'lokacijaId' => null,
                'korisnikId' => $korisnik->id,
                'korisnikIme' => $korisnik->name,
            ]);

            $this->dopuniLokaciju($terminalLokacija->lokacijaId, $dopunaLokacije);

            return $acq;
        });
    }

    /**
     * TID iz zahteva banke koji čeka uređaj (Za instalaciju) ili tehničara (Čeka prekonfiguraciju).
     * Status i lokacija su sopstveni, jer TID još nema uređaj.
     *
     * @param  array<string, mixed>  $podaci  kolone terminal_acq (bez terminalId, statusa, lokacije i autora)
     *
     * @throws RuntimeException kad TID ne zadovoljava pravila
     */
    public function bezUredjaja(array $podaci, int $lokacijaId, int $statusId, User $korisnik): TerminalAcq
    {
        $tid = $this->proveriTid($podaci['acq_tid'] ?? null);

        return TerminalAcq::create([
            ...$podaci,
            'acq_tid' => $tid,
            'terminalId' => null,
            'terminal_statusId' => $statusId,
            'lokacijaId' => $lokacijaId,
            'korisnikId' => $korisnik->id,
            'korisnikIme' => $korisnik->name,
        ]);
    }

    /**
     * @param  array<string, ?string>  $dopuna
     * @return list<string> dopunjena polja
     */
    public function dopuniLokaciju(int $lokacijaId, array $dopuna): array
    {
        $lokacija = Lokacija::find($lokacijaId);
        if ($lokacija === null) {
            return [];
        }

        $dopunjeno = [];
        foreach (array_intersect_key($dopuna, array_flip(self::DOPUNA_LOKACIJE)) as $polje => $vrednost) {
            if ($vrednost !== null && $vrednost !== '' && ($lokacija->{$polje} === null || $lokacija->{$polje} === '')) {
                $lokacija->{$polje} = $vrednost;
                $dopunjeno[] = $polje;
            }
        }

        if ($dopunjeno !== []) {
            $lokacija->save();
        }

        return $dopunjeno;
    }

    /**
     * @throws RuntimeException
     */
    public function proveriTid(mixed $tid): string
    {
        $tid = strtoupper(preg_replace('/\s+/', '', (string) $tid));

        if (! preg_match('/^PN\d{6}$/', $tid)) {
            throw new RuntimeException("Neispravan TID \"{$tid}\" (očekuje se PN i 6 cifara).");
        }

        if (TerminalAcq::query()->where('acq_tid', $tid)->exists()) {
            throw new RuntimeException("TID {$tid} već postoji.");
        }

        return $tid;
    }
}
