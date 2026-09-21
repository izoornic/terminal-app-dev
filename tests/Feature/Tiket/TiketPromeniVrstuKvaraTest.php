<?php

namespace Tests\Feature\Tiket;

use App\Actions\Tiket\TiketPromeniVrstuKvara;
use App\Models\Tiket;
use App\Models\TiketHistory;
use App\Models\TiketKomentar;
use App\Models\TiketOpisKvaraTip;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Pokriva TiketPromeniVrstuKvara — promena tikets.opis_kvaraId.
 *
 * Statusi (tiket_status_tips): 1 Otvoren, 2 Dodeljen, 3 Zatvoren.
 * Pozicije (pozicija_tips): 1 Admin, 2 Call centar, 3 Šef servisa, 4 Serviser, 5 Prodavac ...
 */
class TiketPromeniVrstuKvaraTest extends TestCase
{
    use RefreshDatabase;

    private TiketOpisKvaraTip $stampac;

    private TiketOpisKvaraTip $citacKartica;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stampac = $this->vrstaKvara('Ne radi štampač');
        $this->citacKartica = $this->vrstaKvara('Ne čita kartice');
    }

    #[Test]
    public function admin_menja_vrstu_kvara(): void
    {
        $admin = $this->userSaPozicijom(1);
        $serviser = $this->userSaPozicijom(4);
        $tiket = $this->tiket(['korisnik_dodeljenId' => $serviser->id, 'tiket_statusId' => 2, 'br_komentara' => 4]);

        TiketPromeniVrstuKvara::promeni($tiket->id, $admin, $this->citacKartica->id);

        $tiket->refresh();
        $this->assertSame($this->citacKartica->id, (int) $tiket->opis_kvaraId);
        $this->assertSame(5, (int) $tiket->br_komentara);
        $this->assertSame(2, (int) $tiket->tiket_statusId);
        $this->assertSame($serviser->id, (int) $tiket->korisnik_dodeljenId);

        $history = TiketHistory::where('tiketId', $tiket->id)->sole();
        $this->assertSame($this->stampac->id, (int) $history->opis_kvaraId);
        $this->assertSame(4, (int) $history->br_komentara);

        $komentar = TiketKomentar::where('tiketId', $tiket->id)->sole();
        $this->assertSame($admin->id, (int) $komentar->korisnikId);
        $this->assertSame('Promenjena vrsta kvara: Ne radi štampač -> Ne čita kartice', $komentar->komentar);
    }

    #[Test]
    public function call_centar_menja_vrstu_kvara(): void
    {
        $tiket = $this->tiket();

        TiketPromeniVrstuKvara::promeni($tiket->id, $this->userSaPozicijom(2), $this->citacKartica->id);

        $this->assertSame($this->citacKartica->id, (int) $tiket->refresh()->opis_kvaraId);
    }

    /**
     * Online prijave i stari tiketi mogu imati opis_kvaraId = null.
     */
    #[Test]
    public function tiket_bez_vrste_kvara_dobija_vrstu(): void
    {
        $tiket = $this->tiket(['opis_kvaraId' => null]);

        TiketPromeniVrstuKvara::promeni($tiket->id, $this->userSaPozicijom(1), $this->stampac->id);

        $this->assertSame($this->stampac->id, (int) $tiket->refresh()->opis_kvaraId);
        $this->assertSame(
            'Promenjena vrsta kvara: --- -> Ne radi štampač',
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
    public function ostale_pozicije_ne_mogu_da_menjaju_vrstu_kvara(int $pozicijaId): void
    {
        $user = $this->userSaPozicijom($pozicijaId);
        $tiket = $this->tiket();

        $this->assertFalse(TiketPromeniVrstuKvara::mozeDaPromeni($user));

        try {
            TiketPromeniVrstuKvara::promeni($tiket->id, $user, $this->citacKartica->id);
            $this->fail('Očekivan AuthorizationException.');
        } catch (AuthorizationException) {
        }

        $this->assertNepromenjen($tiket);
    }

    #[Test]
    public function admin_i_call_centar_imaju_pravo(): void
    {
        $this->assertTrue(TiketPromeniVrstuKvara::mozeDaPromeni($this->userSaPozicijom(1)));
        $this->assertTrue(TiketPromeniVrstuKvara::mozeDaPromeni($this->userSaPozicijom(2)));
    }

    #[Test]
    public function zatvoren_tiket_se_ne_menja(): void
    {
        $tiket = $this->tiket(['tiket_statusId' => 3]);

        $this->assertValidationGreska(
            fn () => TiketPromeniVrstuKvara::promeni($tiket->id, $this->userSaPozicijom(1), $this->citacKartica->id),
            'je zatvoren'
        );

        $this->assertNepromenjen($tiket);
    }

    /**
     * Opcija "---" u selectu stiže kao 0.
     */
    #[Test]
    public function nepostojeca_vrsta_kvara_se_odbija(): void
    {
        $tiket = $this->tiket();
        $admin = $this->userSaPozicijom(1);

        $this->assertValidationGreska(fn () => TiketPromeniVrstuKvara::promeni($tiket->id, $admin, 0), 'Izaberite vrstu kvara');
        $this->assertValidationGreska(fn () => TiketPromeniVrstuKvara::promeni($tiket->id, $admin, 999999), 'Izaberite vrstu kvara');

        $this->assertNepromenjen($tiket);
    }

    #[Test]
    public function ista_vrsta_kvara_ne_pravi_istoriju_ni_komentar(): void
    {
        $tiket = $this->tiket();

        $this->assertValidationGreska(
            fn () => TiketPromeniVrstuKvara::promeni($tiket->id, $this->userSaPozicijom(1), $this->stampac->id),
            'već ima vrstu kvara'
        );

        $this->assertNepromenjen($tiket);
    }

    private function assertNepromenjen(Tiket $tiket): void
    {
        $this->assertSame($this->stampac->id, (int) $tiket->refresh()->opis_kvaraId);
        $this->assertSame(0, TiketHistory::count());
        $this->assertSame(0, TiketKomentar::count());
    }

    private function assertValidationGreska(callable $akcija, string $ocekivanaPoruka): void
    {
        try {
            $akcija();
            $this->fail('Očekivan ValidationException.');
        } catch (ValidationException $e) {
            $this->assertStringContainsString($ocekivanaPoruka, $e->errors()['opisKvara'][0]);
        }
    }

    private function vrstaKvara(string $naziv): TiketOpisKvaraTip
    {
        return TiketOpisKvaraTip::forceCreate([
            'termnal_tipId' => 1,
            'tok_naziv' => $naziv,
            'tok_dodela_nadleznostiId' => 1,
            'list_order' => 1,
        ]);
    }

    private function userSaPozicijom(int $pozicijaId): User
    {
        return User::factory()->create(['pozicija_tipId' => $pozicijaId, 'lokacijaId' => 1]);
    }

    /**
     * @param  array<string, mixed>  $atributi
     */
    private function tiket(array $atributi = []): Tiket
    {
        return Tiket::create(array_merge([
            'tremina_lokacijalId' => 501,
            'tiket_statusId' => 1,
            'opis_kvaraId' => $this->stampac->id,
            'korisnik_prijavaId' => null,
            'korisnik_dodeljenId' => null,
            'opis' => 'Test kvar',
            'tiket_prioritetId' => 1,
            'br_komentara' => 0,
            'korisnik_zatvorio_id' => 0,
        ], $atributi));
    }
}
