<?php

namespace Tests\Feature;

use App\Models\Facture;
use App\Models\Operateur;
use App\Models\Reclamation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ReclamationTest extends TestCase
{
    use RefreshDatabase;

    // ── Helper : retourne un token JWT valide ─────────────────
    private function token(): string
    {
        return JWTAuth::fromUser(Operateur::factory()->create());
    }

    // =========================================================
    // POST /api/reclamations  →  store()
    // =========================================================

    #[Test]
    public function store_cree_une_reclamation_avec_des_donnees_valides(): void
    {
        $facture = Facture::factory()->create();

        $this->withToken($this->token())
             ->postJson('/api/reclamations', [
                 'facture_id'  => $facture->id,
                 'description' => 'Ma facture comporte une erreur sur le montant.',
             ])
             ->assertStatus(201)
             ->assertJsonStructure([
                 'success',
                 'message',
                 'data' => ['id', 'facture_id', 'description', 'statut', 'facture'],
             ])
             ->assertJsonFragment(['success' => true, 'statut' => 'En attente']);

        $this->assertDatabaseHas('reclamations', [
            'facture_id'  => $facture->id,
            'description' => 'Ma facture comporte une erreur sur le montant.',
            'statut'      => 'En attente',
        ]);
    }

    #[Test]
    public function store_echoue_si_facture_id_est_absent(): void
    {
        $this->withToken($this->token())
             ->postJson('/api/reclamations', [
                 'description' => 'Description valide de la réclamation.',
             ])
             ->assertStatus(422)
             ->assertJsonValidationErrors(['facture_id']);
    }

    #[Test]
    public function store_echoue_si_facture_id_nexiste_pas(): void
    {
        $this->withToken($this->token())
             ->postJson('/api/reclamations', [
                 'facture_id'  => 99999,
                 'description' => 'Description valide de la réclamation.',
             ])
             ->assertStatus(422)
             ->assertJsonValidationErrors(['facture_id']);
    }

    #[Test]
    public function store_echoue_si_description_est_absente(): void
    {
        $facture = Facture::factory()->create();

        $this->withToken($this->token())
             ->postJson('/api/reclamations', [
                 'facture_id' => $facture->id,
             ])
             ->assertStatus(422)
             ->assertJsonValidationErrors(['description']);
    }

    #[Test]
    public function store_echoue_si_description_est_trop_courte(): void
    {
        $facture = Facture::factory()->create();

        $this->withToken($this->token())
             ->postJson('/api/reclamations', [
                 'facture_id'  => $facture->id,
                 'description' => 'Court',  // < 10 caractères
             ])
             ->assertStatus(422)
             ->assertJsonValidationErrors(['description']);
    }

    #[Test]
    public function store_echoue_sans_authentification(): void
    {
        $this->postJson('/api/reclamations', [
            'facture_id'  => 1,
            'description' => 'Description valide ici.',
        ])->assertStatus(401);
    }

    // =========================================================
    // PUT /api/reclamations/{id}  →  update()
    // =========================================================

    #[Test]
    public function update_modifie_le_statut_dune_reclamation_existante(): void
    {
        $operateur   = Operateur::factory()->create();
        $reclamation = Reclamation::factory()->create(['statut' => 'En attente']);
        $token       = JWTAuth::fromUser($operateur);

        $this->withToken($token)
             ->putJson("/api/reclamations/{$reclamation->id}", [
                 'statut'  => 'En cours',
                 'reponse' => "Votre dossier est en cours d'examen.",
             ])
             ->assertStatus(200)
             ->assertJsonFragment(['success' => true, 'statut' => 'En cours']);

        $this->assertDatabaseHas('reclamations', [
            'id'     => $reclamation->id,
            'statut' => 'En cours',
        ]);
    }

    #[Test]
    public function update_retourne_404_si_reclamation_introuvable(): void
    {
        $this->withToken($this->token())
             ->putJson('/api/reclamations/99999', ['statut' => 'Résolue'])
             ->assertStatus(404)
             ->assertJsonFragment(['code' => 404]);
    }

    #[Test]
    public function update_echoue_si_statut_est_invalide(): void
    {
        $reclamation = Reclamation::factory()->create();

        $this->withToken($this->token())
             ->putJson("/api/reclamations/{$reclamation->id}", ['statut' => 'Invalide'])
             ->assertStatus(422)
             ->assertJsonValidationErrors(['statut']);
    }

    #[Test]
    public function update_echoue_si_statut_est_absent(): void
    {
        $reclamation = Reclamation::factory()->create();

        $this->withToken($this->token())
             ->putJson("/api/reclamations/{$reclamation->id}", [])
             ->assertStatus(422)
             ->assertJsonValidationErrors(['statut']);
    }

    #[Test]
    public function update_conserve_lancienne_reponse_si_reponse_non_fournie(): void
    {
        $reclamation = Reclamation::factory()->create([
            'statut'  => 'En attente',
            'reponse' => 'Ancienne réponse.',
        ]);

        $this->withToken($this->token())
             ->putJson("/api/reclamations/{$reclamation->id}", ['statut' => 'Résolue'])
             ->assertStatus(200);

        $this->assertDatabaseHas('reclamations', [
            'id'      => $reclamation->id,
            'reponse' => 'Ancienne réponse.',
        ]);
    }
}