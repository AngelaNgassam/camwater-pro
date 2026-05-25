@extends('layouts.app')

@section('title', 'Réclamations')
@section('page-heading', 'Réclamations')

@section('content')
    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-slate-500 dark:text-slate-400">Suivi des réclamations</p>
                <h2 class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">Réclamations en attente</h2>
            </div>
            <a href="{{ route('reclamations.create') }}" class="rounded-2xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">Créer une réclamation</a>
        </div>

        <div class="mt-6 space-y-4">
            @forelse($reclamations as $reclamation)
                <article class="rounded-3xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-950">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ \Illuminate\Support\Str::limit($reclamation->description, 70) }}</h3>
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Facture : {{ $reclamation->facture ? 'F-' . str_pad($reclamation->facture->id, 4, '0', STR_PAD_LEFT) : 'N/A' }}</p>
                        </div>
                        <div class="flex flex-col items-start gap-3 sm:items-end">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $reclamation->statut === 'Résolue' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900 dark:text-emerald-300' : ($reclamation->statut === 'En cours' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300') }}">
                                {{ $reclamation->statut }}
                            </span>
                            <a href="{{ route('reclamations.edit', $reclamation->id) }}" class="rounded-2xl border border-slate-200 bg-slate-100 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:hover:bg-slate-800">Modifier</a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5 text-center text-sm text-slate-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-400">
                    Aucune réclamation trouvée.
                </div>
            @endforelse
        </div>
    </div>
@endsection
