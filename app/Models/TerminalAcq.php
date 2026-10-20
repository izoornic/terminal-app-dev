<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * HALK ACQ TID - jedan red po TID-u, trenutno stanje.
 *
 * Dok je terminalId popunjen, terminal_statusId i lokacijaId su null: status i
 * lokacija TID-a se tada čitaju iz terminal_lokacijas uređaja. Popunjeni su samo
 * kad TID nema uređaj (čeka instalaciju / prekonfiguraciju, ili je zatvoren).
 */
class TerminalAcq extends Model
{
    use HasFactory;

    public const IZVOR_UVOZ = 'uvoz';

    public const IZVOR_ZAHTEV = 'zahtev';

    public const IZVOR_RUCNO = 'rucno';

    protected $table = 'terminal_acq';

    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = [
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

    public function terminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class, 'terminalId');
    }

    public function terminalLokacija(): HasOne
    {
        return $this->hasOne(TerminalLokacija::class, 'terminalId', 'terminalId');
    }

    /**
     * Sopstvena lokacija TID-a; popunjena samo dok TID nema uređaj.
     */
    public function lokacija(): BelongsTo
    {
        return $this->belongsTo(Lokacija::class, 'lokacijaId');
    }

    /**
     * Sopstveni status TID-a; popunjen samo dok TID nema uređaj.
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(TerminalStatusTip::class, 'terminal_statusId');
    }

    public function trazeniTerminalTip(): BelongsTo
    {
        return $this->belongsTo(TerminalTip::class, 'trazeni_terminal_tipId');
    }

    public function prethodni(): BelongsTo
    {
        return $this->belongsTo(self::class, 'prethodni_terminal_acqId');
    }

    public function naslednik(): HasOne
    {
        return $this->hasOne(self::class, 'prethodni_terminal_acqId');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(TerminalAcqHistory::class, 'terminal_acqId')->orderBy('id');
    }

    /**
     * Status uređaja dok je pod TID-om, inače sopstveni status TID-a.
     */
    public function efektivniStatusId(): ?int
    {
        if ($this->terminalId !== null) {
            return $this->terminalLokacija?->terminal_statusId;
        }

        return $this->terminal_statusId;
    }

    /**
     * Lokacija uređaja dok je pod TID-om, inače sopstvena lokacija TID-a.
     */
    public function efektivnaLokacijaId(): ?int
    {
        if ($this->terminalId !== null) {
            return $this->terminalLokacija?->lokacijaId;
        }

        return $this->lokacijaId;
    }

    public function efektivnaLokacija(): ?Lokacija
    {
        $lokacijaId = $this->efektivnaLokacijaId();

        return $lokacijaId === null ? null : Lokacija::find($lokacijaId);
    }

    /**
     * Dodaje kolone efektivni_statusId i efektivna_lokacijaId (za listu i filtere).
     *
     * @param  Builder<TerminalAcq>  $query
     * @return Builder<TerminalAcq>
     */
    public function scopeSaEfektivnimStanjem(Builder $query): Builder
    {
        return $query
            ->leftJoin('terminal_lokacijas', 'terminal_lokacijas.terminalId', '=', 'terminal_acq.terminalId')
            ->select('terminal_acq.*')
            ->selectRaw('COALESCE(terminal_lokacijas.terminal_statusId, terminal_acq.terminal_statusId) as efektivni_statusId')
            ->selectRaw('COALESCE(terminal_lokacijas.lokacijaId, terminal_acq.lokacijaId) as efektivna_lokacijaId');
    }
}
