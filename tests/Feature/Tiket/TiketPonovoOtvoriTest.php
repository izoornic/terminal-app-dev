<?php

namespace Tests\Feature\Tiket;

use App\Actions\Tiket\TiketPonovoOtvori;
use App\Models\Tiket;
use App\Models\TiketHistory;
use App\Models\TiketKomentar;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Pokriva TiketPonovoOtvori — vraćanje zatvorenog tiketa u otvoren.
 *
 * Statusi (tiket_status_tips): 1 Otvoren, 2 Dodeljen, 3 Zatvoren.
 * Pozicije (pozicija_tips): 1 Admin, 2 Call centar, 3 Šef servisa, 4 Serviser, 5 Prodavac ...
 */
class TiketPonovoOtvoriTest extends TestCase
{
    use RefreshDatabase;

    private const TERMINAL_LOKACIJA_ID = 501;

    #[Test]
    public function admin_ponovo_otvara_zatvoren_nedodeljen_tiket(): void
    {
        $admin = $this->userSaPozicijom(1);
        $zatvorio = $this->userSaPozicijom(4);
        $tiket = $this->zatvorenTiket(['korisnik_zatvorio_id' => $zatvorio->id, 'br_komentara' => 2]);

        $noviStatus = TiketPonovoOtvori::otvori($tiket->id, $admin);

        $this->assertSame(1, $noviStatus);
        $tiket->refresh();
        $this->assertSame(1, (int) $tiket->tiket_statusId);
        $this->assertSame(0, (int) $tiket->korisnik_zatvorio_id);
        $this->assertSame(3, (int) $tiket->br_komentara);

        $history = TiketHistory::where('tiketId', $tiket->id)->sole();
        $this->assertSame(3, (int) $history->tiket_statusId);
        $this->assertSame($zatvorio->id, (int) $history->korisnik_zatvorio_id);
        $this->assertSame(2, (int) $history->br_komentara);

        $komentar = TiketKomentar::where('tiketId', $tiket->id)->sole();
        $this->assertSame($admin->id, (int) $komentar->korisnikId);
        $this->assertSame('Tiket je ponovo otvoren.', $komentar->komentar);
    }

    #[Test]
    public function call_centar_ponovo_otvara_dodeljen_tiket_koji_ostaje_dodeljen(): void
    {
        $callCentar = $this->userSaPozicijom(2);
        $serviser = $this->userSaPozicijom(4);
        $tiket = $this->zatvorenTiket(['korisnik_dodeljenId' => $serviser->id]);

        $noviStatus = TiketPonovoOtvori::otvori($tiket->id, $callCentar, '  Kvar se ponovio  ');

        $this->assertSame(2, $noviStatus);
        $tiket->refresh();
        $this->assertSame(2, (int) $tiket->tiket_statusId);
        $this->assertSame($serviser->id, (int) $tiket->korisnik_dodeljenId);
        $this->assertSame(
            'Tiket je ponovo otvoren. Razlog: Kvar se ponovio',
            TiketKomentar::where('tiketId', $tiket->id)->sole()->komentar
        );
    }

    /**
     * @return array<string, array{int}>
     */
    public static function nedozvoljenePozicije(): array
    {
        return [
            'Šef servisa' => [3],
            'Serviser' => [4],
            'Prodavac' => [5],
            'Menadžer licenci' => [6],
            'Distributer' => [8],
            'Admin bankomata' => [9],
        ];
    }

    #[Test]
    #[DataProvider('nedozvoljenePozicije')]
    public function ostale_pozicije_ne_mogu_da_otvore_tiket(int $pozicijaId): void
    {
        $user = $this->userSaPozicijom($pozicijaId);
        $tiket = $this->zatvorenTiket();

        $this->assertFalse(TiketPonovoOtvori::mozeDaOtvori($user));

        try {
            TiketPonovoOtvori::otvori($tiket->id, $user);
            $this->fail('Očekivan AuthorizationException.');
        } catch (AuthorizationException) {
        }

        $this->assertSame(3, (int) $tiket->refresh()->tiket_statusId);
        $this->assertSame(0, TiketHistory::count());
        $this->assertSame(0, TiketKomentar::count());
    }

    #[Test]
    public function admin_i_call_centar_imaju_pravo(): void
    {
        $this->assertTrue(TiketPonovoOtvori::mozeDaOtvori($this->userSaPozicijom(1)));
        $this->assertTrue(TiketPonovoOtvori::mozeDaOtvori($this->userSaPozicijom(2)));
    }

    #[Test]
    public function tiket_koji_nije_zatvoren_se_ne_menja(): void
    {
        $admin = $this->userSaPozicijom(1);
        $tiket = $this->zatvorenTiket(['tiket_statusId' => 2]);

        $this->assertValidationGreska(fn () => TiketPonovoOtvori::otvori($tiket->id, $admin), 'nije zatvoren');

        $this->assertSame(2, (int) $tiket->refresh()->tiket_statusId);
        $this->assertSame(0, TiketHistory::count());
        $this->assertSame(0, TiketKomentar::count());
    }

    /**
     * Na terminalu sme biti samo jedan otvoren tiket (Tiket::daliTerminalImaOtvorenTiket) —
     * ako je posle zatvaranja otvoren novi, stari se ne sme vratiti.
     */
    #[Test]
    public function ne_otvara_tiket_ako_terminal_vec_ima_otvoren_tiket(): void
    {
        $admin = $this->userSaPozicijom(1);
        $zatvoren = $this->zatvorenTiket();
        $noviOtvoren = $this->zatvorenTiket(['tiket_statusId' => 1]);

        $this->assertValidationGreska(
            fn () => TiketPonovoOtvori::otvori($zatvoren->id, $admin),
            'otvoren tiket #'.$noviOtvoren->id
        );

        $this->assertSame(3, (int) $zatvoren->refresh()->tiket_statusId);
        $this->assertSame(0, TiketHistory::count());
    }

    #[Test]
    public function zatvoren_tiket_na_drugom_terminalu_ne_blokira_otvaranje(): void
    {
        $admin = $this->userSaPozicijom(1);
        $tiket = $this->zatvorenTiket();
        $this->zatvorenTiket(['tiket_statusId' => 1, 'tremina_lokacijalId' => self::TERMINAL_LOKACIJA_ID + 1]);
        $this->zatvorenTiket();

        TiketPonovoOtvori::otvori($tiket->id, $admin);

        $this->assertSame(1, (int) $tiket->refresh()->tiket_statusId);
    }

    private function assertValidationGreska(callable $akcija, string $ocekivanaPoruka): void
    {
        try {
            $akcija();
            $this->fail('Očekivan ValidationException.');
        } catch (ValidationException $e) {
            $this->assertStringContainsString($ocekivanaPoruka, $e->errors()['ponovoOtvori'][0]);
        }
    }

    private function userSaPozicijom(int $pozicijaId): User
    {
        return User::factory()->create(['pozicija_tipId' => $pozicijaId, 'lokacijaId' => 1]);
    }

    /**
     * @param  array<string, mixed>  $atributi
     */
    private function zatvorenTiket(array $atributi = []): Tiket
    {
        return Tiket::create(array_merge([
            'tremina_lokacijalId' => self::TERMINAL_LOKACIJA_ID,
            'tiket_statusId' => 3,
            'opis_kvaraId' => null,
            'korisnik_prijavaId' => null,
            'korisnik_dodeljenId' => null,
            'opis' => 'Test kvar',
            'tiket_prioritetId' => 1,
            'br_komentara' => 0,
            'korisnik_zatvorio_id' => 0,
        ], $atributi));
    }
}
