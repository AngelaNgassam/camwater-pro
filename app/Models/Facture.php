<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Facture extends Model
{
    
    use HasFactory;

    protected $table = 'factures';

    protected $fillable = [
        'abonne_id',
        'consommation',
        'montant_total',
        'date_emission',
        'statut',
    ];

    // Indique à Laravel de traiter date_emission comme une date Carbon
    protected $casts = [
        'date_emission' => 'date',
    ];

    // Tarifs Domestique 
    const TARIF_DOM_TRANCHE_1 = 350;   // FCFA par m³ pour les 10 premiers m³
    const TARIF_DOM_TRANCHE_2 = 550;   // FCFA par m³ pour les m³ de 11 à 20
    const TARIF_DOM_TRANCHE_3 = 780;   // FCFA par m³ au-delà de 20 m³

    //  Tarif Professionnel
    const TARIF_PRO_FORFAIT   = 8500;  // Prime fixe en FCFA
    const TARIF_PRO_PAR_M3    = 950;   // FCFA supplémentaire par m³


    /**
     * @return BelongsTo
     */
    public function abonne(): BelongsTo
    {
        return $this->belongsTo(Abonne::class, 'abonne_id');
    }

    /**
     * Une facture peut avoir plusieurs réclamations.
     * @return HasMany
     */
    public function reclamations(): HasMany
    {
        return $this->hasMany(Reclamation::class, 'facture_id');
    }


    // MÉTHODE DE CALCUL DU MONTANT 
    /**
     * Calcule le montant d'une facture en FCFA selon le type d'abonnement.
     * @param  int     $consommation     Consommation en m³ (doit être > 0)
     * @param  string  $typeAbonnement   'Domestique' ou 'Professionnel'
     * @return int                       Montant total en FCFA, arrondi à l'entier supérieur
     *
     * @throws \InvalidArgumentException  Si la consommation est nulle ou négative
     * @throws \InvalidArgumentException  Si le type d'abonnement est inconnu
     */
    public static function calculerMontant(int $consommation, string $typeAbonnement): int
    {
        // ── a. Vérification : la consommation doit être un entier > 0 ────────
        if ($consommation <= 0) {
            throw new \InvalidArgumentException(
                "La consommation doit être un entier strictement positif. " .
                "Valeur reçue : {$consommation}"
            );
        }

        // Variable qui va accumuler le montant total
        $montantTotal = 0;

        // ── b. Calcul pour le tarif Domestique (tranches progressives) ───────
        if ($typeAbonnement === 'Domestique') {

            // TRANCHE 1 : les m³ de 1 à 10
            // min() permet de ne pas dépasser 10 m³ dans cette tranche
            $m3Tranche1 = min($consommation, 10);
            $montantTotal += $m3Tranche1 * self::TARIF_DOM_TRANCHE_1;

            // TRANCHE 2 : les m³ de 11 à 20 (seulement si on dépasse 10 m³)
            if ($consommation > 10) {
                // Nombre de m³ dans cette tranche : au maximum 10 m³
                $m3Tranche2 = min($consommation - 10, 10);
                $montantTotal += $m3Tranche2 * self::TARIF_DOM_TRANCHE_2;
            }

            // TRANCHE 3 : les m³ au-delà de 20 (seulement si on dépasse 20 m³)
            if ($consommation > 20) {
                // Nombre de m³ qui dépassent les 20 premiers
                $m3Tranche3 = $consommation - 20;
                $montantTotal += $m3Tranche3 * self::TARIF_DOM_TRANCHE_3;
            }

        // ── c. Calcul pour le tarif Professionnel (forfait + consommation) ───
        } elseif ($typeAbonnement === 'Professionnel') {

            // Formule simple : prime fixe + (consommation × tarif unitaire)
            $montantTotal = self::TARIF_PRO_FORFAIT + ($consommation * self::TARIF_PRO_PAR_M3);

        // ── Cas d'erreur : type d'abonnement inconnu ─────────────────────────
        } else {
            throw new \InvalidArgumentException(
                "Type d'abonnement inconnu : '{$typeAbonnement}'. " .
                "Valeurs acceptées : 'Domestique', 'Professionnel'."
            );
        }

        // ── d. Arrondi à l'entier supérieur puis retour du résultat ──────────
        // ceil() arrondit vers le haut : ex. 6250.7 → 6251
        return (int) ceil($montantTotal);
    }



    // MÉTHODE genererContenu()
    /**
     * Génère un résumé lisible de la facture sous forme de texte.
     * @return string  Le contenu formaté de la facture
     */
    public function genererContenu(): string
    {
        // On charge l'abonné lié à cette facture
        $abonne = $this->abonne;

        return implode("\n", [
            '============================================',
            '          FACTURE CAMWATER PRO              ',
            '============================================',
            'Numéro facture : ' . $this->id,
            'Date émission  : ' . $this->date_emission->format('d/m/Y'),
            '--------------------------------------------',
            'Abonné         : ' . ($abonne ? $abonne->nom_complet    : 'N/A'),
            'Ville          : ' . ($abonne ? $abonne->ville           : 'N/A'),
            'Compteur       : ' . ($abonne ? $abonne->numero_compteur : 'N/A'),
            'Type abonnement: ' . ($abonne ? $abonne->type_abonnement : 'N/A'),
            '--------------------------------------------',
            'Consommation   : ' . $this->consommation . ' m³',
            'Montant total  : ' . number_format($this->montant_total, 0, ',', ' ') . ' FCFA',
            'Statut         : ' . $this->statut,
            '============================================',
        ]);
    }
}
