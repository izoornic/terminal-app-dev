<?php

namespace Tests\Feature\Terminali;

use App\Actions\TerminalAcq\DodajTerminalAcq;
use App\Models\Lokacija;
use App\Models\Region;
use App\Models\Terminal;
use App\Models\TerminalAcq;
use App\Models\TerminalLokacija;
use App\Models\TerminalStatusTip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

/**
 * Novi aktivni TID uz uređaj: pravila koja važe i za "Dodaj terminal" i za halk:uvoz.
 */
class DodajTerminalAcqTest extends TestCase
{
    use RefreshDatabase;

    private const STATUS_ZA_INSTALACIJU = 8;

    private Lokacija $lokacija;

    private User $korisnik;

    protected function setUp(): void
    {
        parent::setUp();

        TerminalStatusTip::forceCreate(['id' => self::STATUS_ZA_INSTALACIJU, 'ts_naziv' => 'Za instalaciju']);
        $regionId = Region::create(['r_naziv' => 'Beograd'])->id;
        $this->lokacija = Lokacija::factory()->create([
            'regionId' => $regionId,
            'lokacija_tipId' => 3,
            'pib' => '113735946',
            'acq_mid' => '000000000004388',
            'mcc' => null,
            'ptt' => null,
        ]);
        $this->korisnik = User::factory()->create(['pozicija_tipId' => 1, 'lokacijaId' => $this->lokacija->id]);
    }

    #[Test]
    public function kreira_tid_uz_uredjaj_i_dopunjuje_samo_prazna_polja_lokacije(): void
    {
        $terminalId = $this->uredjaj();

        $acq = (new DodajTerminalAcq)->saUredjajem(
            ['acq_tid' => ' pn005313 ', 'naziv_na_slipu' => 'PRE-SUDA DOO', 'izvor' => TerminalAcq::IZVOR_RUCNO],
            $terminalId,
            $this->korisnik,
            ['acq_mid' => '000000000004845', 'mcc' => '5812', 'ptt' => '11000', 'pib' => '999999999']
        );

        $this->assertSame('PN005313', $acq->acq_tid);
        $this->assertSame($terminalId, $acq->terminalId);
        $this->assertNull($acq->terminal_statusId);
        $this->assertSame(self::STATUS_ZA_INSTALACIJU, $acq->efektivniStatusId());
        $this->assertSame($this->korisnik->id, $acq->korisnikId);

        $lokacija = $this->lokacija->fresh();
        $this->assertSame('000000000004388', $lokacija->acq_mid, 'postojeći MID se ne prepisuje');
        $this->assertSame('113735946', $lokacija->pib, 'postojeći PIB se ne prepisuje');
        $this->assertSame('5812', $lokacija->mcc);
        $this->assertSame('11000', $lokacija->ptt);
    }

    #[Test]
    public function odbija_neispravan_tid(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Neispravan TID');

        (new DodajTerminalAcq)->saUredjajem(['acq_tid' => 'PN12', 'izvor' => TerminalAcq::IZVOR_RUCNO], $this->uredjaj(), $this->korisnik);
    }

    #[Test]
    public function odbija_postojeci_tid(): void
    {
        TerminalAcq::factory()->create(['acq_tid' => 'PN005313']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('već postoji');

        (new DodajTerminalAcq)->saUredjajem(['acq_tid' => 'PN005313', 'izvor' => TerminalAcq::IZVOR_RUCNO], $this->uredjaj(), $this->korisnik);
    }

    #[Test]
    public function odbija_uredjaj_bez_terminal_lokacije(): void
    {
        $terminal = Terminal::create(['sn' => 'SN-BEZ-LOKACIJE', 'terminal_tipId' => 1]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('nema red u terminal_lokacijas');

        (new DodajTerminalAcq)->saUredjajem(['acq_tid' => 'PN005313', 'izvor' => TerminalAcq::IZVOR_RUCNO], $terminal->id, $this->korisnik);
    }

    #[Test]
    public function odbija_uredjaj_koji_je_vec_pod_aktivnim_tid_om(): void
    {
        $terminalId = $this->uredjaj();
        TerminalAcq::factory()->saUredjajem($terminalId)->create(['acq_tid' => 'PN004096']);

        try {
            (new DodajTerminalAcq)->saUredjajem(['acq_tid' => 'PN005313', 'izvor' => TerminalAcq::IZVOR_RUCNO], $terminalId, $this->korisnik);
            $this->fail('Očekivan izuzetak.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('PN004096', $e->getMessage());
        }

        $this->assertFalse(TerminalAcq::where('acq_tid', 'PN005313')->exists());
    }

    private function uredjaj(): int
    {
        $terminal = Terminal::create(['sn' => fake()->unique()->bothify('A26-12RB-#?#####'), 'terminal_tipId' => 1]);
        TerminalLokacija::create([
            'terminalId' => $terminal->id,
            'lokacijaId' => $this->lokacija->id,
            'terminal_statusId' => self::STATUS_ZA_INSTALACIJU,
            'korisnikId' => 1,
            'korisnikIme' => 'Test',
        ]);

        return $terminal->id;
    }
}
