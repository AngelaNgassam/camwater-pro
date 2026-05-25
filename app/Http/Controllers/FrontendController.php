<?php

namespace App\Http\Controllers;

use App\Models\Abonne;
use App\Models\Facture;
use App\Models\Reclamation;
use Illuminate\View\View;

class FrontendController extends Controller
{
    public function dashboard(): View
    {
        return view('dashboard', [
            'abonnesCount' => Abonne::count(),
            'facturesCount' => Facture::count(),
            'reclamationsCount' => Reclamation::count(),
            'recentAbonnes' => Abonne::orderBy('created_at', 'desc')->limit(5)->get(),
            'recentFactures' => Facture::with('abonne')->orderBy('date_emission', 'desc')->limit(5)->get(),
            'recentReclamations' => Reclamation::orderBy('created_at', 'desc')->limit(5)->get(),
        ]);
    }

    public function abonnes(): View
    {
        return view('abonnes', [
            'abonnes' => Abonne::orderBy('nom')->get(),
        ]);
    }

    public function factures(): View
    {
        return view('factures', [
            'factures' => Facture::with('abonne')->orderBy('date_emission', 'desc')->get(),
        ]);
    }

    public function reclamations(): View
    {
        return view('reclamations', [
            'reclamations' => Reclamation::with(['facture', 'operateur'])->orderBy('created_at', 'desc')->get(),
        ]);
    }
}
