@extends('layouts.app')

@section('title', 'Utilisateurs')
@section('page-heading', 'Utilisateurs (Opérateurs)')

@section('content')
    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
        <div class="mb-6">
            <a 
                href="{{ route('utilisateurs.create') }}" 
                class="rounded-2xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800"
            >
                Créer un nouvel utilisateur
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm dark:divide-slate-800">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                        <th class="px-4 py-3">Nom Complet</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Rôle</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($operateurs as $operateur)
                        <tr class="bg-white dark:bg-slate-950">
                            <td class="px-4 py-4">{{ $operateur->prenom }} {{ $operateur->nom }}</td>
                            <td class="px-4 py-4">{{ $operateur->login }}</td>
                            <td class="px-4 py-4">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $operateur->role === 'admin' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300' : ($operateur->role === 'gestionnaire' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300') }}">
                                    {{ ucfirst($operateur->role) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr class="bg-slate-50 dark:bg-slate-900">
                            <td colspan="3" class="px-4 py-4 text-center text-sm text-slate-500 dark:text-slate-400">
                                Aucun utilisateur trouvé.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
