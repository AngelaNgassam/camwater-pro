<?php

namespace App\Http\Controllers;

use App\Models\Abonne;
use App\Services\LogService;
use App\Traits\PrometheusMetrics;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Prometheus\Facades\Prometheus;

class AbonneController extends Controller
{
    use PrometheusMetrics;

    // Le LogService enregistre les actions dans la table MySQL logs_activite
    private LogService $logService;

    public function __construct(LogService $logService)
    {
        $this->logService = $logService;
    }


    // GET /api/abonnes
    /**
     * Retourne la liste de tous les abonnés.
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        // On récupère tous les abonnés triés par nom (20 par page)
        $abonnes = Abonne::orderBy('nom')->paginate(20);

        // Enregistrer le nombre d'abonnés actifs
        try {
            Prometheus::addGauge('camwater_active_subscribers')
                ->helpText('Nombre d\'abonnés actifs')
                ->value((float)$abonnes->total());
        } catch (\Exception $e) {
            // Ignorer les erreurs de métriques
        }

        return response()->json([
            'success' => true,
            'data'    => $abonnes,
        ]);
    }


    // POST /api/abonnes
    /**
     * Crée un nouvel abonné en base de données.
     * @param  Request  $request  La requête HTTP avec les données
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // Valider les données reçues avant de les enregistrer
        $donneesValidees = $request->validate([
            'nom'             => 'required|string|max:100',
            'prenom'          => 'required|string|max:100',
            'ville'           => 'required|in:Yaoundé,Douala,Bafoussam,Garoua',
            'quartier'        => 'required|string|max:150',
            'numero_compteur' => 'required|string|max:50|unique:abonnes,numero_compteur',
            'type_abonnement' => 'required|in:Domestique,Professionnel',
        ], [
            // Messages d'erreur en français
            'nom.required'              => 'Le nom est obligatoire.',
            'prenom.required'           => 'Le prénom est obligatoire.',
            'ville.required'            => 'La ville est obligatoire.',
            'ville.in'                  => 'La ville doit être : Yaoundé, Douala, Bafoussam ou Garoua.',
            'quartier.required'         => 'Le quartier est obligatoire.',
            'numero_compteur.required'  => 'Le numéro de compteur est obligatoire.',
            'numero_compteur.unique'    => 'Ce numéro de compteur est déjà utilisé.',
            'type_abonnement.required'  => "Le type d'abonnement est obligatoire.",
            'type_abonnement.in'        => "Le type doit être 'Domestique' ou 'Professionnel'.",
        ]);

        // Créer l'abonné avec les données validées
        $abonne = Abonne::create($donneesValidees);

        // Enregistrer un log dans MySQL
        $this->logService->enregistrerLog(
            'modification_abonne',
            $request->user()?->id,
            $abonne->id,
            ['action' => 'creation', 'nom' => $abonne->nom_complet]
        );

        return response()->json([
            'success' => true,
            'message' => 'Abonné créé avec succès.',
            'data'    => $abonne,
        ], 201); // 201 = Created
    }



    // GET /api/abonnes/{id}
    /**
     * Retourne les informations d'un abonné, avec ses factures.
     *
     * @param  int  $id  L'identifiant de l'abonné dans l'URL
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        // On cherche l'abonné et on charge ses factures en même temps (eager loading)
        $abonne = Abonne::with('factures')->find($id);

        // Si l'abonné n'existe pas, retourner une erreur 404
        if ($abonne === null) {
            return response()->json([
                'success' => false,
                'message' => "Aucun abonné trouvé avec l'identifiant #{$id}.",
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $abonne,
        ]);
    }


    // PUT /api/abonnes/{id}
    /**
     * Modifie les informations d'un abonné existant.
     *
     * @param  Request  $request  Les nouvelles données
     * @param  int      $id       L'identifiant de l'abonné à modifier
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        // Vérifier que l'abonné existe
        $abonne = Abonne::find($id);

        if ($abonne === null) {
            return response()->json([
                'success' => false,
                'message' => "Aucun abonné trouvé avec l'identifiant #{$id}.",
            ], 404);
        }

        // Valider les données (le numero_compteur doit rester unique, sauf pour cet abonné)
        $donneesValidees = $request->validate([
            'nom'             => 'sometimes|string|max:100',
            'prenom'          => 'sometimes|string|max:100',
            'ville'           => 'sometimes|in:Yaoundé,Douala,Bafoussam,Garoua',
            'quartier'        => 'sometimes|string|max:150',
            'numero_compteur' => 'sometimes|string|max:50|unique:abonnes,numero_compteur,' . $id,
            'type_abonnement' => 'sometimes|in:Domestique,Professionnel',
        ]);

        // Garder les anciennes valeurs pour le log
        $anciennesValeurs = $abonne->only(array_keys($donneesValidees));

        // Appliquer les modifications
        $abonne->update($donneesValidees);

        // Enregistrer le log dans MySQL
        $this->logService->enregistrerLog(
            'modification_abonne',
            $request->user()?->id,
            $abonne->id,
            [
                'champs_modifies'   => array_keys($donneesValidees),
                'anciennes_valeurs' => $anciennesValeurs,
                'nouvelles_valeurs' => $donneesValidees,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Abonné mis à jour avec succès.',
            'data'    => $abonne->fresh(), // fresh() recharge l'abonné depuis la base
        ]);
    }



    // DELETE /api/abonnes/{id}
    /**
     * Supprime un abonné et toutes ses factures associées.
     * @param  int  $id  L'identifiant de l'abonné à supprimer
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $abonne = Abonne::find($id);

        if ($abonne === null) {
            return response()->json([
                'success' => false,
                'message' => "Aucun abonné trouvé avec l'identifiant #{$id}.",
            ], 404);
        }

        $abonne->delete(); // La cascade supprime aussi ses factures

        return response()->json([
            'success' => true,
            'message' => "L'abonné #{$id} et ses factures ont été supprimés.",
        ]);
    }
}
