<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Test de santé basique — vérifie que l'API répond.
 * On teste /api/auth/login (route existante) plutôt que /
 * qui dépend de la config web non disponible en CI.
 */
class ExampleTest extends TestCase
{
    public function test_the_api_is_reachable(): void
    {
        $response = $this->getJson('/api/auth/login');

        // 405 Method Not Allowed = la route existe mais attend un POST → OK
        // 422 Unprocessable      = la route existe et valide              → OK
        $this->assertContains($response->status(), [405, 422]);
    }
}