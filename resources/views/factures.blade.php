@extends('layouts.app')

@section('title', 'Factures')
@section('page-heading', 'Factures')

@section('content')
    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-slate-500 dark:text-slate-400">Historique des factures</p>
                <h2 class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">Factures récentes</h2>
            </div>
            <a href="{{ route('factures.create') }}" class="rounded-2xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">Créer une facture</a>
        </div>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm dark:divide-slate-800">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                        <th class="px-4 py-3">Référence</th>
                        <th class="px-4 py-3">Abonné</th>
                        <th class="px-4 py-3">Montant</th>
                        <th class="px-4 py-3">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($factures as $facture)
                        <tr class="bg-white dark:bg-slate-950">
                            <td class="px-4 py-4">F-{{ str_pad($facture->id, 4, '0', STR_PAD_LEFT) }}</td>
                            <td class="px-4 py-4">{{ $facture->abonne ? $facture->abonne->nom_complet : 'N/A' }}</td>
                            <td class="px-4 py-4">{{ number_format($facture->montant_total, 0, ',', ' ') }} FCFA</td>
                            <td class="px-4 py-4">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $facture->statut === 'Payée' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900 dark:text-emerald-300' : ($facture->statut === 'Impayée' ? 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-300') }}">
                                    {{ $facture->statut }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr class="bg-slate-50 dark:bg-slate-900">
                            <td colspan="4" class="px-4 py-4 text-center text-sm text-slate-500 dark:text-slate-400">Aucune facture trouvée.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
