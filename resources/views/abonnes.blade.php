@extends('layouts.app')

@section('title', 'Abonnés')
@section('page-heading', 'Abonnés')

@section('content')
    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-slate-500 dark:text-slate-400">Liste des abonnés</p>
                <h2 class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">Gérer les clients</h2>
            </div>
            <div class="flex items-center gap-3">
                <input
                    type="search"
                    placeholder="Rechercher..."
                    class="w-full max-w-sm rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-900 focus:border-slate-400 focus:outline-none dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100"
                />
                <a href="{{ route('abonnes.create') }}" class="rounded-2xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">Nouveau</a>
            </div>
        </div>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm dark:divide-slate-800">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                        <th class="px-4 py-3">Nom</th>
                        <th class="px-4 py-3">Numéro</th>
                        <th class="px-4 py-3">Adresse</th>
                        <th class="px-4 py-3">Type d'abonnement</th>
                        <th class="px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($abonnes as $abonne)
                        <tr class="bg-white dark:bg-slate-950">
                            <td class="px-4 py-4">{{ $abonne->nom_complet }}</td>
                            <td class="px-4 py-4">{{ $abonne->numero_compteur }}</td>
                            <td class="px-4 py-4">{{ $abonne->ville }}</td>
                            <td class="px-4 py-4">{{ $abonne->type_abonnement }}</td>
                            <td class="px-4 py-4">
                                <div class="flex gap-2">
                                    <a 
                                        href="{{ route('abonnes.edit', $abonne->id) }}"
                                        class="text-sm font-medium text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100"
                                    >
                                        Modifier
                                    </a>
                                    <form 
                                        action="{{ route('abonnes.destroy', $abonne->id) }}" 
                                        method="GET" 
                                        onsubmit="return confirm('Êtes-vous sûr ?');"
                                        style="display: inline;"
                                    >
                                        <button 
                                            type="submit"
                                            class="text-sm font-medium text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-200"
                                        >
                                            Supprimer
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="bg-slate-50 dark:bg-slate-900">
                            <td colspan="5" class="px-4 py-4 text-center text-sm text-slate-500 dark:text-slate-400">Aucun abonné trouvé.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
