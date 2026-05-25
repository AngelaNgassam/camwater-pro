<?php

// ============================================================
// Migration : table token_blacklist
//
// Stratégie d'invalidation JWT au logout :
// On stocke les tokens révoqués dans cette table.
// À chaque requête, le middleware vérifie que le token
// n'est pas dans cette liste noire.
// Les tokens expirés sont nettoyés automatiquement.
// ============================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('token_blacklist', function (Blueprint $table) {
            $table->id();

            // Le token JWT complet (ou son identifiant unique "jti")
            $table->text('token');

            // Date d'expiration du token — pour pouvoir nettoyer les anciens
            $table->timestamp('expire_a');

            $table->timestamp('created_at')->useCurrent();

            $table->index('expire_a');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('token_blacklist');
    }
};
