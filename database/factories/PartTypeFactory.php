<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PartType>
 */
class PartTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sifra' => strtoupper($this->faker->unique()->bothify('PT-####-???')),
            'naziv' => $this->faker->words(3, true),
            'opis' => $this->faker->optional(0.7)->sentence(12),
            'category_id' => null, //$this->faker->optional(0.8)->numberBetween(1, 5),
            'cena' => $this->faker->randomFloat(2, 10, 5000),
            'jedinica_mere' => $this->faker->randomElement(['kom', 'kg', 'l', 'm', 'm2', 'set', 'par']),
            'min_kolicina' => $this->faker->numberBetween(0, 50),
            'aktivan' => true,
        ];
    }

    /**
     * Indicate that the part type is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'aktivan' => false,
        ]);
    }

    /**
     * Set specific category.
     */
    public function inCategory(int $categoryId): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => $categoryId,
        ]);
    }

    /**
     * Set without category.
     */
    public function withoutCategory(): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => null,
        ]);
    }

    /**
     * Set specific price range.
     */
    public function priceRange(float $min, float $max): static
    {
        return $this->state(fn (array $attributes) => [
            'cena' => $this->faker->randomFloat(2, $min, $max),
        ]);
    }

    /**
     * Set as expensive item.
     */
    public function expensive(): static
    {
        return $this->state(fn (array $attributes) => [
            'cena' => $this->faker->randomFloat(2, 5000, 50000),
        ]);
    }

    /**
     * Set as cheap item.
     */
    public function cheap(): static
    {
        return $this->state(fn (array $attributes) => [
            'cena' => $this->faker->randomFloat(2, 1, 100),
        ]);
    }

    /**
     * Set specific unit of measure.
     */
    public function unit(string $jedinica): static
    {
        return $this->state(fn (array $attributes) => [
            'jedinica_mere' => $jedinica,
        ]);
    }

    /**
     * Set low stock threshold.
     */
    public function lowStockThreshold(int $threshold): static
    {
        return $this->state(fn (array $attributes) => [
            'min_kolicina' => $threshold,
        ]);
    }

    /**
     * Create complete part type with all fields.
     */
    public function complete(): static
    {
        return $this->state(fn (array $attributes) => [
            'opis' => $this->faker->paragraph(),
            'category_id' => $this->faker->numberBetween(1, 20),
        ]);
    }
}