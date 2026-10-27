<?php

namespace Tests\Unit;

use App\Actions\TerminalAcq\HalkNormalizator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Normalizacija vrednosti iz HALK Excel tabela (primeri su iz stvarnih fajlova).
 */
class HalkNormalizatorTest extends TestCase
{
    /**
     * @return array<string, array{0: mixed, 1: ?string, 2: ?string, 3: bool, 4: bool}>
     */
    public static function tidovi(): array
    {
        return [
            'čist' => ['PN005313', 'PN005313', null, false, true],
            'razmak ispred' => ['  PN004096', 'PN004096', null, false, true],
            'stari/novi' => ['PN001158/PN002927', 'PN002927', 'PN001158', false, true],
            'sufiks TEST' => ['PN004519 TEST', 'PN004519', null, true, true],
            'sufiks testni' => ['PN003333testni', 'PN003333', null, true, true],
            'kosa crta' => ['/', null, null, false, false],
            'prazno' => [null, null, null, false, false],
        ];
    }

    #[Test]
    #[DataProvider('tidovi')]
    public function tid(mixed $ulaz, ?string $tid, ?string $stariTid, bool $testni, bool $ispravan): void
    {
        $this->assertSame(
            ['tid' => $tid, 'stari_tid' => $stariTid, 'testni' => $testni, 'ispravan' => $ispravan],
            HalkNormalizator::tid($ulaz)
        );
    }

    #[Test]
    public function mid_je_tekst_od_15_cifara_a_kod_mid_mid_uzima_novi(): void
    {
        $this->assertSame('000000000004845', HalkNormalizator::mid('000000000004845'));
        $this->assertSame('000000000003531', HalkNormalizator::mid('00000000003531'));
        $this->assertSame('000000000004388', HalkNormalizator::mid(4388));
        $this->assertSame('000000000003982', HalkNormalizator::mid('000000000003562/000000000003982'));
        $this->assertNull(HalkNormalizator::mid(''));
    }

    #[Test]
    public function pib_ptt_mcc_iz_brojeva_postaju_tekst(): void
    {
        $this->assertSame('115682682', HalkNormalizator::pib(115682682));
        $this->assertSame('115682682', HalkNormalizator::pib(115682682.0));
        $this->assertSame('11000', HalkNormalizator::ptt(11000));
        $this->assertSame('5812', HalkNormalizator::mcc(5812));
        $this->assertSame('0742', HalkNormalizator::mcc('742'));
        $this->assertNull(HalkNormalizator::pib(null));
    }

    #[Test]
    public function kontrolna_cifra_pib_a(): void
    {
        $this->assertTrue(HalkNormalizator::pibIspravan('113735946'));
        $this->assertTrue(HalkNormalizator::pibIspravan('115682682'));
        $this->assertTrue(HalkNormalizator::pibIspravan('100165724'));
        $this->assertFalse(HalkNormalizator::pibIspravan('10165724'), '8 cifara (greška u Excelu za KOKA MAR)');
        $this->assertFalse(HalkNormalizator::pibIspravan('111111111'));
        $this->assertFalse(HalkNormalizator::pibIspravan(null));
    }

    #[Test]
    public function komunikacija_se_ujednacava(): void
    {
        $this->assertSame('WIFI', HalkNormalizator::komunikacija('wifi'));
        $this->assertSame('WIFI', HalkNormalizator::komunikacija('WiFI'));
        $this->assertSame('WIFI', HalkNormalizator::komunikacija('WI FI'));
        $this->assertSame('GPRS', HalkNormalizator::komunikacija('GRS'));
        $this->assertSame('GPRS', HalkNormalizator::komunikacija(' gprs '));
        $this->assertSame('ETH', HalkNormalizator::komunikacija('Ethernet'));
        $this->assertNull(HalkNormalizator::komunikacija(''));
    }

