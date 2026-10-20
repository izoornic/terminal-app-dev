<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TerminalAcqHistory extends Model
{
    use HasFactory;

    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = [
        'terminal_acqId',
        'acq_tid',
        'naziv_na_slipu',
        'komunikacija',
        'iccid',
        'inventarni_broj',
        'dodatne_opcije',
        'testni',
        'napomena',
        'datum_zahteva',
        'datum_instalacije',
        'terminalId',
        'terminal_statusId',
        'lokacijaId',
        'acq_mid',
        'prethodni_terminal_acqId',
        'zahtev_acq_mid',
        'trazeni_terminal_tipId',
        'izvor',
        'izvor_fajl',
        'korisnikId',
        'korisnikIme',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'testni' => 'boolean',
        'datum_zahteva' => 'date',
        'datum_instalacije' => 'date',
    ];

    public function terminalAcq(): BelongsTo
    {
        return $this->belongsTo(TerminalAcq::class, 'terminal_acqId');
    }

    /**
     * Snapshot stanja TID-a PRE izmene. Poziva se u istoj transakciji kao izmena,
     * pre izmene TID-a i pre premeštanja uređaja.
     *
     * Red se čita ponovo iz baze (kao TerminalLokacijaHistory), pa nesačuvane
     * izmene na prosleđenom modelu ne ulaze u snapshot. Status, lokacija i MID se
     * upisuju kao efektivne vrednosti u tom trenutku (i nasleđene od uređaja / lokacije).
     */
    public static function createNewHistory(TerminalAcq $acq): self
    {
        $acq = TerminalAcq::query()->findOrFail($acq->id);
        $lokacija = $acq->efektivnaLokacija();

        return self::create([
            'terminal_acqId' => $acq->id,
            'acq_tid' => $acq->acq_tid,
            'naziv_na_slipu' => $acq->naziv_na_slipu,
            'komunikacija' => $acq->komunikacija,
            'iccid' => $acq->iccid,
            'inventarni_broj' => $acq->inventarni_broj,
            'dodatne_opcije' => $acq->dodatne_opcije,
            'testni' => $acq->testni,
            'napomena' => $acq->napomena,
            'datum_zahteva' => $acq->datum_zahteva,
            'datum_instalacije' => $acq->datum_instalacije,
            'terminalId' => $acq->terminalId,
            'terminal_statusId' => $acq->efektivniStatusId(),
            'lokacijaId' => $lokacija?->id,
            'acq_mid' => $lokacija?->acq_mid,
            'prethodni_terminal_acqId' => $acq->prethodni_terminal_acqId,
            'zahtev_acq_mid' => $acq->zahtev_acq_mid,
            'trazeni_terminal_tipId' => $acq->trazeni_terminal_tipId,
            'izvor' => $acq->izvor,
            'izvor_fajl' => $acq->izvor_fajl,
            'korisnikId' => $acq->korisnikId,
            'korisnikIme' => $acq->korisnikIme,
        ]);
    }
}
