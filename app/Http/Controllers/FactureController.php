<?php

namespace App\Http\Controllers;

use App\Models\Abonne;
use App\Models\Facture;
use App\Services\LogService;
use App\Traits\PrometheusMetrics;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Prometheus\Facades\Prometheus;

class FactureController extends Controller
{
    use PrometheusMetrics;

    private LogService $logService;

    public function __construct(LogService $logService)
    {
        $this->logService = $logService;
    }


    // POST /api/factures/generer
    /**
     * Génère une nouvelle facture pour un abonné.
     * @param  Request  $request
     * @return JsonResponse
     */
    public function generer(Request $request): JsonResponse
    {
        // ── Étape 1 : Validation des données d'entrée ─────────────────────
        $donnees = $request->validate([
            'abonne_id'    => 'required|integer|exists:abonnes,id',
            'consommation' => 'required|integer|min:1',
        ], [
            'abonne_id.required'    => "L'identifiant de l'abonné est obligatoire.",
            'abonne_id.exists'      => "Aucun abonné trouvé avec cet identifiant.",
            'consommation.required' => "La consommation est obligatoire.",
            'consommation.integer'  => "La consommation doit être un nombre entier.",
            'consommation.min'      => "La consommation doit être au moins de 1 m³.",
        ]);

        // ── Étape 2 : Retrouver l'abonné ──────────────────────────────────
        $abonne = Abonne::find($donnees['abonne_id']);

        // ── Étape 3 : Calculer le montant ─────────────────────────────────
        // On appelle la méthode statique du modèle Facture
        // Elle peut lever une exception si les données sont invalides
        try {
            $montantCalcule = Facture::calculerMontant(
                (int) $donnees['consommation'],
                $abonne->type_abonnement   // 'Domestique' ou 'Professionnel'
            );
        } catch (\InvalidArgumentException $erreur) {
            // Enregistrer les erreurs de calcul
            try {
                Prometheus::addCounter('camwater_invoice_generation_errors_total')
                    ->helpText('Nombre total d\'erreurs lors de la génération de factures')
                    ->inc(1);
            } catch (\Exception $e) {
                // Ignorer les erreurs de métriques
            }

            return response()->json([
                'success' => false,
                'message' => $erreur->getMessage(),
            ], 422);
        }

        // ── Étape 4 : Créer la facture en base de données ─────────────────
        $facture = Facture::create([
            'abonne_id'     => $abonne->id,
            'consommation'  => $donnees['consommation'],
            'montant_total' => $montantCalcule,
            'date_emission' => now()->toDateString(), // date d'aujourd'hui
            'statut'        => 'Emise',               // toujours "Emise" à la création
        ]);

        // ── Étape 5 : Enregistrer les métriques Prometheus ─────────────────
        try {
            // Compteur de factures générées
            Prometheus::addCounter('camwater_action_invoice_created_total')
                ->helpText('Nombre de factures créées')
                ->labels(['subscription_type', 'city'])
                ->inc(1, [$abonne->type_abonnement, $abonne->ville]);

            // Gauge du montant facturé
            Prometheus::addGauge('camwater_invoice_amount_fcfa')
                ->helpText('Montants des factures')
                ->labels(['subscription_type'])
                ->value((float) $montantCalcule, [$abonne->type_abonnement]);

            // Gauge de la consommation en m³
            Prometheus::addGauge('camwater_water_consumption_m3')
                ->helpText('Consommation d\'eau')
                ->labels(['subscription_type'])
                ->value((float) $donnees['consommation'], [$abonne->type_abonnement]);

            // Gauge du montant moyen
            Prometheus::addGauge('camwater_avg_invoice_amount_fcfa')
                ->helpText('Montant moyen des factures')
                ->value($this->getAverageInvoiceAmount());
        } catch (\Exception $e) {
            // Ignorer les erreurs de métriques
        }

        // ── Étape 6 : Log dans MySQL ──────────────────────────────────────
        $this->logService->enregistrerLog(
            'generation_facture',
            $request->user()?->id,
            $abonne->id,
            [
                'facture_id'         => $facture->id,
                'consommation_m3'    => $facture->consommation,
                'montant_total_fcfa' => $facture->montant_total,
                'type_abonnement'    => $abonne->type_abonnement,
                'ville'              => $abonne->ville,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Facture générée avec succès.',
            'data'    => $facture->load('abonne'), // on inclut les infos de l'abonné
        ], 201);
    }



    // GET /api/factures/{id}
    /**
     * Retourne les détails d'une facture avec son contenu formaté.
     *
     * @param  int  $id  L'identifiant de la facture
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        // On charge la facture avec les informations de l'abonné
        $facture = Facture::with('abonne')->find($id);

        if ($facture === null) {
            return response()->json([
                'success' => false,
                'message' => "Aucune facture trouvée avec l'identifiant #{$id}.",
            ], 404);
        }

        // Enregistrer l'accès à une facture
        $this->recordAction('invoice_viewed', []);

        return response()->json([
            'success' => true,
            'data'    => $facture,
            'contenu' => $facture->genererContenu(), // résumé lisible de la facture
        ]);
    }

    /**
     * Retourne le montant moyen des factures (dernier mois).
     */
    private function getAverageInvoiceAmount(): float
    {
        try {
            $avg = Facture::where('date_emission', '>=', now()->subMonth())
                ->average('montant_total');
            return (float) ($avg ?? 0);
        } catch (\Exception $e) {
            return 0;
        }
    }
}
