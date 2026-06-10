<x-layouts.app title="Novo tenant">
    <div class="mx-auto w-full max-w-lg px-6 py-10">
        <h1 class="mb-6 font-display text-2xl font-semibold text-base-content">Novo tenant</h1>

        <form method="POST" action="{{ route('tenants.store') }}" class="card border border-base-300 bg-base-100">
            @csrf

            <div class="card-body gap-4">
                <label class="form-control w-full">
                    <span class="label-text mb-1">Nome</span>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="input input-bordered w-full @error('name') input-error @enderror" />
                    @error('name')
                        <span class="mt-1 text-sm text-error">{{ $message }}</span>
                    @enderror
                </label>

                <label class="form-control w-full">
                    <span class="label-text mb-1">Domínio</span>
                    <input type="text" name="domain" value="{{ old('domain') }}" required
                        placeholder="exemplo.localhost"
                        class="input input-bordered w-full @error('domain') input-error @enderror" />
                    @error('domain')
                        <span class="mt-1 text-sm text-error">{{ $message }}</span>
                    @enderror
                </label>

                <div class="card-actions justify-end">
                    <a href="{{ route('tenants.index') }}" class="btn btn-ghost">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </div>
        </form>
    </div>
</x-layouts.app>
