<x-layouts.app :title="$tenant->name">
    @php
        $palette = ['#0B2E5C', '#1E40AF', '#047857', '#334155', '#155E75', '#3730A3'];
        $color = $palette[abs(crc32($tenant->slug)) % count($palette)];
        $isActive = ! $tenant->deleted_at && (($tenant->status?->value ?? 'active') === 'active');
    @endphp

    <div class="max-w-[720px] mx-auto">
        <flux:button :href="route('tenants.index')" variant="ghost" size="sm" icon="arrow-left" wire:navigate class="mb-6">
            Inquilinos
        </flux:button>

        <header class="flex items-start gap-[18px] mb-7">
            <span class="ui-avatar ui-avatar--lg" style="background: {{ $color }};" aria-hidden="true">{{ mb_strtoupper(mb_substr($tenant->name, 0, 1)) }}</span>
            <div class="flex-1 min-w-0">
                <p class="ui-overline">Inquilino</p>
                <h1 class="ui-page-title mt-2">{{ $tenant->name }}</h1>
                <div class="flex items-center gap-[10px] mt-3 flex-wrap">
                    <code class="ui-code">{{ $tenant->slug }}</code>
                    @if ($tenant->deleted_at)
                        <x-ui.badge color="red">Excluído</x-ui.badge>
                    @elseif ($isActive)
                        <x-ui.badge color="green">Ativo</x-ui.badge>
                    @else
                        <x-ui.badge color="amber">Inativo</x-ui.badge>
                    @endif
                </div>
            </div>
            @unless ($tenant->deleted_at)
                <flux:button :href="route('tenants.edit', $tenant)" variant="ghost" size="sm" icon="pencil-square" wire:navigate>Editar</flux:button>
            @endunless
        </header>

        <x-ui.detail-grid>
            <x-ui.detail-item label="Slug" :value="$tenant->slug" mono />
            <x-ui.detail-item label="Status" :value="$tenant->deleted_at ? 'Excluído' : ($isActive ? 'Ativo' : 'Inativo')" />
            <x-ui.detail-item label="Criado em" :value="optional($tenant->created_at)->format('d/m/Y H:i') ?? '—'" mono />
            <x-ui.detail-item
                :label="$tenant->deleted_at ? 'Excluído em' : 'Atualizado em'"
                :value="optional($tenant->deleted_at ?? $tenant->updated_at)->format('d/m/Y H:i') ?? '—'"
                mono
            />
        </x-ui.detail-grid>

        <section class="mt-6">
            <div class="ui-overline mb-3">Domínios · {{ $tenant->domains->count() }}</div>
            @if ($tenant->domains->isNotEmpty())
                <div class="flex flex-wrap gap-2">
                    @foreach ($tenant->domains as $domain)
                        <a href="{{ $domain->url() }}" target="_blank" rel="noopener noreferrer"
                           class="ui-code inline-flex items-center gap-2 px-3 py-[7px] hover:border-[var(--border-strong)] transition-colors"
                           title="Abrir {{ $domain->domain }}">
                            <flux:icon name="globe-alt" class="size-4" />
                            {{ $domain->domain }}
                            <flux:icon name="arrow-top-right-on-square" class="size-3.5" />
                        </a>
                    @endforeach
                </div>
            @else
                <p class="t-body-sm" style="color: var(--fg-3);">Nenhum domínio associado.</p>
            @endif
        </section>

        <div class="mt-7">
            @if ($tenant->deleted_at)
                <x-ui.zone
                    variant="info"
                    title="Restaurar inquilino"
                    description="O banco de dados foi preservado no soft-delete. Restaurar reativa o acesso imediatamente."
                >
                    <x-slot:action>
                        <form method="POST" action="{{ route('tenants.restore', $tenant) }}">
                            @csrf
                            <flux:button type="submit" variant="primary" icon="arrow-path">Restaurar</flux:button>
                        </form>
                    </x-slot:action>
                </x-ui.zone>
            @else
                <x-ui.zone
                    variant="danger"
                    title="Excluir inquilino"
                    description="Soft-delete: o banco é preservado e o registro pode ser restaurado depois. Nenhum dado é apagado."
                >
                    <x-slot:action>
                        <form method="POST" action="{{ route('tenants.destroy', $tenant) }}"
                              onsubmit="return confirm('Excluir o inquilino “{{ $tenant->name }}”? O banco de dados é preservado e pode ser restaurado.');">
                            @csrf @method('DELETE')
                            <flux:button type="submit" variant="danger" icon="trash">Excluir</flux:button>
                        </form>
                    </x-slot:action>
                </x-ui.zone>
            @endif
        </div>
    </div>
</x-layouts.app>
