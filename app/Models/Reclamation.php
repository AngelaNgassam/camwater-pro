<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reclamation extends Model
{
    use HasFactory;
    
    protected $table = 'reclamations';

    protected $fillable = [
        'facture_id',
        'description',
        'statut',
        'reponse',
        'operateur_id',
    ];

    /**
     * Une réclamation est liée à une facture précise.
     * @return BelongsTo
     */
    public function facture(): BelongsTo
    {
        return $this->belongsTo(Facture::class, 'facture_id');
    }

    /**
     * Une réclamation est traitée par un opérateur.
     * @return BelongsTo
     */
    public function operateur(): BelongsTo
    {
        return $this->belongsTo(Operateur::class, 'operateur_id');
    }
}
