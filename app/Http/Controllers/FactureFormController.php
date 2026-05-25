<?php

namespace App\Http\Controllers;

use App\Models\Facture;
use App\Models\Abonne;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class FactureFormController extends Controller
{
    /**
     * Affiche le formulaire de création d'une facture.
     */
    public function create(): View
    {
        $abonnes = Abonne::orderBy('nom')->get();
        return view('factures.create', compact('abonnes'));
    }

    /**
     * Enregistre une nouvelle facture en base de données.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'abonne_id' => 'required|exists:abonnes,id',
            'consommation' => 'required|integer|min:1',
        ]);

        $abonne = Abonne::findOrFail($validated['abonne_id']);
        
        // Calculer le montant selon le type d'abonnement
        $montant = Facture::calculerMontant($validated['consommation'], $abonne->type_abonnement);

        Facture::create([
            'abonne_id' => $validated['abonne_id'],
            'consommation' => $validated['consommation'],
            'montant_total' => $montant,
            'date_emission' => now()->toDateString(),
            'statut' => 'Emise',
        ]);

        return redirect()->route('factures')->with('success', 'Facture créée avec succès.');
    }
}
