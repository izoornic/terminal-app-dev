<?php

namespace Tests\Feature\Terminali;

use App\Models\Lokacija;
use App\Models\LokacijaKontaktOsoba;
use App\Models\Region;
use App\Models\Terminal;
use App\Models\TerminalAcq;
use App\Models\TerminalAcqHistory;
use App\Models\TerminalLokacija;
use App\Models\TerminalStatusTip;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * HALK evidencija, Faza 1: model terminal_acq, istorija i statusi samo za TID.
 *
 * Status i lokacija TID-a se nasleđuju od uređaja dok je pod TID-om, a kad TID
 * nema uređaj, važe sopstvene vrednosti iz terminal_acq.
 */
class TerminalAcqModelTest extends TestCase
{
    use RefreshDatabase;

    private const STATUS_MAGACIN = 1;

    private const STATUS_INSTALIRAN = 2;

    private const STATUS_ZA_INSTALACIJU = 8;

    private const STATUS_NA_SERVISU = 11;

    private int $regionId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->regionId = Region::create(['r_naziv' => 'Beograd'])->id;

        TerminalStatusTip::forceCreate(['id' => self::STATUS_MAGACIN, 'ts_naziv' => 'Magacin']);
        TerminalStatusTip::forceCreate(['id' => self::STATUS_INSTALIRAN, 'ts_naziv' => 'Instaliran']);
        TerminalStatusTip::forceCreate(['id' => self::STATUS_ZA_INSTALACIJU, 'ts_naziv' => 'Za instalaciju']);
        TerminalStatusTip::forceCreate(['id' => self::STATUS_NA_SERVISU, 'ts_naziv' => 'Na servisu']);
    }

    #[Test]
    public function migracija_dodaje_statuse_samo_za_tid_iznad_postojecih_idjeva(): void
    {
        $tidStatusi = TerminalStatusTip::where('ts_za_uredjaj', false)->orderBy('id')->get();

        $this->assertSame(
            [
                TerminalStatusTip::DEINSTALIRAN,
                TerminalStatusTip::PREKONFIGURISAN,
                TerminalStatusTip::OPOZVAN,
                TerminalStatusTip::CEKA_PREKONFIGURACIJU,
            ],
            $tidStatusi->pluck('ts_naziv')->all()
        );
        $this->assertGreaterThan(11, $tidStatusi->min('id'));
    }

    #[Test]
    public function tipovi_list_ne_nudi_statuse_samo_za_tid(): void
    {
        $lista = TerminalStatusTip::tipoviList();

        $this->assertSame(
            [
                self::STATUS_MAGACIN => 'Magacin',
                self::STATUS_INSTALIRAN => 'Instaliran',
                self::STATUS_ZA_INSTALACIJU => 'Za instalaciju',
                self::STATUS_NA_SERVISU => 'Na servisu',
            ],
            $lista
        );
        $this->assertNotContains(TerminalStatusTip::DEINSTALIRAN, $lista);
    }

    #[Test]
    public function tid_status_list_vraca_i_statuse_samo_za_tid(): void
    {
        $lista = TerminalStatusTip::tidStatusList();

        $this->assertCount(8, $lista);
        $this->assertContains('Instaliran', $lista);
        $this->assertContains(TerminalStatusTip::CEKA_PREKONFIGURACIJU, $lista);
        $this->assertSame(
            array_search(TerminalStatusTip::OPOZVAN, $lista, true),
            TerminalStatusTip::idPoNazivu(TerminalStatusTip::OPOZVAN)
        );
    }

    #[Test]
    public function tid_sa_uredjajem_nasledjuje_status_i_lokaciju_uredjaja(): void
    {
        $lokacija = $this->lokacija();
        $terminalLokacija = $this->uredjajNaLokaciji($lokacija->id, self::STATUS_INSTALIRAN);
        $acq = TerminalAcq::factory()->saUredjajem($terminalLokacija->terminalId)->create();

        $this->assertNull($acq->terminal_statusId);
        $this->assertNull($acq->lokacijaId);
        $this->assertSame(self::STATUS_INSTALIRAN, $acq->efektivniStatusId());
        $this->assertSame($lokacija->id, $acq->efektivnaLokacijaId());

        $terminalLokacija->update(['terminal_statusId' => self::STATUS_NA_SERVISU]);

        $this->assertSame(self::STATUS_NA_SERVISU, $acq->fresh()->efektivniStatusId());
    }

    #[Test]
    public function tid_bez_uredjaja_ima_sopstveni_status_i_lokaciju(): void
    {
        $lokacija = $this->lokacija();
        $acq = TerminalAcq::factory()->bezUredjaja(self::STATUS_ZA_INSTALACIJU, $lokacija->id)->create();

        $this->assertSame(self::STATUS_ZA_INSTALACIJU, $acq->efektivniStatusId());
        $this->assertSame($lokacija->id, $acq->efektivnaLokacijaId());
        $this->assertSame($lokacija->id, $acq->efektivnaLokacija()->id);
        $this->assertSame('Za instalaciju', $acq->status->ts_naziv);
    }

    #[Test]
    public function scope_vraca_efektivno_stanje_za_oba_slucaja(): void
    {
        $lokacijaUredjaja = $this->lokacija();
        $lokacijaZahteva = $this->lokacija();
        $terminalLokacija = $this->uredjajNaLokaciji($lokacijaUredjaja->id, self::STATUS_INSTALIRAN);

        $saUredjajem = TerminalAcq::factory()->saUredjajem($terminalLokacija->terminalId)->create();
        $bezUredjaja = TerminalAcq::factory()->bezUredjaja(self::STATUS_ZA_INSTALACIJU, $lokacijaZahteva->id)->create();

        $redovi = TerminalAcq::query()->saEfektivnimStanjem()->get()->keyBy('id');

        $this->assertSame(self::STATUS_INSTALIRAN, (int) $redovi[$saUredjajem->id]->efektivni_statusId);
        $this->assertSame($lokacijaUredjaja->id, (int) $redovi[$saUredjajem->id]->efektivna_lokacijaId);
        $this->assertSame(self::STATUS_ZA_INSTALACIJU, (int) $redovi[$bezUredjaja->id]->efektivni_statusId);
        $this->assertSame($lokacijaZahteva->id, (int) $redovi[$bezUredjaja->id]->efektivna_lokacijaId);
    }

    #[Test]
    public function istorija_cuva_efektivno_stanje_iz_baze_pre_izmene(): void
    {
        $lokacija = $this->lokacija(['acq_mid' => '000000000004388']);
        $terminalLokacija = $this->uredjajNaLokaciji($lokacija->id, self::STATUS_INSTALIRAN);
        $acq = TerminalAcq::factory()->saUredjajem($terminalLokacija->terminalId)->create([
            'acq_tid' => 'PN004096',
            'naziv_na_slipu' => 'SAVSKI VENAC RENT',
        ]);

        /**
         * Nesačuvana izmena ne sme da uđe u snapshot.
         */
        $acq->naziv_na_slipu = 'NOVI NAZIV';

        $history = TerminalAcqHistory::createNewHistory($acq);

        $this->assertSame($acq->id, $history->terminal_acqId);
        $this->assertSame('PN004096', $history->acq_tid);
        $this->assertSame('SAVSKI VENAC RENT', $history->naziv_na_slipu);
        $this->assertSame($terminalLokacija->terminalId, $history->terminalId);
        $this->assertSame(self::STATUS_INSTALIRAN, $history->terminal_statusId);
        $this->assertSame($lokacija->id, $history->lokacijaId);
        $this->assertSame('000000000004388', $history->acq_mid);
        $this->assertCount(1, $acq->histories);
    }

    #[Test]
    public function acq_tid_je_jedinstven(): void
    {
        TerminalAcq::factory()->create(['acq_tid' => 'PN005313']);

        $this->expectException(QueryException::class);

        TerminalAcq::factory()->create(['acq_tid' => 'PN005313']);
    }

    #[Test]
    public function lanac_prekonfiguracije_stari_novi(): void
    {
        $stari = TerminalAcq::factory()->create(['acq_tid' => 'PN004096']);
        $novi = TerminalAcq::factory()
            ->bezUredjaja(TerminalStatusTip::idPoNazivu(TerminalStatusTip::CEKA_PREKONFIGURACIJU), $this->lokacija()->id)
            ->create(['acq_tid' => 'PN005313', 'prethodni_terminal_acqId' => $stari->id]);

        $this->assertSame('PN004096', $novi->prethodni->acq_tid);
        $this->assertSame('PN005313', $stari->naslednik->acq_tid);
    }

    #[Test]
    public function lokacija_prima_podatke_banke_a_kontakt_duzi_telefon(): void
    {
        $lokacija = $this->lokacija([
            'acq_mid' => '000000000004845',
            'mcc' => '5812',
            'ptt' => '11000',
            'precizna_lokacija' => 'VGC9+45 Mrčajevci',
        ]);

        $this->assertSame('000000000004845', $lokacija->fresh()->acq_mid);
        $this->assertSame('5812', $lokacija->fresh()->mcc);

        $dvaBroja = '060/608-1025   064 803 1020';
        $kontakt = LokacijaKontaktOsoba::create(['lokacijaId' => $lokacija->id, 'name' => 'Test', 'tel' => $dvaBroja]);

        $this->assertSame($dvaBroja, $kontakt->fresh()->tel);
    }

    /**
     * @param  array<string, mixed>  $atributi
     */
    private function lokacija(array $atributi = []): Lokacija
    {
        return Lokacija::factory()->create(['regionId' => $this->regionId, 'lokacija_tipId' => 3] + $atributi);
    }

    private function uredjajNaLokaciji(int $lokacijaId, int $statusId): TerminalLokacija
    {
        $terminal = Terminal::create(['sn' => fake()->unique()->bothify('A26-12RB-#?#####'), 'terminal_tipId' => 1]);

        return TerminalLokacija::create([
            'terminalId' => $terminal->id,
            'lokacijaId' => $lokacijaId,
            'terminal_statusId' => $statusId,
            'korisnikId' => 1,
            'korisnikIme' => 'Test',
        ]);
    }
}
