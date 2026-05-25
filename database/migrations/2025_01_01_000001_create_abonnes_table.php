<?php

// ============================================================
// Migration : création de la table "abonnes"
//
// Une migration Laravel décrit la structure d'une table.
// Pour l'exécuter : php artisan migrate
// Pour l'annuler  : php artisan migrate:rollback
// ============================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // up() est appelée quand on exécute la migration (création)
    public function up(): void
    {
        Schema::create('abonnes', function (Blueprint $table) {

            $table->id(); // colonne "id" INT AUTO_INCREMENT PRIMARY KEY

            $table->string('nom',    100);   // VARCHAR(100) NOT NULL
            $table->string('prenom', 100);   // VARCHAR(100) NOT NULL

            // La ville doit être l'une des 4 villes autorisées
            $table->enum('ville', ['Yaoundé', 'Douala', 'Bafoussam', 'Garoua']);

            $table->string('quartier', 150);

            // Numéro de compteur unique dans toute la table
            $table->string('numero_compteur', 50)->unique();

            // Type d'abonnement : soit Domestique soit Professionnel
            $table->enum('type_abonnement', ['Domestique', 'Professionnel']);

            // Date générée automatiquement à la création de l'abonné
            $table->timestamp('date_creation')->useCurrent();

            $table->timestamps(); // crée created_at et updated_at automatiquement

            // Index pour accélérer les statistiques par ville et par type
            $table->index('ville');
            $table->index('type_abonnement');
        });
    }

    // down() est appelée quand on annule la migration (suppression)
    public function down(): void
    {
        Schema::dropIfExists('abonnes');
    }
};
