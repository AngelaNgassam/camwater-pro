<?php

namespace Database\Seeders;

// ============================================================
// DatabaseSeeder
//
// Injecte des données de test dans la base de données.
// Commande : php artisan db:seed
//
// Ce seeder crée :
//   • 1 opérateur admin
//   • 5 abonnés (3 Domestiques, 2 Professionnels, 3 villes différentes)
//   • 3 factures avec les montants calculés
// ============================================================

use Illuminate\Database\Seeder;
use App\Models\Abonne;
use App\Models\Facture;
use App\Models\Operateur;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Opérateur admin ───────────────────────────────────────────────
        Operateur::create([
            'nom'      => 'KENGA',
            'prenom'   => 'Brandon',
            'login'    => 'admin@camwater.cm',
            'password' => 'Admin123!', 
            'role'     => 'admin',
        ]);
        Operateur::create([
            'nom'      => 'MBENGUE',
            'prenom'   => 'Yoan',
            'login'    => 'YoanN@camwater.cm',
            'password' => 'Admin123!', 
            'role'     => 'gestionnaire',
        ]);

        // ── 5 abonnés ─────────────────────────────────────────────────────
        Abonne::insert([
            // Abonnés Domestiques
            [
                'nom'             => 'MBARGA',
                'prenom'          => 'Jean',
                'ville'           => 'Yaoundé',
                'quartier'        => 'Bastos',
                'numero_compteur' => 'YDE-DOM-001',
                'type_abonnement' => 'Domestique',
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'nom'             => 'BIYA',
                'prenom'          => 'Marie',
                'ville'           => 'Douala',
                'quartier'        => 'Bonanjo',
                'numero_compteur' => 'DLA-DOM-002',
                'type_abonnement' => 'Domestique',
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'nom'             => 'NKOMO',
                'prenom'          => 'Paul',
                'ville'           => 'Bafoussam',
                'quartier'        => 'Djeleng',
                'numero_compteur' => 'BFS-DOM-003',
                'type_abonnement' => 'Domestique',
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            // Abonnés Professionnels
            [
                'nom'             => 'NGUINI',
                'prenom'          => 'Roger',
                'ville'           => 'Yaoundé',
                'quartier'        => 'Centre-Ville',
                'numero_compteur' => 'YDE-PRO-001',
                'type_abonnement' => 'Professionnel',
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'nom'             => 'FOTSO',
                'prenom'          => 'Alice',
                'ville'           => 'Douala',
                'quartier'        => 'Akwa',
                'numero_compteur' => 'DLA-PRO-002',
                'type_abonnement' => 'Professionnel',
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
        ]);

        // ── 3 factures ────────────────────────────────────────────────────
        // On utilise la méthode calculerMontant() pour être cohérent

        // Facture 1 : MBARGA (Domestique, 8 m³)
        // Calcul : 8 × 350 = 2 800 FCFA
        $abonneMbarga = Abonne::where('numero_compteur', 'YDE-DOM-001')->first();
        Facture::create([
            'abonne_id'     => $abonneMbarga->id,
            'consommation'  => 8,
            'montant_total' => Facture::calculerMontant(8, 'Domestique'), // 2 800
            'date_emission' => now()->toDateString(),
            'statut'        => 'Emise',
        ]);

        // Facture 2 : BIYA (Domestique, 15 m³)
        // Calcul : (10×350) + (5×550) = 3500 + 2750 = 6 250 FCFA
        $abonneBiya = Abonne::where('numero_compteur', 'DLA-DOM-002')->first();
        Facture::create([
            'abonne_id'     => $abonneBiya->id,
            'consommation'  => 15,
            'montant_total' => Facture::calculerMontant(15, 'Domestique'), // 6 250
            'date_emission' => now()->toDateString(),
            'statut'        => 'Emise',
        ]);

        // Facture 3 : NGUINI (Professionnel, 25 m³)
        // Calcul : 8500 + (25 × 950) = 8500 + 23750 = 32 250 FCFA
        $abonneNguini = Abonne::where('numero_compteur', 'YDE-PRO-001')->first();
        Facture::create([
            'abonne_id'     => $abonneNguini->id,
            'consommation'  => 25,
            'montant_total' => Facture::calculerMontant(25, 'Professionnel'), // 32 250
            'date_emission' => now()->toDateString(),
            'statut'        => 'Emise',
        ]);

        // ── Logs d'activité ───────────────────────────────────────────────
        // 3 exemples de types différents pour illustrer la table logs_activite
        $operateur = Operateur::first();

        \App\Models\LogActivite::create([
            'type_action'  => 'connexion',
            'operateur_id' => $operateur->id,
            'abonne_id'    => null,
            'details'      => [
                'adresse_ip' => '192.168.1.45',
                'navigateur' => 'Chrome 124',
                'statut'     => 'succes',
            ],
        ]);

        \App\Models\LogActivite::create([
            'type_action'  => 'generation_facture',
            'operateur_id' => $operateur->id,
            'abonne_id'    => $abonneMbarga->id,
            'details'      => [
                'consommation_m3'    => 8,
                'montant_total_fcfa' => 2800,
                'type_abonnement'    => 'Domestique',
                'ville'              => 'Yaoundé',
            ],
        ]);

        \App\Models\LogActivite::create([
            'type_action'  => 'modification_abonne',
            'operateur_id' => $operateur->id,
            'abonne_id'    => $abonneBiya->id,
            'details'      => [
                'champs_modifies'   => ['quartier'],
                'ancienne_valeur'   => 'Bonanjo',
                'nouvelle_valeur'   => 'Akwa',
            ],
        ]);

        $this->command->info('✔ Données de test insérées avec succès !');
    }
}
