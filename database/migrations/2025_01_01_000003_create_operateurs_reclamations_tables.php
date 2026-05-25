<?php

// ============================================================
// Migration : création des tables "operateurs" et "reclamations"
//
// Les opérateurs sont créés en premier car les réclamations
// font référence à eux (clé étrangère).
// ============================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Table operateurs ─────────────────────────────────────────────────
        // Utilisateurs internes : admin ou gestionnaire
        Schema::create('operateurs', function (Blueprint $table) {

            $table->id();

            $table->string('nom',    100);
            $table->string('prenom', 100);

            // Le login est l'identifiant de connexion (unique, souvent un email)
            $table->string('login', 80)->unique();

            // Le mot de passe sera haché avec bcrypt() avant d'être stocké
            $table->string('password', 255);

            // Rôle de l'opérateur : admin (tous droits) ou gestionnaire
            $table->enum('role', ['admin', 'gestionnaire'])->default('gestionnaire');

            $table->timestamps();
        });

        // ── Table reclamations ───────────────────────────────────────────────
        // Une réclamation est soumise par un abonné sur une facture précise
        Schema::create('reclamations', function (Blueprint $table) {

            $table->id();

            // Référence vers la facture concernée
            $table->foreignId('facture_id')
                  ->constrained('factures')
                  ->onDelete('cascade'); // si la facture est supprimée, la réclamation aussi

            // Texte de la réclamation rédigé par l'abonné
            $table->text('description');

            // Statut de traitement de la réclamation
            $table->enum('statut', ['En attente', 'En cours', 'Résolue'])->default('En attente');

            // Réponse rédigée par l'opérateur (vide tant qu'il n'a pas répondu)
            $table->text('reponse')->nullable();

            // Opérateur qui traite la réclamation
            // nullable() car non encore assigné à la création
            $table->foreignId('operateur_id')
                  ->nullable()
                  ->constrained('operateurs')
                  ->onDelete('set null'); // si l'opérateur est supprimé, on met NULL

            // Date de soumission générée automatiquement
            $table->timestamp('date_soumission')->useCurrent();

            $table->timestamps();

            // Index pour filtrer par statut et trier par date
            $table->index('statut');
            $table->index('date_soumission');
        });
    }

    public function down(): void
    {
        // On supprime dans l'ordre inverse pour respecter les dépendances
        Schema::dropIfExists('reclamations');
        Schema::dropIfExists('operateurs');
    }
};
