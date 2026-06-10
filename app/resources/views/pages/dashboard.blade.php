<x-layouts.app>
    <div class="flex min-h-screen flex-col">
        <header class="navbar border-b border-base-300 bg-base-100 px-6">
            <div class="navbar-start">
                <span class="font-display font-semibold text-base-content">{{ config('app.name') }}</span>
            </div>
            <div class="navbar-end gap-3">
                <span class="text-sm text-base-content/70">{{ auth()->user()->name }}</span>
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
            </div>
        </header>

        <div class="flex flex-1 items-center justify-center">
            <p class="text-base-content/60">Dashboard</p>
        </div>
    </div>
</x-layouts.app>
