<x-layouts.app title="Tenants">
    <div class="mx-auto w-full max-w-4xl px-6 py-10">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="font-display text-2xl font-semibold text-base-content">Tenants</h1>
            <a href="{{ route('tenants.create') }}" class="btn btn-primary btn-sm">Novo tenant</a>
        </div>

        @if (session('status'))
            <div class="alert alert-success mb-6" role="alert">
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <div class="overflow-x-auto rounded-box border border-base-300 bg-base-100">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Domínios</th>
                        <th>Criado em</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tenants as $tenant)
                        <tr>
                            <td class="font-medium">{{ $tenant->name }}</td>
                            <td>
                                @forelse ($tenant->domains as $domain)
                                    <span class="badge badge-ghost">{{ $domain->domain }}</span>
                                @empty
                                    —
                                @endforelse
                            </td>
                            <td>{{ $tenant->created_at?->format('d/m/Y H:i') }}</td>
                            <td class="text-right">
                                <a href="{{ route('tenants.edit', $tenant) }}" class="btn btn-ghost btn-xs">Editar</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-base-content/60">
                                Nenhum tenant cadastrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
