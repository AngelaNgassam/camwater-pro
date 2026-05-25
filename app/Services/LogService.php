<?php

namespace App\Services;

use App\Models\LogActivite;
use Illuminate\Support\Facades\Log;

class LogService
{
    /**
     * Enregistre une action dans la table MySQL "logs_activite".
     * @param  string    $typeAction   'connexion', 'generation_facture' ou 'modification_abonne'
     * @param  int|null  $operateurId  Identifiant de l'opérateur (null si action système)
     * @param  int|null  $abonneId     Identifiant de l'abonné concerné (null si non applicable)
     * @param  array     $details      Données supplémentaires spécifiques à l'action
     * @return void
     */
    public function enregistrerLog(
        string $typeAction,
        ?int   $operateurId,
        ?int   $abonneId,
        array  $details
    ): void {
        try {
            LogActivite::create([
                'type_action'  => $typeAction,
                'operateur_id' => $operateurId,
                'abonne_id'    => $abonneId,
                'timestamp'    => now(),
                'details'      => $details, // sera automatiquement converti en JSON
            ]);
        } catch (\Exception $erreur) {
            // Si l'enregistrement du log échoue, on ne bloque pas l'application
            // On écrit juste un avertissement dans le fichier storage/logs/laravel.log
            Log::warning('Impossible d\'enregistrer le log : ' . $erreur->getMessage());
        }
    }

    /**
     * Retourne tous les logs d'un opérateur sur les 7 derniers jours.
     * Utilise la méthode du modèle LogActivite.
     *
     * @param  int  $operateurId  Identifiant de l'opérateur
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLogsOperateur7Jours(int $operateurId)
    {
        return LogActivite::getLogsOperateur7Jours($operateurId);
    }
}
