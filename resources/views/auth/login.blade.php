<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Connexion - Camwater Pro</title>
        @vite(['frontend/css/app.css', 'frontend/js/app.js'])
    </head>
    <body class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-slate-900 dark:to-slate-800 flex items-center justify-center px-4">
        <div class="w-full max-w-md">
            <div class="rounded-3xl border border-blue-200 bg-white p-8 shadow-xl dark:border-slate-700 dark:bg-slate-950">
                <div class="mb-8 text-center">
                    <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Camwater Pro</h1>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Gestion de la facturation et des abonnés</p>
                </div>

                @if ($errors->any())
                    <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 dark:border-red-900 dark:bg-red-900/20">
                        @foreach ($errors->all() as $error)
                            <p class="text-sm text-red-700 dark:text-red-200">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('login.submit') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="login" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Identifiant</label>
                        <input
                            type="text"
                            id="login"
                            name="login"
                            value="{{ old('login') }}"
                            required
                            autofocus
                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 placeholder-slate-500 focus:border-blue-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:placeholder-slate-400"
                            placeholder="Ex: admin@camwater.cm"
                        />
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Mot de passe</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 placeholder-slate-500 focus:border-blue-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:placeholder-slate-400"
                            placeholder="Votre mot de passe"
                        />
                    </div>

                    <button
                        type="submit"
                        class="w-full rounded-2xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-md transition hover:bg-blue-700 active:bg-blue-800 dark:bg-blue-700 dark:hover:bg-blue-600"
                    >
                        Se connecter
                    </button>
                </form>


            </div>
        </div>
    </body>
</html>
