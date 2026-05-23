<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body class="min-h-screen bg-gray-50 flex items-center justify-center">
        <div class="w-full max-w-sm px-6">
            <div class="mb-8 text-center">
                <h1 class="text-2xl font-bold text-gray-900">{{ config('app.name') }}</h1>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
                {{ $slot }}
            </div>
        </div>

        @livewireScripts
    </body>
</html>
