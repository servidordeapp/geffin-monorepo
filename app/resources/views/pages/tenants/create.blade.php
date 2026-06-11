<x-layouts.app title="Novo tenant">
    <div class="mx-auto w-full container px-[clamp(20px,5vw,48px)] py-10">
        <x-page-header
            class="mb-8"
            eyebrow="Administração"
            heading="Novo tenant"
            subtitle="Cadastre a escola e o domínio inicial de acesso ao painel."
        />

        <form method="POST" action="{{ route('tenants.store') }}"
            class="container rounded-box border border-base-300 bg-base-100 p-6">
            @csrf

            <div class="space-y-4">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Nome</legend>
                    <input type="text" name="name" value="{{ old('name') }}" required autofocus
                        placeholder="Escola Exemplo"
                        class="input w-full @error('name') input-error @enderror" />
                    @error('name')
                        <p class="label text-error">{{ $message }}</p>
                    @enderror
                </fieldset>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Domínio</legend>
                    <input type="text" name="domain" value="{{ old('domain') }}" required
                        placeholder="exemplo.localhost"
                        class="input w-full font-mono @error('domain') input-error @enderror" />
                    @error('domain')
                        <p class="label text-error">{{ $message }}</p>
                    @enderror
                </fieldset>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <a href="{{ route('tenants.index') }}" class="btn btn-ghost">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </div>
        </form>
    </div>
</x-layouts.app>
