<?php

namespace Tests\Unit;

use App\Http\Controllers\Distributer\DistPredracunControler;
use App\Http\Livewire\DistributerLokacija;
use ErrorException;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ReflectionObject;

/**
 * Dinamička svojstva su deprecirana od PHP 8.2 i biće fatalna u PHP 9.
 * Ovi testovi hvataju mjesta koja su se oslanjala na njih.
 */
class DinamickaSvojstvaTest extends TestCase
{
    /**
     * Podigne E_DEPRECATED u izuzetak za trajanje callback-a.
     */
    private function bezDeprecacija(callable $callback): mixed
    {
        set_error_handler(
            static function (int $severity, string $message, string $file, int $line): bool {
                throw new ErrorException($message, 0, $severity, $file, $line);
            },
            E_DEPRECATED
        );

        try {
            return $callback();
        } finally {
            restore_error_handler();
        }
    }

    public function test_vrsta_dokumenta_ne_pravi_dinamicka_svojstva(): void
    {
        $metoda = new ReflectionMethod(DistPredracunControler::class, 'vrstaDokumenta');

        $predracun = $this->bezDeprecacija(
            fn (): object => $metoda->invoke(new DistPredracunControler(), 'p')
        );

        $this->assertSame('p', $predracun->tip);
        $this->assertSame('Predračun', $predracun->naslov);
        $this->assertSame('Za uplatu', $predracun->placanje);
        $this->assertSame('Datum dospeća:', $predracun->datum);
    }

    public function test_vrsta_dokumenta_vraca_racun_za_tip_r(): void
    {
        $metoda = new ReflectionMethod(DistPredracunControler::class, 'vrstaDokumenta');

        $racun = $this->bezDeprecacija(
            fn (): object => $metoda->invoke(new DistPredracunControler(), 'r')
        );

        $this->assertSame('r', $racun->tip);
        $this->assertSame('Račun', $racun->naslov);
        $this->assertSame('Ukupno', $racun->placanje);
        $this->assertSame('Datum:', $racun->datum);
    }

    /**
     * Livewire ne serijalizuje nedeklarisana svojstva, pa `modelId` mora biti
     * deklarisan da bi preživio zahtjev između otvaranja modala i brisanja.
     */
    public function test_distributer_lokacija_ima_deklarisan_model_id(): void
    {
        $komponenta = new DistributerLokacija();

        $this->assertTrue(
            (new ReflectionObject($komponenta))->hasProperty('modelId'),
            'DistributerLokacija::$modelId mora biti deklarisan, ne dinamički.'
        );

        $this->bezDeprecacija(function () use ($komponenta): void {
            $komponenta->modelId = 42;
        });

        $this->assertSame(42, $komponenta->modelId);
    }
}
