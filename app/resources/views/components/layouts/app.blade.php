<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body class="bg-base-200 font-sans antialiased">
        <div class="flex min-h-screen flex-col">
            <header class="navbar border-b border-base-300 bg-base-100 px-[clamp(20px,5vw,48px)]">
                <div class="navbar-start">
                    <a href="{{ route('dashboard') }}" wire:navigate
                        class="inline-flex items-center gap-2.5 font-semibold text-base-content">
                        <span class="inline-flex size-7 items-center justify-center rounded-lg text-white shadow-lg shadow-primary/40
                                     bg-gradient-to-br from-primary to-success">
                            <x-icon-academic-cap class="size-4" />
                        </span>
                        <span class="font-display tracking-tight">{{ config('app.name', 'Geffin') }}</span>
                    </a>
                </div>

                <div class="navbar-end gap-3">
                    @auth
                        <span class="hidden text-sm text-base-content/70 sm:inline">{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-ghost btn-sm gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                                </svg>
                                Sair
                            </button>
                        </form>
                    @endauth
                </div>
            </header>

            <main class="flex-1">
                {{ $slot }}
            </main>

            <footer class="flex items-center justify-between border-t border-base-300 bg-base-100 px-[clamp(20px,5vw,48px)] py-4
                           font-mono text-[10.5px] uppercase tracking-[0.14em] text-base-content/50">
                <span>© {{ date('Y') }} {{ config('app.name', 'Geffin') }}</span>
                <span>v{{ config('app.version', '0.1.0') }} · São Paulo</span>
            </footer>
        </div>

        @livewireScripts
    </body>
</html>
