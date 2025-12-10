<?php

namespace Database\Factories;

use App\Models\Lokacija;
use App\Models\PartType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PartStock>
 */
class PartStockFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $kolicinaUkupno = $this->faker->numberBetween(0, 100);
        $kolicinaRezervisana = $this->faker->numberBetween(0, min($kolicinaUkupno, 20));

        return [
            'part_type_id' => PartType::inRandomOrder()->first()?->id ?? PartType::factory()->create()->id,
            'lokacija_id' => Lokacija::inRandomOrder()->first()?->id ?? Lokacija::factory()->create()->id,
            'kolicina_ukupno' => $kolicinaUkupno,
            'kolicina_rezervisana' => $kolicinaRezervisana,
            // kolicina_dostupna je generated column - ne treba je postavljati
        ];
    }

    /**
     * Set specific part type.
     */
    public function forPartType(int $partTypeId): static
    {
        return $this->state(fn (array $attributes) => [
            'part_type_id' => $partTypeId,
        ]);
    }

    /**
     * Set specific location.
     */
    public function atLocation(int $lokacijaId): static
    {
        return $this->state(fn (array $attributes) => [
            'lokacija_id' => $lokacijaId,
        ]);
    }

    /**
     * Set specific part at specific location.
     */
    public function forPartTypeAtLocation(int $partTypeId, int $lokacijaId): static
    {
        return $this->state(fn (array $attributes) => [
            'part_type_id' => $partTypeId,
            'lokacija_id' => $lokacijaId,
        ]);
    }

    /**
     * Set high stock quantity.
     */
    public function highStock(): static
    {
        return $this->state(function (array $attributes) {
            $kolicina = $this->faker->numberBetween(100, 500);
            return [
                'kolicina_ukupno' => $kolicina,
                'kolicina_rezervisana' => $this->faker->numberBetween(0, (int)($kolicina * 0.3)),
            ];
        });
    }

    /**
     * Set low stock quantity.
     */
    public function lowStock(): static
    {
        return $this->state(function (array $attributes) {
            $kolicina = $this->faker->numberBetween(1, 10);
            return [
                'kolicina_ukupno' => $kolicina,
                'kolicina_rezervisana' => $this->faker->numberBetween(0, max(1, (int)($kolicina * 0.5))),
            ];
        });
    }

    /**
     * Set out of stock.
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'kolicina_ukupno' => 0,
            'kolicina_rezervisana' => 0,
        ]);
    }

    /**
     * Set fully reserved stock.
     */
    public function fullyReserved(): static
    {
        return $this->state(function (array $attributes) {
            $kolicina = $this->faker->numberBetween(10, 50);
            return [
                'kolicina_ukupno' => $kolicina,
                'kolicina_rezervisana' => $kolicina,
            ];
        });
    }

    /**
     * Set specific quantities.
     */
    public function withQuantities(int $ukupno, int $rezervisana = 0): static
    {
        return $this->state(fn (array $attributes) => [
            'kolicina_ukupno' => $ukupno,
            'kolicina_rezervisana' => min($rezervisana, $ukupno),
        ]);
    }

    /**
     * Set no reserved quantity.
     */
    public function noReservations(): static
    {
        return $this->state(fn (array $attributes) => [
            'kolicina_rezervisana' => 0,
        ]);
    }

    /**
     * Set with some reservations (30-50% of total).
     */
    public function withReservations(): static
    {
        return $this->state(function (array $attributes) {
            $ukupno = $attributes['kolicina_ukupno'] ?? $this->faker->numberBetween(20, 100);
            $procenat = $this->faker->numberBetween(30, 50) / 100;
            
            return [
                'kolicina_ukupno' => $ukupno,
                'kolicina_rezervisana' => (int)($ukupno * $procenat),
            ];
        });
    }
}