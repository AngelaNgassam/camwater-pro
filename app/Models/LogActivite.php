<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogActivite extends Model
{
    protected $table = 'logs_activite';

    protected $fillable = [
        'type_action',
        'operateur_id',
        'abonne_id',
        'timestamp',
        'details',
    ];

    // Indique à Laravel de convertir automatiquement
    // la colonne JSON "details" en tableau PHP lors de la lecture
    protected $casts = [
        'details'   => 'array',
        'timestamp' => 'datetime',
    ];


    // RELATIONS
    /**
     * Un log est lié à l'opérateur qui a effectué l'action.
     * @return BelongsTo
     */
    public function operateur(): BelongsTo
    {
        return $this->belongsTo(Operateur::class, 'operateur_id');
    }

    /**
     * Un log est lié à l'abonné concerné par l'action (si applicable).
     * @return BelongsTo
     */
    public function abonne(): BelongsTo
    {
        return $this->belongsTo(Abonne::class, 'abonne_id');
    }



    // MÉTHODES STATIQUES UTILITAIRES
    /**
     * Retourne tous les logs d'un opérateur sur les 7 derniers jours.
     * @param  int  $operateurId  L'identifiant de l'opérateur
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getLogsOperateur7Jours(int $operateurId)
    {
        // now()->subDays(7) calcule la date d'il y a 7 jours
        return static::where('operateur_id', $operateurId)
                     ->where('timestamp', '>=', now()->subDays(7))
                     ->orderBy('timestamp', 'desc')
                     ->get();
    }
}
