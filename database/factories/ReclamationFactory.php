<?php

namespace Database\Factories;

use App\Models\Facture;
use App\Models\Operateur;
use App\Models\Reclamation;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReclamationFactory extends Factory
{
    protected $model = Reclamation::class;

    public function definition(): array
    {
        return [
            'facture_id'  => Facture::factory(),
            'description' => $this->faker->sentence(15),  // > 10 caractères garanti
            'statut'      => 'En attente',
            'reponse'     => null,
            'operateur_id'=> null,
        ];
    }

    // ── États pratiques ──────────────────────────────────────

    public function enCours(): static
    {
        return $this->state([
            'statut'       => 'En cours',
            'operateur_id' => Operateur::factory(),
        ]);
    }

    public function resolue(): static
    {
        return $this->state([
            'statut'       => 'Résolue',
            'reponse'      => $this->faker->paragraph(),
            'operateur_id' => Operateur::factory(),
        ]);
    }
}