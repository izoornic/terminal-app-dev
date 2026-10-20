<?php

namespace Database\Factories;

use App\Models\TerminalAcq;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Podrazumevano TID bez uređaja i bez statusa; stanje se bira state metodama.
 *
 * @extends Factory<TerminalAcq>
 */
class TerminalAcqFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'acq_tid' => $this->faker->unique()->numerify('PN######'),
            'naziv_na_slipu' => strtoupper($this->faker->company()),
            'komunikacija' => $this->faker->randomElement(['GPRS', 'WIFI', 'ETH']),
            'testni' => false,
            'izvor' => TerminalAcq::IZVOR_RUCNO,
            'korisnikId' => 1,
            'korisnikIme' => 'Test',
        ];
    }

    /**
     * TID pod uređajem: status i lokacija se nasleđuju iz terminal_lokacijas.
     */
    public function saUredjajem(int $terminalId): static
    {
        return $this->state(fn (array $attributes) => [
            'terminalId' => $terminalId,
            'terminal_statusId' => null,
            'lokacijaId' => null,
        ]);
    }

    /**
     * TID bez uređaja, sa sopstvenim statusom i lokacijom.
     */
    public function bezUredjaja(int $statusId, ?int $lokacijaId = null): static
    {
        return $this->state(fn (array $attributes) => [
            'terminalId' => null,
            'terminal_statusId' => $statusId,
            'lokacijaId' => $lokacijaId,
        ]);
    }
}
