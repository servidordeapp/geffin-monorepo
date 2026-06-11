<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>@yield('title') — {{ config('app.name', 'Geffin') }}</title>

        @vite(['resources/css/app.css'])
    </head>
    <body class="bg-base-200 font-sans antialiased">
        <div class="flex min-h-screen flex-col">
            <header class="navbar border-b border-base-300 bg-base-100 px-[clamp(20px,5vw,48px)]">
                <div class="navbar-start">
                    <a href="{{ url('/') }}" class="inline-flex items-center gap-2.5 font-semibold text-base-content">
                        <span class="inline-flex size-7 items-center justify-center rounded-lg text-white shadow-lg shadow-primary/40
                                     bg-gradient-to-br from-primary to-success">
                            <x-icon-academic-cap class="size-4" />
                        </span>
                        <span class="font-display tracking-tight">{{ config('app.name', 'Geffin') }}</span>
                    </a>
                </div>
            </header>

            <main class="flex flex-1 items-center justify-center px-6 py-16" role="main">
                <div class="w-full max-w-[480px] text-center">
                    <h1 class="font-display text-2xl font-bold tracking-tight text-base-content">
                        @yield('message')
                    </h1>

                    <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                        @auth
                            <a href="{{ route('dashboard') }}" class="btn btn-primary">{{ __('Back to dashboard') }}</a>
                        @else
                            <a href="{{ url('/') }}" class="btn btn-primary">{{ __('Back to home') }}</a>
                        @endauth

                        <button type="button" onclick="window.history.back()" class="btn btn-ghost">
                            {{ __('Go back') }}
                        </button>
                    </div>
                </div>
            </main>

            <footer class="flex items-center justify-between border-t border-base-300 bg-base-100 px-[clamp(20px,5vw,48px)] py-4
                           font-mono text-[10.5px] uppercase tracking-[0.14em] text-base-content/50">
                <span>© {{ date('Y') }} {{ config('app.name', 'Geffin') }}</span>
                <span>v{{ config('app.version', '0.1.0') }} · São Paulo</span>
            </footer>
        </div>
    </body>
</html>
