<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject; // ← Ajout
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Operateur extends Authenticatable implements JWTSubject // ← Ajout
{
    use HasFactory;

    protected $table = 'operateurs';

    protected $fillable = [
        'nom',
        'prenom',
        'login',
        'password',
        'role',
    ];

    // On cache le mot de passe dans les réponses JSON (sécurité)
    protected $hidden = ['password'];

    /**
     * Hache automatiquement le mot de passe avant de le stocker.
     * @param string $valeur  Le mot de passe en clair
     */
    public function setPasswordAttribute(string $valeur): void
    {
        $this->attributes['password'] = bcrypt($valeur);
    }

    /**
     * Un opérateur peut traiter plusieurs réclamations.
     */
    public function reclamations()
    {
        return $this->hasMany(Reclamation::class, 'operateur_id');
    }

    // Méthodes obligatoires pour JWT 

    /**
     * Retourne l'identifiant unique utilisé par JWT (la clé primaire).
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Données supplémentaires encodées dans le token JWT.
     */
    public function getJWTCustomClaims(): array
    {
        return [
            'role'   => $this->role,
            'nom'    => $this->nom,
            'prenom' => $this->prenom,
        ];
    }
}