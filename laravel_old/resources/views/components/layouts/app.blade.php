@props([
    'title' => null,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? config('app.name', 'Geffin') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="min-h-screen">
        <nav class="ui-topbar">
            <div class="ui-topbar__brand">
                <a href="{{ route('tenants.index') }}" class="ui-topbar__logo" wire:navigate>
                    {{ config('app.name', 'Geffin') }}
                </a>
                @auth
                    @if (auth()->user()->is_central_admin)
                        <span class="ui-topbar__tag">Admin Central</span>
                    @endif
                @endauth
            </div>

            @auth
                <div class="ui-topbar__nav">
                    <a href="{{ route('tenants.index') }}" class="ui-topbar__link" wire:navigate>Inquilinos</a>
                    <span class="ui-topbar__divider"></span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="ui-topbar__link ui-topbar__link--muted">Sair</button>
                    </form>
                </div>
            @endauth
        </nav>

        <main class="ui-shell">
            {{ $slot }}
        </main>

        @livewireScripts
        @fluxScripts
    </body>
</html>
