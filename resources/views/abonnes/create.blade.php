@extends('layouts.app')

@section('title', 'Créer un Abonné')
@section('page-heading', 'Créer un nouvel abonné')

@section('content')
    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
        <form action="{{ route('abonnes.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid gap-6 lg:grid-cols-2">
                <div>
                    <label for="nom" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Nom</label>
                    <input 
                        type="text" 
                        name="nom" 
                        id="nom" 
                        value="{{ old('nom') }}"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-slate-900 focus:border-slate-400 focus:outline-none dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100"
                        required
                    />
                    @error('nom')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="prenom" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Prénom</label>
                    <input 
                        type="text" 
                        name="prenom" 
                        id="prenom" 
                        value="{{ old('prenom') }}"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-slate-900 focus:border-slate-400 focus:outline-none dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100"
                        required
                    />
                    @error('prenom')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="ville" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Ville</label>
                    <input 
                        type="text" 
                        name="ville" 
                        id="ville" 
                        value="{{ old('ville') }}"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-slate-900 focus:border-slate-400 focus:outline-none dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100"
                        required
                    />
                    @error('ville')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="quartier" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Quartier</label>
                    <input 
                        type="text" 
                        name="quartier" 
                        id="quartier" 
                        value="{{ old('quartier') }}"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-slate-900 focus:border-slate-400 focus:outline-none dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100"
                        required
                    />
                    @error('quartier')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="numero_compteur" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Numéro de compteur</label>
                    <input 
                        type="text" 
                        name="numero_compteur" 
                        id="numero_compteur" 
                        value="{{ old('numero_compteur') }}"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-slate-900 focus:border-slate-400 focus:outline-none dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100"
                        required
                    />
                    @error('numero_compteur')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="type_abonnement" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Type d'abonnement</label>
                    <select 
                        name="type_abonnement" 
                        id="type_abonnement"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-slate-900 focus:border-slate-400 focus:outline-none dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100"
                        required
                    >
                        <option value="">-- Sélectionner --</option>
                        <option value="Domestique" @selected(old('type_abonnement') === 'Domestique')>Domestique</option>
                        <option value="Professionnel" @selected(old('type_abonnement') === 'Professionnel')>Professionnel</option>
                    </select>
                    @error('type_abonnement')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex gap-3">
                <button 
                    type="submit" 
                    class="rounded-2xl bg-slate-900 px-6 py-3 font-medium text-white hover:bg-slate-800"
                >
                    Créer l'abonné
                </button>
                <a 
                    href="{{ route('abonnes') }}" 
                    class="rounded-2xl border border-slate-200 bg-slate-50 px-6 py-3 font-medium text-slate-900 hover:bg-slate-100 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100 dark:hover:bg-slate-800"
                >
                    Annuler
                </a>
            </div>
        </form>
    </div>
@endsection
