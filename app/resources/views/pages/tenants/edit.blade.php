<x-layouts.app title="Editar tenant">
    <div class="mx-auto w-full container px-[clamp(20px,5vw,48px)] py-10">
        <x-page-header
            class="mb-8"
            eyebrow="Administração"
            heading="Editar tenant"
            subtitle="Atualize os dados da escola e gerencie seus domínios de acesso."
        />

        @if (session('status'))
            <div class="alert alert-success mb-6" role="status">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="size-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('tenants.update', $tenant) }}"
            class="container rounded-box border border-base-300 bg-base-100 p-6">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Nome</legend>
                    <input type="text" name="name" value="{{ old('name', $tenant->name) }}" required
                        class="input w-full @error('name') input-error @enderror" />
                    @error('name')
                        <p class="label text-error">{{ $message }}</p>
                    @enderror
                </fieldset>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <a href="{{ route('tenants.index') }}" class="btn btn-ghost">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </div>
        </form>

        <div class="mt-6 container rounded-box border border-base-300 bg-base-100 p-6">
            <h2 class="font-display text-base font-semibold tracking-tight text-base-content">Domínios</h2>

            <ul class="mt-4 divide-y divide-base-300">
                @forelse ($tenant->domains as $domain)
                    <li class="flex items-center justify-between py-2">
                        <span class="font-mono text-sm">{{ $domain->domain }}</span>
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
                class="mt-4 flex items-start gap-2">
                @csrf
                <fieldset class="fieldset flex-1">
                    <input type="text" name="domain" value="{{ old('domain') }}" required
                        placeholder="exemplo.localhost"
                        class="input w-full font-mono @error('domain') input-error @enderror" />
                    @error('domain')
                        <p class="label text-error">{{ $message }}</p>
                    @enderror
                </fieldset>
                <button type="submit" class="btn btn-primary">Adicionar</button>
            </form>
        </div>
    </div>
</x-layouts.app>
