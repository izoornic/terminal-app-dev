<?php

namespace Database\Factories;

use App\Models\Lokacija;
use Illuminate\Database\Eloquent\Factories\Factory;

class LokacijaFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Lokacija::class;

     /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'is_duplicate' => false,
            'regionId' => $this->faker->randomElement([1, 2, 4]),
            'lokacija_tipId' => $this->faker->numberBetween(1, 4),
            'l_naziv' => $this->faker->company(),
            'l_naziv_sufix' => $this->faker->optional(0.3)->companySuffix(),
            'mesto' => $this->faker->city(),
            'adresa' => $this->faker->streetAddress(),
            'latitude' => $this->faker->latitude(42.0, 46.0), // Koordinate za Srbiju
            'longitude' => $this->faker->longitude(19.0, 23.0),
            'pib' => $this->faker->optional(0.8)->numerify('##########'),
            'distributerId' => $this->faker->optional(0.7)->numberBetween(1, 20),
            'mb' => $this->faker->optional(0.8)->numerify('########'),
            'email' => $this->faker->optional(0.6)->safeEmail(),
        ];
    }

    /**
     * Indicate that the location is a duplicate.
     */
    public function duplicate(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_duplicate' => true,
        ]);
    }

    /**
     * Set specific region.
     */
    public function inRegion(int $regionId): static
    {
        return $this->state(fn (array $attributes) => [
            'regionId' => $regionId,
        ]);
    }

    /**
     * Set specific location type.
     */
    public function ofType(int $tipId): static
    {
        return $this->state(fn (array $attributes) => [
            'lokacija_tipId' => $tipId,
        ]);
    }

    /**
     * Include all optional fields.
     */
    public function complete(): static
    {
        return $this->state(fn (array $attributes) => [
            'l_naziv_sufix' => $this->faker->companySuffix(),
            'pib' => $this->faker->numerify('##########'),
            'distributerId' => $this->faker->numberBetween(1, 20),
            'mb' => $this->faker->numerify('########'),
            'email' => $this->faker->safeEmail(),
        ]);
    }
}