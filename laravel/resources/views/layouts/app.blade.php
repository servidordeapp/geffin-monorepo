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
    <body>
        {{ $slot }}

        @livewireScripts
        <script>
            document.addEventListener('DOMContentLoaded', () => lucide.createIcons());
            document.addEventListener('livewire:navigated', () => lucide.createIcons());
        </script>
    </body>
</html>
