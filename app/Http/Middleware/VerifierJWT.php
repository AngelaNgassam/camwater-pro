<?php

namespace App\Http\Middleware;

// ============================================================
// Middleware : VerifierJWT
//
// Rôle : intercepte chaque requête protégée et vérifie que
// le token JWT est présent, valide, non expiré et non révoqué.
//
// Utilisation dans routes/api.php :
//   Route::middleware('jwt')->group(function() { ... });
//
// En cas d'échec : retourne HTTP 401 avec message JSON.
// En cas de succès : injecte l'opérateur dans la requête
// via $request->operateur pour les contrôleurs suivants.
// ============================================================

use App\Http\Controllers\AuthController;
use App\Models\Operateur;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifierJWT
{
    // On injecte AuthController pour réutiliser verifierToken()
    private AuthController $authController;

    public function __construct(AuthController $authController)
    {
        $this->authController = $authController;
    }

    /**
     * Intercepte la requête et vérifie le token JWT.
     *
     * @param  Request  $request  La requête HTTP entrante
     * @param  Closure  $next     La prochaine étape (contrôleur)
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // ── Étape 1 : Extraire le token depuis Authorization: Bearer ──────
        $entete = $request->header('Authorization');

        if (!$entete || !str_starts_with($entete, 'Bearer ')) {
            return response()->json([
                'status'    => 'error',
                'code'      => 401,
                'message'   => 'Token manquant. Ajoutez Authorization: Bearer <token> dans les en-têtes.',
                'timestamp' => now()->toIso8601String(),
            ], 401);
        }

        $token = substr($entete, 7); // Retirer "Bearer "

        // ── Étape 2 : Vérifier la validité du token ───────────────────────
        $payload = $this->authController->verifierToken($token);

        if ($payload === null) {
            return response()->json([
                'status'    => 'error',
                'code'      => 401,
                'message'   => 'Token invalide, malformé ou expiré. Reconnectez-vous.',
                'timestamp' => now()->toIso8601String(),
            ], 401);
        }

        // ── Étape 3 : Charger l'opérateur depuis la base ──────────────────
        $operateur = Operateur::authenticate();

        if ($operateur === null) {
            return response()->json([
                'status'    => 'error',
                'code'      => 401,
                'message'   => 'Opérateur introuvable.',
                'timestamp' => now()->toIso8601String(),
            ], 401);
        }

        // ── Étape 4 : Injecter l'opérateur dans la requête ────────────────
        // Les contrôleurs peuvent accéder à $request->operateur
        $request->merge(['operateur' => $operateur]);
        $request->attributes->set('operateur', $operateur);

        // Continuer vers le contrôleur
        return $next($request);
    }
}
