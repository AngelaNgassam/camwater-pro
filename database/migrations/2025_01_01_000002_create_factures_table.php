<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factures', function (Blueprint $table) {
            $table->id();

            $table->foreignId('abonne_id')
                  ->constrained('abonnes')
                  ->onDelete('cascade');

            $table->unsignedInteger('consommation');
            $table->unsignedBigInteger('montant_total');
            $table->date('date_emission');
            $table->enum('statut', ['Emise', 'Payée'])->default('Emise');

            $table->timestamps();

            $table->index('date_emission');
            $table->index('statut');
        });

        // Les contraintes CHECK ne sont pas supportées par SQLite —
        // on les applique uniquement sous MySQL/PostgreSQL.
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE factures ADD CONSTRAINT chk_consommation CHECK (consommation > 0)');
            DB::statement('ALTER TABLE factures ADD CONSTRAINT chk_montant CHECK (montant_total > 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('factures');
    }
};