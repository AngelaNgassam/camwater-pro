<?php

namespace App\Http\Middleware;

// ============================================================
// Middleware : VerifierRole
//
// Rôle : après validation du JWT par VerifierJWT, ce middleware
// s'assure que l'opérateur possède le rôle requis pour accéder
// à la route.
//
// Utilisation dans routes/api.php :
//   Route::middleware(['jwt', 'role:admin'])->group(...)
//   Route::middleware(['jwt', 'role:gestionnaire'])->group(...)
//
// En cas de rôle insuffisant : retourne HTTP 403.
// ============================================================

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifierRole
{
    /**
     * Vérifie que l'opérateur connecté possède le rôle requis.
     *
     * @param  Request  $request      La requête HTTP (contient $request->operateur)
     * @param  Closure  $next         La prochaine étape
     * @param  string   $roleRequis   Le rôle attendu : 'admin' ou 'gestionnaire'
     * @return Response
     */
    public function handle(Request $request, Closure $next, string $roleRequis): Response
    {
        // Récupérer l'opérateur injecté par le middleware VerifierJWT
        $operateur = $request->attributes->get('operateur');

        if ($operateur === null) {
            return response()->json([
                'status'    => 'error',
                'code'      => 401,
                'message'   => 'Non authentifié.',
                'timestamp' => now()->toIso8601String(),
            ], 401);
        }

        // L'admin a accès à tout — le gestionnaire seulement à son rôle
        // Hiérarchie : admin > gestionnaire
        $rolesAutorises = match($roleRequis) {
            'admin'        => ['admin'],              // admin uniquement
            'gestionnaire' => ['admin', 'gestionnaire'], // les deux peuvent
            default        => [],
        };

        if (!in_array($operateur->role, $rolesAutorises)) {
            return response()->json([
                'status'    => 'error',
                'code'      => 403,
                'message'   => "Accès refusé. Rôle requis : {$roleRequis}. Votre rôle : {$operateur->role}.",
                'timestamp' => now()->toIso8601String(),
            ], 403);
        }

        return $next($request);
    }
}
