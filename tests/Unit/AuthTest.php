<?php

namespace Tests\Feature;

use App\Models\Operateur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================
    // POST /api/auth/login  →  login()
    // =========================================================

    #[Test]
    public function login_retourne_un_token_avec_des_identifiants_valides(): void
    {
        $operateur = Operateur::factory()->avecMotDePasse('secret123')->create([
            'login' => 'admin',
        ]);

        $this->postJson('/api/auth/login', [
            'login'    => 'admin',
            'password' => 'secret123',
        ])->assertStatus(200)
          ->assertJsonStructure([
              'message',
              'token',
              'operateur' => ['id', 'nom', 'prenom', 'login', 'role'],
          ])
          ->assertJsonFragment(['message' => 'Connexion réussie.', 'login' => 'admin']);
    }

    #[Test]
    public function login_echoue_avec_un_mot_de_passe_incorrect(): void
    {
        Operateur::factory()->avecMotDePasse('secret123')->create(['login' => 'admin']);

        $this->postJson('/api/auth/login', [
            'login'    => 'admin',
            'password' => 'mauvais',
        ])->assertStatus(401)
          ->assertJsonFragment(['message' => 'Identifiants incorrects.']);
    }

    #[Test]
    public function login_echoue_avec_un_login_inconnu(): void
    {
        $this->postJson('/api/auth/login', [
            'login'    => 'inconnu',
            'password' => 'nimporte',
        ])->assertStatus(401)
          ->assertJsonFragment(['message' => 'Identifiants incorrects.']);
    }

    #[Test]
    public function login_echoue_si_le_champ_login_est_absent(): void
    {
        $this->postJson('/api/auth/login', [
            'password' => 'secret123',
        ])->assertStatus(422)
          ->assertJsonValidationErrors(['login']);
    }

    #[Test]
    public function login_echoue_si_le_champ_password_est_absent(): void
    {
        $this->postJson('/api/auth/login', [
            'login' => 'admin',
        ])->assertStatus(422)
          ->assertJsonValidationErrors(['password']);
    }

    // =========================================================
    // POST /api/auth/logout  →  logout()
    // =========================================================

    #[Test]
    public function logout_invalide_le_token_jwt(): void
    {
        $token = JWTAuth::fromUser(Operateur::factory()->create());

        $this->withToken($token)
             ->postJson('/api/auth/logout')
             ->assertStatus(200)
             ->assertJsonFragment(['message' => 'Déconnexion réussie.']);
    }

    #[Test]
    public function logout_echoue_sans_token(): void
    {
        $this->postJson('/api/auth/logout')->assertStatus(401);
    }

    // =========================================================
    // GET /api/auth/me  →  me()
    // =========================================================

    #[Test]
    public function me_retourne_les_infos_de_loperateur_connecte(): void
    {
        $operateur = Operateur::factory()->create();
        $token     = JWTAuth::fromUser($operateur);

        $this->withToken($token)
             ->getJson('/api/auth/me')
             ->assertStatus(200)
             ->assertJsonFragment([
                 'id'    => $operateur->id,
                 'login' => $operateur->login,
             ]);
    }

    #[Test]
    public function me_echoue_sans_token(): void
    {
        $this->getJson('/api/auth/me')->assertStatus(401);
    }

    #[Test]
    public function me_echoue_avec_un_token_invalide(): void
    {
        $this->withToken('token.invalide.ici')
             ->getJson('/api/auth/me')
             ->assertStatus(401);
    }
}