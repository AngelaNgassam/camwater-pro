<?php

use App\Http\Controllers\FrontendController;
use App\Http\Controllers\AbonneFormController;
use App\Http\Controllers\FactureFormController;
use App\Http\Controllers\ReclamationFormController;
use App\Http\Controllers\OperateurFormController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Routes publiques (login)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Routes protégées par authentification
Route::middleware('auth:web')->group(function () {
    // Pages de consultation
    Route::get('/', [FrontendController::class, 'dashboard'])->name('dashboard');
    Route::get('/abonnes', [FrontendController::class, 'abonnes'])->name('abonnes');
    Route::get('/factures', [FrontendController::class, 'factures'])->name('factures');
    Route::get('/reclamations', [FrontendController::class, 'reclamations'])->name('reclamations');

    // Formulaires et CRUD Abonnés
    Route::get('/abonnes/create', [AbonneFormController::class, 'create'])->name('abonnes.create');
    Route::post('/abonnes', [AbonneFormController::class, 'store'])->name('abonnes.store');
    Route::get('/abonnes/{id}/edit', [AbonneFormController::class, 'edit'])->name('abonnes.edit');
    Route::post('/abonnes/{id}', [AbonneFormController::class, 'update'])->name('abonnes.update');
    Route::get('/abonnes/{id}/delete', [AbonneFormController::class, 'destroy'])->name('abonnes.destroy');

    // Formulaires et CRUD Factures
    Route::get('/factures/create', [FactureFormController::class, 'create'])->name('factures.create');
    Route::post('/factures', [FactureFormController::class, 'store'])->name('factures.store');

    // Formulaires et CRUD Réclamations
    Route::get('/reclamations/create', [ReclamationFormController::class, 'create'])->name('reclamations.create');
    Route::post('/reclamations', [ReclamationFormController::class, 'store'])->name('reclamations.store');
    Route::get('/reclamations/{id}/edit', [ReclamationFormController::class, 'edit'])->name('reclamations.edit');
    Route::post('/reclamations/{id}', [ReclamationFormController::class, 'update'])->name('reclamations.update');

    // Utilisateurs (Opérateurs) - Seulement admin
    Route::middleware('admin')->group(function () {
        Route::get('/utilisateurs', [OperateurFormController::class, 'index'])->name('utilisateurs.index');
        Route::get('/utilisateurs/create', [OperateurFormController::class, 'create'])->name('utilisateurs.create');
        Route::post('/utilisateurs', [OperateurFormController::class, 'store'])->name('utilisateurs.store');
    });
});

