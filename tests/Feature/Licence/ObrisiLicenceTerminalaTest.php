<?php

namespace Tests\Feature\Licence;

use App\Actions\Licence\ObrisiLicenceTerminala;
use App\Models\LicencaNaplata;
use App\Models\LicencaParametarTerminal;
use App\Models\LicenceZaTerminal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Brisanje svih licenci terminala sa stranice distributera: parametri, aktivna naplata i
 * red u licence_za_terminals, i za licence koje nemaju aktivnu naplatu (servisne).
 */
class ObrisiLicenceTerminalaTest extends TestCase
{
    use RefreshDatabase;

    private const TERMINAL_LOKACIJA = 501;

    private const DISTRIBUTER = 27;

    #[Test]
    public function brise_regularnu_i_servisnu_licencu_i_zatvara_aktivnu_naplatu(): void
    {
        $this->licenca(10, poreklo: 1);
        $this->naplata(10);
        $this->parametar(10);
        $this->licenca(11, poreklo: 2);
        $this->parametar(11);
        $drugiTerminal = $this->licenca(10, terminalLokacijaId: 502);

        $obrisano = (new ObrisiLicenceTerminala)->izvrsi(self::TERMINAL_LOKACIJA);

        $this->assertSame(2, $obrisano);
        $this->assertSame(0, LicenceZaTerminal::where('terminal_lokacijaId', self::TERMINAL_LOKACIJA)->count());
        $this->assertSame(0, LicencaParametarTerminal::where('terminal_lokacijaId', self::TERMINAL_LOKACIJA)->count());

        $naplata = LicencaNaplata::where('terminal_lokacijaId', self::TERMINAL_LOKACIJA)->sole();
        $this->assertSame(0, (int) $naplata->aktivna, 'naplata ostaje zapisana, ali nije aktivna');
        $this->assertSame(0.0, (float) $naplata->razduzeno);
        $this->assertNotNull($naplata->datum_razduzenja);

        $this->assertTrue($drugiTerminal->fresh()->exists, 'licenca drugog terminala ostaje');
    }

    #[Test]
    public function servisna_licenca_bez_naplate_se_brise(): void
    {
        $this->licenca(11, poreklo: 2);

        $this->assertSame(1, (new ObrisiLicenceTerminala)->izvrsi(self::TERMINAL_LOKACIJA));
        $this->assertSame(0, LicenceZaTerminal::count());
        $this->assertSame(0, LicencaNaplata::count());
    }

    #[Test]
    public function terminal_bez_licenci(): void
    {
        $this->assertSame(0, (new ObrisiLicenceTerminala)->izvrsi(self::TERMINAL_LOKACIJA));
    }

    private function licenca(int $cenaId, int $poreklo = 1, int $terminalLokacijaId = self::TERMINAL_LOKACIJA): LicenceZaTerminal
    {
        return LicenceZaTerminal::create([
            'terminal_lokacijaId' => $terminalLokacijaId,
            'distributerId' => self::DISTRIBUTER,
            'licenca_distributer_cenaId' => $cenaId,
            'naziv_licence' => $poreklo === 2 ? 'Servisna' : 'Regularna',
            'mesecId' => 1,
            'terminal_sn' => '0300421120147911',
            'datum_pocetak' => '2026-01-01',
            'datum_kraj' => '2026-12-31',
            'datum_prekoracenja' => '2027-01-15',
            'licenca_poreklo' => $poreklo,
            'signature' => 'potpis',
        ]);
    }

    private function naplata(int $cenaId): LicencaNaplata
    {
        return LicencaNaplata::create([
            'terminal_lokacijaId' => self::TERMINAL_LOKACIJA,
            'distributerId' => self::DISTRIBUTER,
            'licenca_distributer_cenaId' => $cenaId,
            'mesecId' => 1,
            'zaduzeno' => 1200,
            'datum_zaduzenja' => '2026-01-01',
            'aktivna' => 1,
        ]);
    }

    private function parametar(int $cenaId): void
    {
        LicencaParametarTerminal::create([
            'terminal_lokacijaId' => self::TERMINAL_LOKACIJA,
            'distributerId' => self::DISTRIBUTER,
            'licenca_distributer_cenaId' => $cenaId,
            'licenca_distributer_terminalId' => 1,
            'licenca_parametarId' => 1,
        ]);
    }
}
