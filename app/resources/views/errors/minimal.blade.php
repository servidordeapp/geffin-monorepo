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
                    <div class="font-mono text-[11px] uppercase tracking-[0.22em] text-primary">
                        {{ __('Error') }} @yield('code')
                    </div>

                    <div class="mt-3 font-display text-[clamp(4.5rem,12vw,6.5rem)] font-bold leading-none tracking-tight
                                bg-gradient-to-br from-primary to-success bg-clip-text text-transparent">
                        @yield('code')
                    </div>

                    <h1 class="mt-4 font-display text-2xl font-bold tracking-tight text-base-content">
                        @yield('message')
                    </h1>

                    <p class="mt-3 text-[0.9375rem] leading-relaxed text-base-content/70">
                        @yield('description', __('Something went wrong. Use the buttons below to get back on track.'))
                    </p>

                    <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                        @auth
                            <a href="{{ route('dashboard') }}" class="btn btn-primary gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" />
                                </svg>
                                {{ __('Back to dashboard') }}
                            </a>
                        @else
                            <a href="{{ url('/') }}" class="btn btn-primary gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" />
                                </svg>
                                {{ __('Back to home') }}
                            </a>
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
