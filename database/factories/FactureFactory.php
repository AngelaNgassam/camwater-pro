<?php

namespace Database\Factories;

use App\Models\Abonne;
use App\Models\Facture;
use Illuminate\Database\Eloquent\Factories\Factory;

class FactureFactory extends Factory
{
    protected $model = Facture::class;

    public function definition(): array
    {
        return [
            'abonne_id'     => Abonne::factory(),
            'consommation'  => $this->faker->numberBetween(1, 100),   // > 0 (contrainte CHECK)
            'montant_total' => $this->faker->numberBetween(1000, 500000),
            'date_emission' => $this->faker->dateTimeBetween('-12 months', 'now')->format('Y-m-d'),
            'statut'        => $this->faker->randomElement(['Emise', 'Payée']),
        ];
    }

    // ── États pratiques ──────────────────────────────────────

    /** Facture émise ce mois-ci */
    public function cemois(): static
    {
        return $this->state([
            'date_emission' => now()->format('Y-m-d'),
        ]);
    }

    /** Facture avec une date d'émission précise */
    public function avecDate(string $date): static
    {
        return $this->state(['date_emission' => $date]);
    }

    /** Facture non payée */
    public function emise(): static
    {
        return $this->state(['statut' => 'Emise']);
    }

    /** Facture payée */
    public function payee(): static
    {
        return $this->state(['statut' => 'Payée']);
    }
}