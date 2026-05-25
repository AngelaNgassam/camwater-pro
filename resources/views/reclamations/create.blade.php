@extends('layouts.app')

@section('title', 'Créer une Réclamation')
@section('page-heading', 'Créer une nouvelle réclamation')

@section('content')
    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
        <form action="{{ route('reclamations.store') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label for="facture_id" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Facture</label>
                <select 
                    name="facture_id" 
                    id="facture_id"
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-slate-900 focus:border-slate-400 focus:outline-none dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100"
                    required
                >
                    <option value="">-- Sélectionner une facture --</option>
                    @foreach($factures as $facture)
                        <option value="{{ $facture->id }}" @selected(old('facture_id') == $facture->id)>
                            F-{{ str_pad($facture->id, 4, '0', STR_PAD_LEFT) }} - 
                            {{ $facture->abonne->nom_complet }} - 
                            {{ number_format($facture->montant_total, 0, ',', ' ') }} FCFA
                        </option>
                    @endforeach
                </select>
                @error('facture_id')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Description</label>
                <textarea 
                    name="description" 
                    id="description" 
                    rows="6"
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-slate-900 focus:border-slate-400 focus:outline-none dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100"
                    required
                >{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-3">
                <button 
                    type="submit" 
                    class="rounded-2xl bg-slate-900 px-6 py-3 font-medium text-white hover:bg-slate-800"
                >
                    Créer la réclamation
                </button>
                <a 
                    href="{{ route('reclamations') }}" 
                    class="rounded-2xl border border-slate-200 bg-slate-50 px-6 py-3 font-medium text-slate-900 hover:bg-slate-100 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100 dark:hover:bg-slate-800"
                >
                    Annuler
                </a>
            </div>
        </form>
    </div>
@endsection
