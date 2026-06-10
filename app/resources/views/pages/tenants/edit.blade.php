<x-layouts.app title="Editar tenant">
    <div class="mx-auto w-full max-w-lg px-6 py-10">
        <h1 class="mb-6 font-display text-2xl font-semibold text-base-content">Editar tenant</h1>

        @if (session('status'))
            <div class="alert alert-success mb-6" role="alert">
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('tenants.update', $tenant) }}"
            class="card border border-base-300 bg-base-100">
            @csrf
            @method('PUT')

            <div class="card-body gap-4">
                <label class="form-control w-full">
                    <span class="label-text mb-1">Nome</span>
                    <input type="text" name="name" value="{{ old('name', $tenant->name) }}" required
                        class="input input-bordered w-full @error('name') input-error @enderror" />
                    @error('name')
                        <span class="mt-1 text-sm text-error">{{ $message }}</span>
                    @enderror
                </label>

                <div class="card-actions justify-end">
                    <a href="{{ route('tenants.index') }}" class="btn btn-ghost">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </div>
        </form>

        <div class="card mt-6 border border-base-300 bg-base-100">
            <div class="card-body gap-4">
                <h2 class="card-title text-base">Domínios</h2>

                <ul class="divide-y divide-base-300">
                    @forelse ($tenant->domains as $domain)
                        <li class="flex items-center justify-between py-2">
                            <span>{{ $domain->domain }}</span>
                            @if ($tenant->domains->count() > 1)
                                <form method="POST"
                                    action="{{ route('tenants.domains.destroy', [$tenant, $domain]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-ghost btn-xs text-error">Remover</button>
                                </form>
                            @endif
                        </li>
                    @empty
                        <li class="py-2 text-base-content/60">Nenhum domínio cadastrado.</li>
                    @endforelse
                </ul>

                <form method="POST" action="{{ route('tenants.domains.store', $tenant) }}"
                    class="flex items-start gap-2">
                    @csrf
                    <label class="form-control flex-1">
                        <input type="text" name="domain" value="{{ old('domain') }}" required
                            placeholder="exemplo.localhost"
                            class="input input-bordered w-full @error('domain') input-error @enderror" />
                        @error('domain')
                            <span class="mt-1 text-sm text-error">{{ $message }}</span>
                        @enderror
                    </label>
                    <button type="submit" class="btn btn-primary">Adicionar</button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
