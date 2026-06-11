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
                                        <a href="{{ request()->getScheme() }}://{{ $domain->domain }}{{ in_array(request()->getPort(), [80, 443], true) ? '' : ':'.request()->getPort() }}"
                                            target="_blank" rel="noopener"
                                            class="badge badge-ghost gap-1 font-mono text-xs hover:badge-primary">
                                            {{ $domain->domain }}
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="2" stroke="currentColor" class="size-3">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                            </svg>
                                        </a>
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
