<?php

namespace Tests\Feature;

use App\Models\Abonne;
use App\Models\Facture;
use App\Models\Operateur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class StatistiqueTest extends TestCase
{
    use RefreshDatabase;

    // ── Helper : requête authentifiée sur /api/statistiques ───
    private function getStatistiques(): \Illuminate\Testing\TestResponse
    {
        $token = JWTAuth::fromUser(Operateur::factory()->create());

        return $this->withToken($token)->getJson('/api/statistiques');
    }

    // =========================================================
    // GET /api/statistiques  →  index()
    // =========================================================

    #[Test]
    public function index_echoue_sans_authentification(): void
    {
        $this->getJson('/api/statistiques')->assertStatus(401);
    }

    #[Test]
    public function index_retourne_une_structure_json_correcte(): void
    {
        $this->getStatistiques()
             ->assertStatus(200)
             ->assertJsonStructure([
                 'success',
                 'data' => [
                     'factures_par_ville_ce_mois',
                     'factures_par_mois',
                     'abonnes_par_type',
                 ],
             ])
             ->assertJsonFragment(['success' => true]);
    }

    #[Test]
    public function index_retourne_les_factures_du_mois_courant_par_ville(): void
    {
        $abonneYaounde = Abonne::factory()->ville('Yaoundé')->create();
        $abonneDla     = Abonne::factory()->ville('Douala')->create();

        // 3 factures ce mois pour Yaoundé (total 30 000)
        Facture::factory()->count(3)->create([
            'abonne_id'     => $abonneYaounde->id,
            'date_emission' => Carbon::now()->format('Y-m-d'),
            'montant_total' => 10000,
        ]);

        // 2 factures ce mois pour Douala (total 10 000)
        Facture::factory()->count(2)->create([
            'abonne_id'     => $abonneDla->id,
            'date_emission' => Carbon::now()->format('Y-m-d'),
            'montant_total' => 5000,
        ]);

        // Hors mois courant — ne doit PAS apparaître
        Facture::factory()->create([
            'abonne_id'     => $abonneYaounde->id,
            'date_emission' => Carbon::now()->subMonths(2)->format('Y-m-d'),
            'montant_total' => 99999,
        ]);

        $data = $this->getStatistiques()
                     ->assertStatus(200)
                     ->json('data.factures_par_ville_ce_mois');

        $yaoundeRow = collect($data)->firstWhere('ville', 'Yaoundé');
        $this->assertNotNull($yaoundeRow);
        $this->assertEquals(3,     $yaoundeRow['nombre_factures']);
        $this->assertEquals(30000, $yaoundeRow['total_montant_fcfa']);

        $doualaRow = collect($data)->firstWhere('ville', 'Douala');
        $this->assertNotNull($doualaRow);
        $this->assertEquals(2,     $doualaRow['nombre_factures']);
        $this->assertEquals(10000, $doualaRow['total_montant_fcfa']);
    }

    #[Test]
    public function index_retourne_les_totaux_par_mois_sur_12_mois(): void
    {
        $abonne = Abonne::factory()->create();

        // Dans la fenêtre (il y a 1 mois)
        Facture::factory()->create([
            'abonne_id'     => $abonne->id,
            'date_emission' => Carbon::now()->subMonths(1)->format('Y-m-d'),
            'montant_total' => 20000,
        ]);

        // Hors fenêtre (il y a 13 mois) — ne doit PAS apparaître
        Facture::factory()->create([
            'abonne_id'     => $abonne->id,
            'date_emission' => Carbon::now()->subMonths(13)->format('Y-m-d'),
            'montant_total' => 99999,
        ]);

        $facturesParMois = $this->getStatistiques()
                                ->assertStatus(200)
                                ->json('data.factures_par_mois');

        $this->assertCount(1, $facturesParMois);
        $this->assertEquals(20000, $facturesParMois[0]['total_montant_fcfa']);
    }

    #[Test]
    public function index_retourne_la_repartition_des_abonnes_par_type(): void
    {
        Abonne::factory()->count(4)->domestique()->create();
        Abonne::factory()->count(2)->professionnel()->create();

        $abonnesParType = collect(
            $this->getStatistiques()
                 ->assertStatus(200)
                 ->json('data.abonnes_par_type')
        );

        $this->assertEquals(4, $abonnesParType->firstWhere('type_abonnement', 'Domestique')['nombre']);
        $this->assertEquals(2, $abonnesParType->firstWhere('type_abonnement', 'Professionnel')['nombre']);
    }

    #[Test]
    public function index_retourne_des_listes_vides_quand_aucune_donnee(): void
    {
        $response = $this->getStatistiques()->assertStatus(200);

        $this->assertEmpty($response->json('data.factures_par_ville_ce_mois'));
        $this->assertEmpty($response->json('data.factures_par_mois'));
        // Les opérateurs créés par getStatistiques() n'ont pas d'abonnement — liste vide
        $this->assertEmpty($response->json('data.abonnes_par_type'));
    }

    #[Test]
    public function index_trie_les_villes_par_montant_total_decroissant(): void
    {
        // Utiliser uniquement des villes de l'enum : Yaoundé, Douala, Bafoussam, Garoua
        $villeA = Abonne::factory()->ville('Bafoussam')->create();
        $villeB = Abonne::factory()->ville('Garoua')->create();

        Facture::factory()->create([
            'abonne_id'     => $villeA->id,
            'date_emission' => Carbon::now()->format('Y-m-d'),
            'montant_total' => 1000,
        ]);
        Facture::factory()->create([
            'abonne_id'     => $villeB->id,
            'date_emission' => Carbon::now()->format('Y-m-d'),
            'montant_total' => 5000,
        ]);

        $data = $this->getStatistiques()
                     ->assertStatus(200)
                     ->json('data.factures_par_ville_ce_mois');

        // Garoua (5 000) doit précéder Bafoussam (1 000)
        $this->assertEquals('Garoua',    $data[0]['ville']);
        $this->assertEquals('Bafoussam', $data[1]['ville']);
    }
}