<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * Rend toutes les exceptions en réponse JSON structurée.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Throwable                 $e        L'exception attrapée
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function render($request, Throwable $e)
    {
        // Toutes les routes /api/* reçoivent du JSON
        if ($request->is('api/*') || $request->expectsJson()) {

            // 422 : Erreur de validation 
            if ($e instanceof ValidationException) {
                return response()->json([
                    'status'    => 'error',
                    'code'      => 422,
                    'message'   => 'Données invalides. Vérifiez les champs envoyés.',
                    'erreurs'   => $e->errors(), // détail champ par champ
                    'timestamp' => now()->toIso8601String(),
                ], 422);
            }

            // 401 : Non authentifié 
            if ($e instanceof AuthenticationException) {
                return response()->json([
                    'status'    => 'error',
                    'code'      => 401,
                    'message'   => 'Non authentifié. Token manquant ou invalide.',
                    'timestamp' => now()->toIso8601String(),
                ], 401);
            }

            // 403 : Accès refusé
            if ($e instanceof AccessDeniedHttpException) {
                return response()->json([
                    'status'    => 'error',
                    'code'      => 403,
                    'message'   => 'Accès refusé. Vous n\'avez pas les droits nécessaires.',
                    'timestamp' => now()->toIso8601String(),
                ], 403);
            }

            // 404 : Ressource non trouvée 
            if ($e instanceof NotFoundHttpException || $e instanceof ModelNotFoundException) {
                return response()->json([
                    'status'    => 'error',
                    'code'      => 404,
                    'message'   => 'Ressource introuvable.',
                    'timestamp' => now()->toIso8601String(),
                ], 404);
            }

            // 500 : Erreur serveur inattendue 
            // On ne retourne JAMAIS les détails techniques au client
            return response()->json([
                'status'    => 'error',
                'code'      => 500,
                'message'   => 'Une erreur interne est survenue. Contactez l\'administrateur.',
                // En mode debug uniquement, on expose les détails
                'debug'     => config('app.debug') ? $e->getMessage() : null,
                'timestamp' => now()->toIso8601String(),
            ], 500);
        }

        // Pour les routes non-API, comportement Laravel par défaut
        return parent::render($request, $e);
    }
}
