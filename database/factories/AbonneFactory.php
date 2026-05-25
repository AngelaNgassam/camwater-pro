<?php

namespace Database\Factories;

use App\Models\Abonne;
use Illuminate\Database\Eloquent\Factories\Factory;

class AbonneFactory extends Factory
{
    protected $model = Abonne::class;

    public function definition(): array
    {
        return [
            // setNomAttribute stocke en majuscules — on passe une valeur normale
            'nom'              => $this->faker->lastName(),
            'prenom'           => $this->faker->firstName(),
            'ville'            => $this->faker->randomElement(['Yaoundé', 'Douala', 'Bafoussam', 'Garoua']),
            'quartier'         => $this->faker->streetName(),
            'numero_compteur'  => $this->faker->unique()->numerify('CPT-#####'),
            'type_abonnement'  => $this->faker->randomElement(['Domestique', 'Professionnel']),
        ];
    }

    // ── États pratiques ──────────────────────────────────────

    /** Abonné de type Domestique */
    public function domestique(): static
    {
        return $this->state(['type_abonnement' => 'Domestique']);
    }

    /** Abonné de type Professionnel */
    public function professionnel(): static
    {
        return $this->state(['type_abonnement' => 'Professionnel']);
    }

    /** Abonné dans une ville précise */
    public function ville(string $ville): static
    {
        return $this->state(['ville' => $ville]);
    }
}