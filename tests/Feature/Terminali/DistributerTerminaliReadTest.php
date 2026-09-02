<?php

namespace Tests\Feature\Terminali;

use App\Actions\Terminali\TerminaliReadActions;
use App\Models\Lokacija;
use App\Models\Terminal;
use App\Models\TerminalLokacija;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DistributerTerminaliReadTest extends TestCase
{
    use RefreshDatabase;

    private const DIST_A = 11;
    private const DIST_B = 22;

    /**
     * Kreira terminal na lokaciji kod zadatog distributera.
     *
     * @param  array<string, mixed>  $lokacijaAtributi
     */
    private function terminalKodDistributera(int $distributerId, array $lokacijaAtributi): TerminalLokacija
    {
        $lokacija = Lokacija::factory()->create($lokacijaAtributi);

        $terminal = Terminal::create([
            'sn' => 'SN'.fake()->unique()->numerify('########'),
            'terminal_tipId' => 1,
        ]);

        return TerminalLokacija::create([
            'terminalId' => $terminal->id,
            'lokacijaId' => $lokacija->id,
            'terminal_statusId' => 1,
            'korisnikId' => 1,
            'korisnikIme' => 'test',
            'distributerId' => $distributerId,
        ]);
    }

    #[Test]
    public function pretraga_po_adresi_vraca_samo_terminale_trazenog_distributera(): void
    {
        $moj = $this->terminalKodDistributera(self::DIST_A, [
            'adresa' => 'Bulevar Oslobodjenja 100',
            'mesto' => 'Novi Sad',
            'l_naziv' => 'Moja radnja',
        ]);

        $tudji = $this->terminalKodDistributera(self::DIST_B, [
            'adresa' => 'Bulevar Oslobodjenja 200',
            'mesto' => 'Beograd',
            'l_naziv' => 'Tudja radnja',
        ]);

        $rezultat = TerminaliReadActions::DistributerTerminaliRead(
            self::DIST_A,
            ['searchLokacija' => 'Bulevar Oslobodjenja']
        )->get();

        $this->assertCount(1, $rezultat);
        $this->assertSame($moj->id, $rezultat->first()->id);
        $this->assertNotContains($tudji->id, $rezultat->pluck('id')->all());
    }

    #[Test]
    public function pretraga_po_mestu_vraca_samo_terminale_trazenog_distributera(): void
    {
        $moj = $this->terminalKodDistributera(self::DIST_A, [
            'adresa' => 'Knez Mihailova 1',
            'mesto' => 'Beograd',
            'l_naziv' => 'Moja radnja',
        ]);

        $this->terminalKodDistributera(self::DIST_B, [
            'adresa' => 'Terazije 5',
            'mesto' => 'Beograd',
            'l_naziv' => 'Tudja radnja',
        ]);

        $rezultat = TerminaliReadActions::DistributerTerminaliRead(
            self::DIST_A,
            ['searchLokacija' => 'Beograd']
        )->get();

        $this->assertCount(1, $rezultat);
        $this->assertSame($moj->id, $rezultat->first()->id);
    }

    #[Test]
    public function pretraga_po_nazivu_lokacije_vraca_samo_terminale_trazenog_distributera(): void
    {
        $moj = $this->terminalKodDistributera(self::DIST_A, [
            'adresa' => 'Cara Dusana 3',
            'mesto' => 'Nis',
            'l_naziv' => 'Market Zvezda',
        ]);

        $this->terminalKodDistributera(self::DIST_B, [
            'adresa' => 'Vojvode Misica 8',
            'mesto' => 'Kragujevac',
            'l_naziv' => 'Market Zvezda',
        ]);

        $rezultat = TerminaliReadActions::DistributerTerminaliRead(
            self::DIST_A,
            ['searchLokacija' => 'Market Zvezda']
        )->get();

        $this->assertCount(1, $rezultat);
        $this->assertSame($moj->id, $rezultat->first()->id);
    }

    #[Test]
    public function pretraga_po_adresi_ne_ponistava_ostale_filtere(): void
    {
        $this->terminalKodDistributera(self::DIST_A, [
            'adresa' => 'Glavna 1',
            'mesto' => 'Subotica',
            'l_naziv' => 'Prva',
            'pib' => '1111111111',
        ]);

        $ocekivani = $this->terminalKodDistributera(self::DIST_A, [
            'adresa' => 'Glavna 2',
            'mesto' => 'Subotica',
            'l_naziv' => 'Druga',
            'pib' => '2222222222',
        ]);

        $rezultat = TerminaliReadActions::DistributerTerminaliRead(
            self::DIST_A,
            ['searchLokacija' => 'Glavna', 'searchPib' => '2222222222']
        )->get();

        $this->assertCount(1, $rezultat);
        $this->assertSame($ocekivani->id, $rezultat->first()->id);
    }
}
