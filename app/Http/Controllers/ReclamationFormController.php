<?php

namespace App\Http\Controllers;

use App\Models\Reclamation;
use App\Models\Facture;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ReclamationFormController extends Controller
{
    /**
     * Affiche le formulaire de création d'une réclamation.
     */
    public function create(): View
    {
        $factures = Facture::with('abonne')->orderBy('date_emission', 'desc')->get();
        return view('reclamations.create', compact('factures'));
    }

    /**
     * Enregistre une nouvelle réclamation en base de données.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'facture_id' => 'required|exists:factures,id',
            'description' => 'required|string|min:10|max:1000',
        ]);

        Reclamation::create([
            'facture_id' => $validated['facture_id'],
            'description' => $validated['description'],
            'statut' => 'En attente',
        ]);

        return redirect()->route('reclamations')->with('success', 'Réclamation créée avec succès.');
    }

    /**
     * Affiche le formulaire d'édition d'une réclamation existante.
     */
    public function edit(int $id): View
    {
        $reclamation = Reclamation::with('facture.abonne')->findOrFail($id);
        $statusOptions = ['En attente', 'En cours', 'Résolue'];

        return view('reclamations.edit', compact('reclamation', 'statusOptions'));
    }

    /**
     * Met à jour une réclamation existante.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $reclamation = Reclamation::findOrFail($id);

        $validated = $request->validate([
            'description' => 'required|string|min:10|max:1000',
            'statut' => 'required|in:En attente,En cours,Résolue',
        ]);

        $reclamation->update($validated);

        return redirect()->route('reclamations')->with('success', 'Réclamation mise à jour avec succès.');
    }
}
