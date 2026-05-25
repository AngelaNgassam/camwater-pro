<?php

namespace Tests\Unit;

// ============================================================
// FactureTest.php — Tests unitaires de calculerMontant()
//
// Commande pour lancer les tests :
//   php artisan test
// ou :
//   ./vendor/bin/phpunit tests/Unit/FactureTest.php
// ============================================================

use App\Models\Facture;
use PHPUnit\Framework\TestCase;

class FactureTest extends TestCase
{
    // =========================================================
    // Tests Domestique
    // =========================================================

    /**
     * Cas 1 : Domestique, 8 m³ — uniquement tranche 1
     * Calcul attendu : 8 × 350 = 2 800 FCFA
     */
    public function test_domestique_8_m3_tranche_1_uniquement(): void
    {
        $montant = Facture::calculerMontant(8, 'Domestique');
        $this->assertEquals(2800, $montant, "8 m³ Domestique doit coûter 2 800 FCFA");
    }

    /**
     * Cas 2 : Domestique, 15 m³ — tranches 1 et 2
     * Calcul attendu : (10 × 350) + (5 × 550) = 3 500 + 2 750 = 6 250 FCFA
     */
    public function test_domestique_15_m3_deux_tranches(): void
    {
        $montant = Facture::calculerMontant(15, 'Domestique');
        $this->assertEquals(6250, $montant, "15 m³ Domestique doit coûter 6 250 FCFA");
    }

    /**
     * Cas 3 : Domestique, 25 m³ — trois tranches
     * Calcul attendu : (10×350) + (10×550) + (5×780) = 3500 + 5500 + 3900 = 12 900 FCFA
     */
    public function test_domestique_25_m3_trois_tranches(): void
    {
        $montant = Facture::calculerMontant(25, 'Domestique');
        $this->assertEquals(12900, $montant, "25 m³ Domestique doit coûter 12 900 FCFA");
    }

    /**
     * Cas 4 : Professionnel, 30 m³
     * Calcul attendu : 8 500 + (30 × 950) = 8 500 + 28 500 = 37 000 FCFA
     */
    public function test_professionnel_30_m3(): void
    {
        $montant = Facture::calculerMontant(30, 'Professionnel');
        $this->assertEquals(37000, $montant, "30 m³ Professionnel doit coûter 37 000 FCFA");
    }

    /**
     * Cas 5 : Consommation nulle → doit lever une exception
     */
    public function test_consommation_nulle_leve_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Facture::calculerMontant(0, 'Domestique');
    }

    /**
     * Cas 6 : Consommation négative → doit lever une exception
     */
    public function test_consommation_negative_leve_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Facture::calculerMontant(-5, 'Domestique');
    }

    /**
     * Cas 7 : Type d'abonnement inconnu → doit lever une exception
     */
    public function test_type_inconnu_leve_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Facture::calculerMontant(10, 'Inconnu');
    }
}
