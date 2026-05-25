@extends('layouts.app')

@section('title', 'Modifier la Réclamation')
@section('page-heading', 'Modifier une réclamation')

@section('content')
    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
        <form action="{{ route('reclamations.update', $reclamation->id) }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="lg:col-span-2">
                    <label for="facture" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Facture liée</label>
                    <input
                        type="text"
                        id="facture"
                        value="{{ $reclamation->facture ? 'F-' . str_pad($reclamation->facture->id, 4, '0', STR_PAD_LEFT) . ' - ' . $reclamation->facture->abonne->nom_complet : 'N/A' }}"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-100 px-4 py-3 text-slate-900 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100"
                        readonly
                    />
                </div>

                <div class="lg:col-span-2">
                    <label for="description" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Description</label>
                    <textarea
                        name="description"
                        id="description"
                        rows="6"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-slate-900 focus:border-slate-400 focus:outline-none dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100"
                        required
                    >{{ old('description', $reclamation->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="lg:col-span-2">
                    <label for="statut" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Statut</label>
                    <select
                        id="statut"
                        name="statut"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-slate-900 focus:border-slate-400 focus:outline-none dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100"
                        required
                    >
                        @foreach($statusOptions as $status)
                            <option value="{{ $status }}" @selected(old('statut', $reclamation->statut) === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                    @error('statut')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="rounded-2xl bg-slate-900 px-6 py-3 font-medium text-white hover:bg-slate-800">Enregistrer</button>
                <a href="{{ route('reclamations') }}" class="rounded-2xl border border-slate-200 bg-slate-50 px-6 py-3 font-medium text-slate-900 hover:bg-slate-100 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100 dark:hover:bg-slate-800">Annuler</a>
            </div>
        </form>
    </div>
@endsection
