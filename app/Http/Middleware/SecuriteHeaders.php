<?php

namespace App\Http\Middleware;

// ============================================================
// Middleware : SecuriteHeaders
//
// Rôle : ajoute des en-têtes de sécurité HTTP à TOUTES les
// réponses de l'API, et configure la politique CORS.
//
// En-têtes ajoutés :
//   - Content-Security-Policy  : limite les sources de contenu
//   - X-Content-Type-Options   : empêche le MIME sniffing
//   - X-Frame-Options          : empêche le clickjacking
//
// CORS : seules 2 origines sont autorisées :
//   - https://app.camwater.cm
//   - https://admin.camwater.cm
// ============================================================

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecuriteHeaders
{
    // Origines autorisées pour les requêtes cross-origin
    private const ORIGINES_AUTORISEES = [
        'https://app.camwater.cm',
        'https://admin.camwater.cm',
        'https://kenga.cdwfs.net',
        'http://kenga.cdwfs.net',  // Pour HTTP si nécessaire
    ];

    /**
     * Ajoute les en-têtes de sécurité à chaque réponse.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Traiter la requête d'abord
        $response = $next($request);

        // ── En-têtes de sécurité HTTP ─────────────────────────────────────

        // Empêche le chargement de contenu depuis des sources non autorisées
        // ✅ CORRECT - concaténation sans retours à la ligne
        $response->headers->set('Content-Security-Policy', 
            "default-src 'self'; " .
            "script-src 'self' 'unsafe-inline' 'unsafe-eval'; " .
            "style-src 'self' 'unsafe-inline' https://fonts.bunny.net; " .
            "font-src 'self' https://fonts.bunny.net; " .
            "img-src 'self' data:; " .
            "connect-src 'self';"
        );
        // Empêche les navigateurs de "deviner" le type MIME d'un fichier
        // Protection contre les attaques de type MIME sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Empêche la page d'être affichée dans une iframe
        // Protection contre le clickjacking
        $response->headers->set('X-Frame-Options', 'DENY');

        // ── Configuration CORS ────────────────────────────────────────────
        $origineRequete = $request->header('Origin');

        if ($origineRequete && in_array($origineRequete, self::ORIGINES_AUTORISEES)) {
            // L'origine est autorisée → on ajoute les en-têtes CORS
            $response->headers->set('Access-Control-Allow-Origin',  $origineRequete);
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
            $response->headers->set('Access-Control-Max-Age',       '3600');
        }
        // Si l'origine n'est pas dans la liste → pas d'en-tête CORS ajouté
        // Le navigateur refusera automatiquement la requête cross-origin

        // Gérer les requêtes OPTIONS (preflight CORS) — répondre 200 directement
        if ($request->getMethod() === 'OPTIONS') {
            $response->setStatusCode(200);
        }

        return $response;
    }
}