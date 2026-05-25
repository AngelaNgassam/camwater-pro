@extends('layouts.app')

@section('title', 'Créer un Utilisateur')
@section('page-heading', 'Créer un nouvel utilisateur')

@section('content')
    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
        <form action="{{ route('utilisateurs.store') }}" method="POST" class="space-y-6">
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

                <div class="lg:col-span-2">
                    <label for="login" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Email (login)</label>
                    <input 
                        type="email" 
                        name="login" 
                        id="login" 
                        value="{{ old('login') }}"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-slate-900 focus:border-slate-400 focus:outline-none dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100"
                        required
                    />
                    @error('login')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Mot de passe</label>
                    <input 
                        type="password" 
                        name="password" 
                        id="password" 
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-slate-900 focus:border-slate-400 focus:outline-none dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100"
                        required
                    />
                    @error('password')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Confirmer le mot de passe</label>
                    <input 
                        type="password" 
                        name="password_confirmation" 
                        id="password_confirmation" 
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-slate-900 focus:border-slate-400 focus:outline-none dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100"
                        required
                    />
                </div>

                <div class="lg:col-span-2">
                    <label for="role" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Rôle</label>
                    <select 
                        name="role" 
                        id="role"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-slate-900 focus:border-slate-400 focus:outline-none dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100"
                        required
                    >
                        <option value="">-- Sélectionner un rôle --</option>
                        <option value="admin" @selected(old('role') === 'admin')>Administrateur</option>
                        <option value="gestionnaire" @selected(old('role') === 'gestionnaire')>Gestionnaire</option>
                    </select>
                    @error('role')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex gap-3">
                <button 
                    type="submit" 
                    class="rounded-2xl bg-slate-900 px-6 py-3 font-medium text-white hover:bg-slate-800"
                >
                    Créer l'utilisateur
                </button>
                <a 
                    href="{{ route('utilisateurs.index') }}" 
                    class="rounded-2xl border border-slate-200 bg-slate-50 px-6 py-3 font-medium text-slate-900 hover:bg-slate-100 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100 dark:hover:bg-slate-800"
                >
                    Annuler
                </a>
            </div>
        </form>
    </div>
@endsection
