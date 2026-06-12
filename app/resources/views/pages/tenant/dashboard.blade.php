<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>Painel · {{ tenant('name') ?? config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body class="bg-base-200 font-sans antialiased">
        <main class="mx-auto w-full max-w-3xl px-6 py-12">
            <div class="font-mono text-[11px] uppercase tracking-[0.22em] text-primary">Painel do tenant</div>
            <h1 class="mt-2.5 font-display text-[2rem] font-bold leading-tight tracking-tight text-base-content">
                Bem-vindo, {{ auth()->guard('tenant')->user()->name }}
            </h1>
            <p class="mt-2.5 text-[0.9375rem] leading-relaxed text-base-content/70">
                Você está autenticado no ambiente da escola <strong>{{ tenant('name') }}</strong>.
            </p>

            <form method="POST" action="{{ route('tenant.logout') }}" class="mt-8">
                @csrf
                <button type="submit" class="btn btn-outline btn-sm">Sair</button>
            </form>
        </main>

        @livewireScripts
    </body>
</html>
