<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php', 
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // Middleware global (appliqué à toutes les réponses)
        // Ajoute les en-têtes de sécurité HTTP + gère le CORS
        $middleware->append(\App\Http\Middleware\SecuriteHeaders::class);
        
        // Middleware Prometheus (collecte les métriques)
        $middleware->append(\App\Http\Middleware\PrometheusQueryMetrics::class);

        //Alias de middlewares (utilisés dans routes/api.php) ───────────
        // 'jwt'  → vérifie la présence et la validité du token JWT
        $middleware->alias([
            'jwt'  => \App\Http\Middleware\VerifierJWT::class,
            'role' => \App\Http\Middleware\VerifierRole::class,
            'admin' => \App\Http\Middleware\CheckAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
    $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
        
    });
})->create();
