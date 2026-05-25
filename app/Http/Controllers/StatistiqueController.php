<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatistiqueController extends Controller
{
    /**
     * Retourne les statistiques globales :
     *   - Nombre et total des factures par ville (mois courant)
     *   - Nombre et total des factures par mois (12 derniers mois)
     *   - Nombre total d'abonnés par type
     *
     * Compatible SQLite (tests) et MySQL (production).
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $isSqlite = DB::getDriverName() === 'sqlite';

        // ── Stat 1 : Factures par ville pour le mois courant ──────────────
        $facturesParVille = DB::table('factures')
            ->join('abonnes', 'abonnes.id', '=', 'factures.abonne_id')
            ->select(
                'abonnes.ville',
                DB::raw('COUNT(factures.id) as nombre_factures'),
                DB::raw('SUM(factures.montant_total) as total_montant_fcfa')
            )
            ->whereYear('factures.date_emission',  now()->year)
            ->whereMonth('factures.date_emission', now()->month)
            ->groupBy('abonnes.ville')
            ->orderByDesc('total_montant_fcfa')
            ->get();

        // ── Stat 2 : Total facturé par mois sur les 12 derniers mois ──────
        // YEAR() / MONTH() → MySQL   |   strftime() → SQLite
        if ($isSqlite) {
            $facturesParMois = DB::table('factures')
                ->select(
                    DB::raw("CAST(strftime('%Y', date_emission) AS INTEGER) as annee"),
                    DB::raw("CAST(strftime('%m', date_emission) AS INTEGER) as mois"),
                    DB::raw('COUNT(id)          as nombre_factures'),
                    DB::raw('SUM(montant_total) as total_montant_fcfa')
                )
                ->where('date_emission', '>=', now()->subMonths(12))
                ->groupBy('annee', 'mois')
                ->orderByDesc('annee')
                ->orderByDesc('mois')
                ->get();
        } else {
            $facturesParMois = DB::table('factures')
                ->select(
                    DB::raw('YEAR(date_emission)  as annee'),
                    DB::raw('MONTH(date_emission) as mois'),
                    DB::raw('COUNT(id)            as nombre_factures'),
                    DB::raw('SUM(montant_total)   as total_montant_fcfa')
                )
                ->where('date_emission', '>=', now()->subMonths(12))
                ->groupBy('annee', 'mois')
                ->orderByDesc('annee')
                ->orderByDesc('mois')
                ->get();
        }

        // ── Stat 3 : Répartition des abonnés par type ─────────────────────
        $abonnesParType = DB::table('abonnes')
            ->select(
                'type_abonnement',
                DB::raw('COUNT(id) as nombre')
            )
            ->groupBy('type_abonnement')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => [
                'factures_par_ville_ce_mois' => $facturesParVille,
                'factures_par_mois'          => $facturesParMois,
                'abonnes_par_type'           => $abonnesParType,
            ],
        ]);
    }
}