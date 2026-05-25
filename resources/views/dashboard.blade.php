@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-heading', 'Tableau de bord')

@section('content')
    <div class="grid gap-6 lg:grid-cols-3">
        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
            <p class="text-sm text-slate-500 dark:text-slate-400">Abonnés actifs</p>
            <p class="mt-3 text-4xl font-semibold text-slate-900 dark:text-white">{{ number_format($abonnesCount) }}</p>
            <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">Suivi du nombre d'abonnés enregistrés.</p>
        </article>

        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
            <p class="text-sm text-slate-500 dark:text-slate-400">Factures enregistrées</p>
            <p class="mt-3 text-4xl font-semibold text-slate-900 dark:text-white">{{ number_format($facturesCount) }}</p>
            <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">Toutes les factures de la base.</p>
        </article>

        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
            <p class="text-sm text-slate-500 dark:text-slate-400">Réclamations</p>
            <p class="mt-3 text-4xl font-semibold text-slate-900 dark:text-white">{{ number_format($reclamationsCount) }}</p>
            <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">Réclamations enregistrées dans le système.</p>
        </article>
    </div>

    <section class="mt-10 grid gap-6 lg:grid-cols-2">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Actions rapides</h2>
            <div class="mt-5 grid gap-3">
                <a href="{{ route('abonnes') }}" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-900 hover:bg-slate-100 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100 dark:hover:bg-slate-800">Voir les abonnés</a>
                <a href="{{ route('factures') }}" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-900 hover:bg-slate-100 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100 dark:hover:bg-slate-800">Voir les factures</a>
                <a href="{{ route('reclamations') }}" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-900 hover:bg-slate-100 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100 dark:hover:bg-slate-800">Voir les réclamations</a>
            </div>
        </div>


    </section>

    <section class="mt-10 grid gap-6 lg:grid-cols-2">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Derniers abonnés</h2>
            <div class="mt-5 space-y-3">
                @forelse($recentAbonnes as $abonne)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-900">
                        <p class="font-semibold text-slate-900 dark:text-white">{{ $abonne->nom_complet }}</p>
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ $abonne->ville }} — {{ $abonne->numero_compteur }}</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500 dark:text-slate-400">Aucun abonné récent.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Dernières réclamations</h2>
            <div class="mt-5 space-y-3">
                @forelse($recentReclamations as $reclamation)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-900">
                        <p class="font-semibold text-slate-900 dark:text-white">{{ $reclamation->description }}</p>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Statut : {{ $reclamation->statut }}</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500 dark:text-slate-400">Aucune réclamation récente.</p>
                @endforelse
            </div>
        </div>
    </section>
@endsection
