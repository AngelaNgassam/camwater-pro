<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Camwater Pro') }} - @yield('title')</title>
        @vite(['frontend/css/app.css', 'frontend/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100">
        <div x-data="{ mobileOpen: false }" class="min-h-screen flex flex-col">
            <header class="border-b border-slate-200 bg-white/90 backdrop-blur dark:border-slate-800 dark:bg-slate-900/90">
                <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                    <a href="/" class="font-semibold text-slate-900 dark:text-white">Camwater-Pro 2026</a>

                    <div class="hidden gap-4 items-center md:flex">
                        <span class="text-sm text-slate-600 dark:text-slate-300">{{ auth('web')->user()->prenom }} {{ auth('web')->user()->nom }}</span>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-sm text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">Déconnexion</button>
                        </form>
                    </div>

                    <button
                        @click="mobileOpen = !mobileOpen"
                        class="inline-flex items-center gap-2 rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm transition hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:hover:bg-slate-800 md:hidden"
                    >
                        <span x-text="mobileOpen ? 'Fermer' : 'Menu'"></span>
                    </button>

                    <nav class="hidden items-center gap-3 md:flex">
                        <a href="{{ route('dashboard') }}" class="rounded-md px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800">Dashboard</a>
                        <a href="{{ route('abonnes') }}" class="rounded-md px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800">Abonnés</a>
                        <a href="{{ route('factures') }}" class="rounded-md px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800">Factures</a>
                        <a href="{{ route('reclamations') }}" class="rounded-md px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800">Réclamations</a>
                        @if (auth('web')->user()->role === 'admin')
                            <a href="{{ route('utilisateurs.index') }}" class="rounded-md px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800">Utilisateurs</a>
                        @endif
                    </nav>
                </div>

                <div x-show="mobileOpen" x-cloak class="border-t border-slate-200 bg-white/95 px-4 py-4 dark:border-slate-800 dark:bg-slate-950/95 md:hidden">
                    <nav class="flex flex-col gap-2">
                        <a href="{{ route('dashboard') }}" class="rounded-md px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800">Dashboard</a>
                        <a href="{{ route('abonnes') }}" class="rounded-md px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800">Abonnés</a>
                        <a href="{{ route('factures') }}" class="rounded-md px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800">Factures</a>
                        <a href="{{ route('reclamations') }}" class="rounded-md px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800">Réclamations</a>
                        @if (auth('web')->user()->role === 'admin')
                            <a href="{{ route('utilisateurs.index') }}" class="rounded-md px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800">Utilisateurs</a>
                        @endif
                        <div class="border-t border-slate-200 pt-3 dark:border-slate-700">
                            <p class="px-3 py-2 text-sm text-slate-600 dark:text-slate-300">{{ auth('web')->user()->prenom }} {{ auth('web')->user()->nom }}</p>
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="w-full text-left rounded-md px-3 py-2 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">Déconnexion</button>
                            </form>
                        </div>
                    </nav>
                </div>
            </header>

            <main class="flex-1 px-4 py-8 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-7xl">
                    <header class="mb-8">
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-900 dark:text-white">@yield('page-heading')</h1>
                    </header>

                    @yield('content')
                </div>
            </main>

            <footer class="border-t border-slate-200 bg-white/90 px-4 py-4 text-sm text-slate-500 dark:border-slate-800 dark:bg-slate-900/90 dark:text-slate-400">
                <div class="mx-auto max-w-7xl"> Camwater Pro — CDWFS 2026</div>
            </footer>
        </div>
    </body>
</html>
