<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Abonne extends Model
{
    
    use HasFactory;

    protected $table = 'abonnes';

    protected $fillable = [
        'nom',
        'prenom',
        'ville',
        'quartier',
        'numero_compteur',
        'type_abonnement',
    ];


    /**
     * Retourne le nom complet de l'abonné (prénom + nom).
     * @return string
     */
    public function getNomCompletAttribute(): string
    {
        return $this->prenom . ' ' . $this->nom;
    }


    /**
     * Stocke le nom en majuscules dans la base.
     * @param string $valeur  Le nom tel que saisi
     */
    public function setNomAttribute(string $valeur): void
    {
        $this->attributes['nom'] = strtoupper(trim($valeur));
    }

    /**
     * Stocke le prénom avec la première lettre en majuscule.
     * @param string $valeur  Le prénom tel que saisi
     */
    public function setPrenomAttribute(string $valeur): void
    {
        $this->attributes['prenom'] = ucfirst(strtolower(trim($valeur)));
    }


    // RELATION — lien vers la table factures
    /**
     * Un abonné peut avoir plusieurs factures.
     * @return HasMany
     */
    public function factures(): HasMany
    {
        return $this->hasMany(Facture::class, 'abonne_id');
    }



    // MÉTHODES MÉTIER
    /**
     * Enregistre ou met à jour l'abonné en base de données.
     * @return bool  true si l'enregistrement a réussi
     */
    public function save(array $options = []): bool
    {
        return parent::save($options);
    }

    /**
     * Supprime l'abonné de la base de données.
     * @return bool|null
     */
    public function delete(): ?bool
    {
        return parent::delete();
    }

    /**
     * Cherche et retourne un abonné à partir de son identifiant.
     * @param  int  $id  L'identifiant de l'abonné
     * @return static|null
     */
    public static function findById(int $id): ?self
    {
        return static::find($id);
    }

    /**
     * Retourne la liste de tous les abonnés, triés par nom.
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function findAll()
    {
        return static::orderBy('nom')->get();
    }
}
