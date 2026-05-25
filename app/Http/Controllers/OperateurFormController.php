<?php

namespace App\Http\Controllers;

use App\Models\Operateur;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class OperateurFormController extends Controller
{
    /**
     * Affiche la liste des utilisateurs (opérateurs).
     */
    public function index(): View
    {
        $operateurs = Operateur::orderBy('nom')->get();
        return view('utilisateurs.index', compact('operateurs'));
    }

    /**
     * Affiche le formulaire de création d'un nouvel utilisateur.
     */
    public function create(): View
    {
        return view('utilisateurs.create');
    }

    /**
     * Enregistre un nouvel utilisateur en base de données.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'login' => 'required|email|unique:operateurs,login',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,gestionnaire',
        ]);

        Operateur::create($validated);

        return redirect()->route('utilisateurs.index')->with('success', 'Utilisateur créé avec succès.');
    }
}
