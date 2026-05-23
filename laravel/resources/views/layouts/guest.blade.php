<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js" defer></script>

        @livewireStyles
    </head>
    <body class="min-h-screen flex items-center justify-center" style="background: var(--bg-app);">
        <div class="w-full max-w-sm px-6 py-12">
            <div class="mb-8 text-center">
                <div
                    class="inline-flex items-center justify-center w-12 h-12 rounded-xl mb-4"
                    style="background: var(--bg-brand);"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        <polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                </div>

                <h1 class="text-2xl font-bold tracking-tight" style="color: var(--fg-1);">
                    {{ config('app.name') }}
                </h1>
                <p class="mt-1 text-sm" style="color: var(--fg-2);">
                    {{ $title ?? 'Acesse sua conta' }}
                </p>
            </div>

            <div
                class="rounded-xl p-8"
                style="background: var(--bg-surface); border: 1px solid var(--border-subtle); box-shadow: var(--shadow-md);"
            >
                {{ $slot }}
            </div>
        </div>

        @livewireScripts
        <script>
            document.addEventListener('DOMContentLoaded', () => lucide.createIcons());
            document.addEventListener('livewire:navigated', () => lucide.createIcons());
        </script>
    </body>
</html>
