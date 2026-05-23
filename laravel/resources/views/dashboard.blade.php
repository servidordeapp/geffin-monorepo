<x-layouts.app title="Dashboard">
    <div class="min-h-screen bg-gray-50 p-8">
        <div class="max-w-4xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-sm text-gray-500 hover:text-gray-700 transition">
                        Sair
                    </button>
                </form>
            </div>

            <p class="text-gray-600">Bem-vindo, {{ auth()->user()->name }}.</p>
        </div>
    </div>
</x-layouts.app>
