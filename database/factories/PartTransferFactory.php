<?php

namespace Database\Factories;

use App\Models\Lokacija;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PartTransfer>
 */
class PartTransferFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sourceLokacija = Lokacija::inRandomOrder()->first()?->id ?? Lokacija::factory()->create()->id;
        $destinationLokacija = Lokacija::where('id', '!=', $sourceLokacija)->inRandomOrder()->first()?->id 
            ?? Lokacija::factory()->create()->id;

        return [
            'transfer_broj' => strtoupper($this->faker->unique()->bothify('TR-####-????')),
            'source_lokacija_id' => $sourceLokacija,
            'destination_lokacija_id' => $destinationLokacija,
            'status' => 'PENDING',
            'kreirao_korisnik_id' => User::inRandomOrder()->first()?->id ?? User::factory()->create()->id,
            'odobrio_korisnik_id' => null,
            'napomena' => $this->faker->optional(0.4)->sentence(10),
            'completed_at' => null,
        ];
    }

    /**
     * Indicate that the transfer is completed.
     */
    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            $completedAt = $this->faker->dateTimeBetween('-30 days', 'now');
            
            return [
                'status' => 'COMPLETED',
                'odobrio_korisnik_id' => User::inRandomOrder()->first()?->id ?? User::factory()->create()->id,
                'completed_at' => $completedAt,
            ];
        });
    }

    /**
     * Indicate that the transfer is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'CANCELLED',
            'odobrio_korisnik_id' => User::inRandomOrder()->first()?->id ?? User::factory()->create()->id,
        ]);
    }

    /**
     * Set pending status explicitly.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'PENDING',
            'odobrio_korisnik_id' => null,
            'completed_at' => null,
        ]);
    }

    /**
     * Set specific source location.
     */
    public function fromLocation(int $lokacijaId): static
    {
        return $this->state(fn (array $attributes) => [
            'source_lokacija_id' => $lokacijaId,
        ]);
    }

    /**
     * Set specific destination location.
     */
    public function toLocation(int $lokacijaId): static
    {
        return $this->state(fn (array $attributes) => [
            'destination_lokacija_id' => $lokacijaId,
        ]);
    }

    /**
     * Set specific creator user.
     */
    public function createdBy(int $userId): static
    {
        return $this->state(fn (array $attributes) => [
            'kreirao_korisnik_id' => $userId,
        ]);
    }

    /**
     * Set specific approver user.
     */
    public function approvedBy(int $userId): static
    {
        return $this->state(fn (array $attributes) => [
            'odobrio_korisnik_id' => $userId,
        ]);
    }

    /**
     * Add a note to the transfer.
     */
    public function withNote(string $napomena): static
    {
        return $this->state(fn (array $attributes) => [
            'napomena' => $napomena,
        ]);
    }

    /**
     * Set completed at specific date.
     */
    public function completedAt($date): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'COMPLETED',
            'completed_at' => $date,
            'odobrio_korisnik_id' => $attributes['odobrio_korisnik_id'] 
                ?? User::inRandomOrder()->first()?->id 
                ?? User::factory()->create()->id,
        ]);
    }

    /**
     * Create transfer between specific locations.
     */
    public function between(int $sourceId, int $destinationId): static
    {
        return $this->state(fn (array $attributes) => [
            'source_lokacija_id' => $sourceId,
            'destination_lokacija_id' => $destinationId,
        ]);
    }
}