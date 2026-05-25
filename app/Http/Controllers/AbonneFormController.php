<?php

namespace App\Http\Controllers;

use App\Models\Abonne;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AbonneFormController extends Controller
{
    /**
     * Affiche le formulaire de création d'un abonné.
     */
    public function create(): View
    {
        return view('abonnes.create');
    }

    /**
     * Enregistre un nouvel abonné en base de données.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'ville' => 'required|string|max:255',
            'quartier' => 'required|string|max:255',
            'numero_compteur' => 'required|string|unique:abonnes',
            'type_abonnement' => 'required|in:Domestique,Professionnel',
        ]);

        Abonne::create($validated);

        return redirect()->route('abonnes')->with('success', 'Abonné créé avec succès.');
    }

    /**
     * Affiche le formulaire d'édition d'un abonné.
     */
    public function edit($id): View
    {
        $abonne = Abonne::findOrFail($id);
        return view('abonnes.edit', compact('abonne'));
    }

    /**
     * Met à jour un abonné en base de données.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $abonne = Abonne::findOrFail($id);

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'ville' => 'required|string|max:255',
            'quartier' => 'required|string|max:255',
            'numero_compteur' => 'required|string|unique:abonnes,numero_compteur,' . $abonne->id,
            'type_abonnement' => 'required|in:Domestique,Professionnel',
        ]);

        $abonne->update($validated);

        return redirect()->route('abonnes')->with('success', 'Abonné mis à jour avec succès.');
    }

    /**
     * Supprime un abonné.
     */
    public function destroy($id): RedirectResponse
    {
        $abonne = Abonne::findOrFail($id);
        $abonne->delete();

        return redirect()->route('abonnes')->with('success', 'Abonné supprimé avec succès.');
    }
}
