<?php

// ============================================================
// routes/api.php
//
// Rôle : définit toutes les routes de l'API CAMWATER PRO.
// Préfixe automatique : /api (ex: /api/abonnes)
//
// Middlewares utilisés :
//   'jwt'               → vérifie le token JWT (VerifierJWT)
//   'role:admin'        → réservé aux administrateurs
//   'role:gestionnaire' → admin + gestionnaire
//   'throttle:5,1'      → max 5 requêtes par minute (login)
// ============================================================

use App\Http\Controllers\AbonneController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FactureController;
use App\Http\Controllers\ReclamationController;
use App\Http\Controllers\StatistiqueController;
use Illuminate\Support\Facades\Route;


Route::post('/auth/login',  [AuthController::class, 'login']);

// ROUTES PROTÉGÉES 
Route::middleware('auth:api')->group(function () {


    // ABONNÉS
    Route::get('/abonnes',         [AbonneController::class, 'index']);
    Route::get('/abonnes/{id}',    [AbonneController::class, 'show']);
    Route::put('/abonnes/{id}',    [AbonneController::class, 'update']);
    Route::delete('/abonnes/{id}', [AbonneController::class, 'destroy']);
    Route::post('/abonnes',        [AbonneController::class, 'store']);

    // FACTURES
    Route::post('/factures/generer', [FactureController::class, 'generer']);
    Route::get('/factures/{id}',     [FactureController::class, 'show']);

    // RÉCLAMATIONS
    Route::post('/reclamations',     [ReclamationController::class, 'store']);
    Route::put('/reclamations/{id}', [ReclamationController::class, 'update']);

    // STATISTIQUES
    Route::get('/statistiques', [StatistiqueController::class, 'index']);

        // AUTHENTIFICATION 
    Route::post('/auth/logout', [\App\Http\Controllers\AuthController::class, 'logout']);
    Route::get('/auth/me',      [\App\Http\Controllers\AuthController::class, 'me']);
});


// DOCUMENTATION SWAGGER UI 
Route::get('/docs', function () {
    $html = file_get_contents(public_path('docs.html'));
    return response($html, 200)->withHeaders([
        'Content-Type'           => 'text/html',
        'Content-Security-Policy' => "default-src 'self' 'unsafe-inline' 'unsafe-eval' http://127.0.0.1:8000",
    ]);
});
