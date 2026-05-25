<?php

// ============================================================
// Migration : ajout de la table reclamations complète
// avec tous les champs nécessaires au sujet
// ============================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // On recrée proprement si elle n'existe pas encore avec tous les champs
        if (!Schema::hasTable('reclamations')) {
            Schema::create('reclamations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('facture_id')
                      ->constrained('factures')
                      ->onDelete('cascade');
                $table->text('description');
                $table->enum('statut', ['En attente', 'En cours', 'Résolue'])
                      ->default('En attente');
                $table->text('reponse')->nullable();
                $table->foreignId('operateur_id')
                      ->nullable()
                      ->constrained('operateurs')
                      ->onDelete('set null');
                $table->timestamp('date_soumission')->useCurrent();
                $table->timestamps();
                $table->index('statut');
                $table->index('date_soumission');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('reclamations');
    }
};