    #[Test]
    public function status_bez_obzira_na_velicinu_slova(): void
    {
        $this->assertSame('Deinstaliran', HalkNormalizator::status('deinstaliran'));
        $this->assertSame('Opozvan', HalkNormalizator::status('opozvano'));
        $this->assertSame('Za instalaciju', HalkNormalizator::status(' Za instalaciju '));
        $this->assertSame('Testni', HalkNormalizator::status('Testni'));
        $this->assertNull(HalkNormalizator::status(null));
        $this->assertNull(HalkNormalizator::status('nešto'));
    }

    #[Test]
    public function prazan_status_se_izvodi_kao_u_excel_formuli(): void
    {
        $this->assertSame(['status' => 'Deinstaliran', 'izveden' => false], HalkNormalizator::statusReda(['status' => 'deinstaliran']));
        $this->assertSame(['status' => 'Instaliran', 'izveden' => true], HalkNormalizator::statusReda(['status' => null, 'datum_instalacije' => 46282]));
        $this->assertSame(['status' => 'Za instalaciju', 'izveden' => true], HalkNormalizator::statusReda(['status' => null]));
        $this->assertSame(['status' => 'Opozvan', 'izveden' => true], HalkNormalizator::statusReda(['status' => null, 'sn' => 'OPOZVANO!', 'datum_instalacije' => 46091]));
    }

    #[Test]
    public function napomena_u_koloni_sn_nije_sn(): void
    {
        $this->assertSame('A26-12RB-1K14121', HalkNormalizator::ispravanSn(' a26-12rb-1k14121 '));
        $this->assertNull(HalkNormalizator::ispravanSn('OPOZVANO!'));
        $this->assertNull(HalkNormalizator::ispravanSn('26.05.26. uklonjen sa održavanja po nalogu banke'));
        $this->assertNull(HalkNormalizator::ispravanSn(null));
    }

    #[Test]
    public function datum_iz_excel_serijskog_broja_i_teksta(): void
    {
        $this->assertSame('2026-03-17', HalkNormalizator::datum(46098)->toDateString());
        $this->assertSame('2026-03-17', HalkNormalizator::datum('46098')->toDateString());
        $this->assertSame('2026-10-13', HalkNormalizator::datum('13.10.2026.')->toDateString());
        $this->assertNull(HalkNormalizator::datum(null));
        $this->assertNull(HalkNormalizator::datum('uskoro'));
    }

    #[Test]
    public function tekst_i_telefon_bez_visestrukih_razmaka(): void
    {
        $this->assertSame('PR STUDIO ATELJE HJERA', HalkNormalizator::tekst('PR STUDIO ATELJE HJERA '));
        $this->assertSame('060/608-1025 064 803 1020', HalkNormalizator::telefon('060/608-1025   064 803 1020'));
        $this->assertNull(HalkNormalizator::tekst('   '));
    }

    #[Test]
    public function telefon_za_kontakt_dobija_381_kao_u_formi_lokacije(): void
    {
        $this->assertSame('+38166 500 000 2', HalkNormalizator::telefonZaKontakt('066 500 000 2'));
        $this->assertSame('+381 64 3372518', HalkNormalizator::telefonZaKontakt('+381 64 3372518'));
        $this->assertSame('+381641234567', HalkNormalizator::telefonZaKontakt('00381641234567'));
        $this->assertNull(HalkNormalizator::telefonZaKontakt(''));
    }

    #[Test]
    public function oznaka_iz_broja_postaje_tekst_bez_decimala(): void
    {
        $this->assertSame('2000034603', HalkNormalizator::oznaka(2000034603));
        $this->assertSame('2000034603', HalkNormalizator::oznaka(2000034603.0));
        $this->assertSame('89381030000359086511', HalkNormalizator::oznaka('89381030000359086511'));
        $this->assertSame('integrator', HalkNormalizator::oznaka('integrator'));
    }

    #[Test]
    public function plus_kod(): void
    {
        $this->assertTrue(HalkNormalizator::jePlusKod('VGC9+45 Mrčajevci'));
        $this->assertFalse(HalkNormalizator::jePlusKod('PREKONFIGURACIJA NA PN002927'));
        $this->assertFalse(HalkNormalizator::jePlusKod(null));
    }
}
