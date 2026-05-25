<?php

namespace Database\Factories;

use App\Models\Operateur;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class OperateurFactory extends Factory
{
    protected $model = Operateur::class;

    public function definition(): array
    {
        return [
            'nom'      => $this->faker->lastName(),
            'prenom'   => $this->faker->firstName(),
            'login'    => $this->faker->unique()->userName(),
            // On passe le hash directement pour contourner le mutateur bcrypt()
            // qui re-hacherait un hash déjà haché — utile pour les tests JWT
            'password' => Hash::make('password'),
            'role'     => $this->faker->randomElement(['admin', 'gestionnaire']),
        ];
    }

    // ── États pratiques ──────────────────────────────────────

    /**
     * Opérateur avec un mot de passe connu (pour les tests de login).
     *
     * On utilise afterCreating() pour mettre à jour le mot de passe DIRECTEMENT
     * en base via une requête brute, court-circuitant le mutateur setPasswordAttribute()
     * qui appellerait bcrypt() une deuxième fois sur un hash déjà haché.
     *
     * La factory crée d'abord l'opérateur avec le mot de passe par défaut ('password'),
     * puis afterCreating() remplace la valeur en base avec le hash correct.
     */
    public function avecMotDePasse(string $motDePasse): static
    {
        $hash = Hash::make($motDePasse);

        return $this->afterCreating(function (Operateur $operateur) use ($hash) {
            $operateur->newQuery()
                      ->where('id', $operateur->id)
                      ->update(['password' => $hash]);

            // Synchroniser l'instance en mémoire pour les assertions éventuelles
            $operateur->setRawAttributes(
                array_merge($operateur->getAttributes(), ['password' => $hash])
            );
        });
    }

    public function admin(): static
    {
        return $this->state(['role' => 'admin']);
    }

    public function gestionnaire(): static
    {
        return $this->state(['role' => 'gestionnaire']);
    }
}