@extends('layouts.app')

@section('title', 'Créer une Facture')
@section('page-heading', 'Créer une nouvelle facture')

@section('content')
    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
        <form action="{{ route('factures.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid gap-6 lg:grid-cols-2">
                <div>
                    <label for="abonne_id" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Abonné</label>
                    <select 
                        name="abonne_id" 
                        id="abonne_id"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-slate-900 focus:border-slate-400 focus:outline-none dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100"
                        required
                    >
                        <option value="">-- Sélectionner un abonné --</option>
                        @foreach($abonnes as $abonne)
                            <option value="{{ $abonne->id }}" @selected(old('abonne_id') == $abonne->id)>
                                {{ $abonne->nom_complet }} - {{ $abonne->numero_compteur }}
                            </option>
                        @endforeach
                    </select>
                    @error('abonne_id')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="consommation" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Consommation (m³)</label>
                    <input 
                        type="number" 
                        name="consommation" 
                        id="consommation" 
                        value="{{ old('consommation') }}"
                        min="1"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-slate-900 focus:border-slate-400 focus:outline-none dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100"
                        required
                    />
                    @error('consommation')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex gap-3">
                <button 
                    type="submit" 
                    class="rounded-2xl bg-slate-900 px-6 py-3 font-medium text-white hover:bg-slate-800"
                >
                    Créer la facture
                </button>
                <a 
                    href="{{ route('factures') }}" 
                    class="rounded-2xl border border-slate-200 bg-slate-50 px-6 py-3 font-medium text-slate-900 hover:bg-slate-100 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100 dark:hover:bg-slate-800"
                >
                    Annuler
                </a>
            </div>
        </form>
    </div>
@endsection
