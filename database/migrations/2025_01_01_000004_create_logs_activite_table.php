<?php

// ============================================================
// Migration : création de la table "logs_activite"
//
// Cette table remplace MongoDB pour stocker les logs.
// Elle enregistre toutes les actions importantes :
//   - connexions des opérateurs
//   - générations de factures
//   - modifications d'abonnés
// ============================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logs_activite', function (Blueprint $table) {

            $table->id();

            // Type de l'action effectuée
            $table->enum('type_action', [
                'connexion',
                'generation_facture',
                'modification_abonne',
            ])->notNull();

            // Opérateur qui a effectué l'action (nullable : peut être le système)
            $table->foreignId('operateur_id')
                  ->nullable()
                  ->constrained('operateurs')
                  ->onDelete('set null');

            // Abonné concerné par l'action (nullable : ex. lors d'une connexion)
            $table->foreignId('abonne_id')
                  ->nullable()
                  ->constrained('abonnes')
                  ->onDelete('set null');

            // Date et heure exacte de l'action
            $table->timestamp('timestamp')->useCurrent();

            // Détails de l'action stockés en JSON
            // Exemples :
            //   connexion          → { "adresse_ip": "...", "navigateur": "..." }
            //   generation_facture → { "facture_id": 5, "montant": 6250, ... }
            //   modification_abonne→ { "champs": ["ville"], "avant": {...}, "apres": {...} }
            $table->json('details')->nullable();

            $table->timestamps();

            // Index pour les recherches fréquentes
            $table->index('type_action');
            $table->index('operateur_id');
            $table->index('timestamp');   // lister les logs des 7 derniers jours
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logs_activite');
    }
};
