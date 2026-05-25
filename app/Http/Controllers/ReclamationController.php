<?php

namespace App\Http\Controllers;

// ============================================================
// ReclamationController
//
// Rôle : gère les réclamations des abonnés sur leurs factures.
//
// Routes :
//   POST /api/reclamations        → soumettre une réclamation
//   PUT  /api/reclamations/{id}   → mettre à jour le statut (gestionnaire)
// ============================================================

use App\Models\Reclamation;
use App\Models\Facture;
use App\Traits\PrometheusMetrics;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Prometheus\Facades\Prometheus;

class ReclamationController extends Controller
{
    use PrometheusMetrics;

    // POST /api/reclamations
    /**
     * Soumet une nouvelle réclamation liée à une facture.
     * @param  Request  $request
     * @return JsonResponse  201 si créée, 422 si données invalides
     */
    public function store(Request $request): JsonResponse
    {
        $donnees = $request->validate([
            'facture_id'  => 'required|integer|exists:factures,id',
            'description' => 'required|string|min:10',
        ], [
            'facture_id.required'  => "L'identifiant de la facture est obligatoire.",
            'facture_id.exists'    => "Aucune facture trouvée avec cet identifiant.",
            'description.required' => 'La description de la réclamation est obligatoire.',
            'description.min'      => 'La description doit contenir au moins 10 caractères.',
        ]);

        $reclamation = Reclamation::create([
            'facture_id'  => $donnees['facture_id'],
            'description' => $donnees['description'],
            'statut'      => 'En attente',
        ]);

        // Enregistrer la nouvelle réclamation
        try {
            Prometheus::addCounter('camwater_action_complaint_created_total')
                ->helpText('Nombre de réclamations créées')
                ->labels(['status'])
                ->inc(1, ['pending']);

            // Enregistrer le nombre total de réclamations
            Prometheus::addGauge('camwater_total_complaints')
                ->helpText('Nombre total de réclamations')
                ->value((float)Reclamation::count());
        } catch (\Exception $e) {
            // Ignorer les erreurs de métriques
        }

        return response()->json([
            'success' => true,
            'message' => 'Réclamation soumise avec succès.',
            'data'    => $reclamation->load('facture'),
        ], 201);
    }


    // PUT /api/reclamations/{id}
    /**
     * Met à jour le statut d'une réclamation (réservé au gestionnaire).
     * @param  Request  $request
     * @param  int      $id       Identifiant de la réclamation
     * @return JsonResponse  200 si mis à jour, 404 si introuvable
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $reclamation = Reclamation::find($id);

        if ($reclamation === null) {
            return response()->json([
                'status'    => 'error',
                'code'      => 404,
                'message'   => "Aucune réclamation trouvée avec l'identifiant #{$id}.",
                'timestamp' => now()->toIso8601String(),
            ], 404);
        }

        $donnees = $request->validate([
            'statut'  => 'required|in:En attente,En cours,Résolue',
            'reponse' => 'nullable|string',
        ], [
            'statut.required' => 'Le statut est obligatoire.',
            'statut.in'       => "Le statut doit être : 'En attente', 'En cours' ou 'Résolue'.",
        ]);

        // Associer l'opérateur connecté à la réclamation
        $operateur = $request->attributes->get('operateur');

        $reclamation->update([
            'statut'       => $donnees['statut'],
            'reponse'      => $donnees['reponse'] ?? $reclamation->reponse,
            'operateur_id' => $operateur?->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Réclamation mise à jour avec succès.',
            'data'    => $reclamation->fresh()->load('facture', 'operateur'),
        ]);
    }
}
