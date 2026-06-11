<x-layouts.app title="Dashboard">
    <div class="mx-auto w-full container px-[clamp(20px,5vw,48px)] py-10">
        <x-page-header
            class="mb-8"
            eyebrow="Visão geral"
            heading="Dashboard"
            subtitle="Acompanhe a operação financeira das escolas em um único painel."
        >
            <a href="{{ route('tenants.index') }}" class="btn btn-primary gap-2">
                Gerenciar tenants
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="size-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                </svg>
            </a>
        </x-page-header>

        <div class="stats mb-8 border border-base-300 bg-base-100 shadow-sm">
            <div class="stat">
                <div class="stat-figure text-primary">
                    <x-icon-academic-cap class="size-8" />
                </div>
                <div class="stat-title">Tenants cadastrados</div>
                <div class="stat-value">{{ $tenantCount }}</div>
                <div class="stat-desc">Escolas registradas no sistema</div>
            </div>
        </div>

        <div class="flex items-center justify-center rounded-box border border-dashed border-base-300 bg-base-100 py-24">
            <p class="text-base-content/60">Conteúdo do dashboard em construção.</p>
        </div>
    </div>
</x-layouts.app>
