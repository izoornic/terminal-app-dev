<?php

namespace Tests\Feature\Terminali;

use App\Actions\Terminali\StatusZaPremestanje;
use App\Models\Lokacija;
use App\Models\Terminal as TerminalModel;
use App\Models\TerminalLokacija;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Status koji se nudi u modalu za premeštanje više izabranih terminala.
 */
class StatusZaPremestanjeTest extends TestCase
{
    use RefreshDatabase;

    private const STATUS_MAGACIN = 1;

    private const STATUS_ZAMENSKI = 3;

    private const STATUS_ZA_DELOVE = 5;

    private int $lokacijaId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lokacijaId = Lokacija::factory()->create()->id;
    }

    #[Test]
    public function vraca_status_prvog_izabranog_reda(): void
    {
        $prvi = $this->terminalLokacija(self::STATUS_ZAMENSKI);
        $drugi = $this->terminalLokacija(self::STATUS_ZA_DELOVE);

        $this->assertSame(
            self::STATUS_ZAMENSKI,
            StatusZaPremestanje::premaPrvomIzabranom([(string) $prvi->id, (string) $drugi->id])
        );
    }

    /**
     * Sa liste stižu id-evi terminal_lokacijas redova (tlid), ne id-evi terminala.
     */
    #[Test]
    public function trazi_po_id_u_reda_a_ne_po_terminal_id_u(): void
    {
        /**
         * Terminal bez lokacije, da se id-evi terminala i terminal_lokacijas redova ne poklope.
         */
        $this->terminal();

        $izabrani = $this->terminalLokacija(self::STATUS_ZAMENSKI);
        $this->terminalLokacija(self::STATUS_MAGACIN, $izabrani->id);

        $this->assertNotSame($izabrani->id, $izabrani->terminalId);
        $this->assertSame(
            self::STATUS_ZAMENSKI,
            StatusZaPremestanje::premaPrvomIzabranom([(string) $izabrani->id])
        );
    }

    #[Test]
    public function bez_reda_vraca_podrazumevani_status(): void
    {
        $nepostojeci = $this->terminalLokacija(self::STATUS_ZAMENSKI)->id + 100;

        $this->assertSame(
            StatusZaPremestanje::PODRAZUMEVANI_STATUS,
            StatusZaPremestanje::premaPrvomIzabranom([(string) $nepostojeci])
        );
    }

    #[Test]
    public function bez_izabranih_terminala_vraca_podrazumevani_status(): void
    {
        $this->assertSame(
            StatusZaPremestanje::PODRAZUMEVANI_STATUS,
            StatusZaPremestanje::premaPrvomIzabranom([])
        );
    }

    /**
     * Livewire ne mora da ostavi ključ 0 kada se čekiranje ukloni sa liste.
     */
    #[Test]
    public function radi_i_kada_niz_nema_kljuc_nula(): void
    {
        $prvi = $this->terminalLokacija(self::STATUS_ZAMENSKI);

        $izabrani = [(string) $this->terminalLokacija(self::STATUS_MAGACIN)->id, (string) $prvi->id];
        unset($izabrani[0]);

        $this->assertSame(
            self::STATUS_ZAMENSKI,
            StatusZaPremestanje::premaPrvomIzabranom($izabrani)
        );
    }

    private function terminal(): TerminalModel
    {
        return TerminalModel::create([
            'sn' => fake()->unique()->numerify('SN########'),
            'terminal_tipId' => 1,
        ]);
    }

    private function terminalLokacija(int $statusId, ?int $terminalId = null): TerminalLokacija
    {
        return TerminalLokacija::create([
            'terminalId' => $terminalId ?? $this->terminal()->id,
            'lokacijaId' => $this->lokacijaId,
            'terminal_statusId' => $statusId,
            'korisnikId' => 1,
            'korisnikIme' => 'test',
        ]);
    }
}
