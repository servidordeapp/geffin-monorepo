<x-layouts.app title="Tenants">
    <div class="mx-auto w-full container px-[clamp(20px,5vw,48px)] py-10">
        <x-page-header
            class="mb-8"
            eyebrow="Administração"
            heading="Tenants"
            subtitle="Escolas e instituições com acesso ao hub, cada uma com seu próprio banco de dados isolado."
        >
            <a href="{{ route('tenants.create') }}" class="btn btn-primary gap-2">
                Novo tenant
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="size-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
            </a>
        </x-page-header>

        @if (session('status'))
            <div class="alert alert-success mb-6" role="status">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="size-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <div class="overflow-x-auto rounded-box border border-base-300 bg-base-100">
            <table class="table">
                <thead>
                    <tr class="font-mono text-[11px] uppercase tracking-[0.14em] text-base-content/60">
                        <th>Nome</th>
                        <th>Domínios</th>
                        <th>Criado em</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tenants as $tenant)
                        <tr>
                            <td class="font-medium text-base-content">{{ $tenant->name }}</td>
                            <td>
                                <div class="flex flex-wrap gap-1.5">
                                    @forelse ($tenant->domains as $domain)
                                        <span class="badge badge-ghost font-mono text-xs">{{ $domain->domain }}</span>
                                    @empty
                                        <span class="text-base-content/50">—</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="text-base-content/70">{{ $tenant->created_at?->format('d/m/Y H:i') }}</td>
                            <td class="text-right">
                                <a href="{{ route('tenants.edit', $tenant) }}" class="btn btn-ghost btn-xs">Editar</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-10 text-center text-base-content/60">
                                Nenhum tenant cadastrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
