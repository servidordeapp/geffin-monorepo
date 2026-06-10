<div>
    <x-ui.page-header
        overline="Gestão do sistema · Multi-tenant"
        title="Inquilinos"
        subtitle="Escolas e organizações provisionadas na plataforma."
    >
        <x-slot:badge>
            <span class="count">{{ $stats['active'] }} ativos</span>
        </x-slot:badge>
        <x-slot:actions>
            <flux:button :href="route('tenants.create')" variant="primary" icon="plus" wire:navigate>
                Novo inquilino
            </flux:button>
        </x-slot:actions>
    </x-ui.page-header>

    @if (session('status'))
        <x-ui.alert variant="info" class="mb-5">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.stats label="Resumo">
        <x-ui.stat
            label="Total cadastrado"
            :value="$stats['total']"
            hint="inquilinos na base, incluindo excluídos"
            dot="var(--color-brand-primary-500)"
        />
        <x-ui.stat
            label="Em operação"
            :value="$stats['active']"
            hint="ativos e respondendo"
            tone="success"
            dot="var(--color-accent-green-500)"
        />
        <x-ui.stat
            label="Excluídos"
            :value="$stats['deleted']"
            hint="soft-delete, dados preservados"
            tone="danger"
            dot="var(--color-semantic-danger)"
        />
    </x-ui.stats>

    <div class="ui-toolbar">
        <div class="ui-toolbar__grow">
            <x-ui.input
                wire:model.live.debounce.300ms="q"
                icon="magnifying-glass"
                placeholder="Buscar por nome, slug ou domínio…"
                aria-label="Buscar inquilinos"
            />
        </div>
        <x-ui.checkbox wire:model.live="incluirExcluidos" label="Incluir excluídos" />
        @if ($tenants->total() > 0)
            <span class="ui-toolbar__count">{{ $tenants->firstItem() }}–{{ $tenants->lastItem() }} de {{ $tenants->total() }}</span>
        @else
            <span class="ui-toolbar__count">0 inquilinos</span>
        @endif
    </div>

    <x-ui.table>
        <x-slot:head>
            <tr>
                <th class="w-16">#</th>
                <th>Inquilino</th>
                <th>Slug</th>
                <th>Domínio</th>
                <th>Status</th>
                <th class="is-end">Ações</th>
            </tr>
        </x-slot:head>

        @forelse ($tenants as $tenant)
            @php
                $palette = ['#0B2E5C', '#1E40AF', '#047857', '#334155', '#155E75', '#3730A3'];
                $color = $palette[abs(crc32($tenant->slug)) % count($palette)];
                $rowNumber = ($tenants->firstItem() ?? 0) + $loop->index;
                $primaryDomain = $tenant->domains->first();
                $extraDomains = max(0, ($tenant->domains_count ?? 0) - 1);
            @endphp
            <tr wire:key="tenant-{{ $tenant->id }}" @class(['is-muted' => $tenant->deleted_at])>
                <td><span class="ui-meta">#{{ str_pad((string) $rowNumber, 3, '0', STR_PAD_LEFT) }}</span></td>
                <td>
                    <div class="ui-identity">
                        <span class="ui-avatar" style="background: {{ $color }};" aria-hidden="true">{{ mb_strtoupper(mb_substr($tenant->name, 0, 1)) }}</span>
                        <div>
                            <a href="{{ route('tenants.show', $tenant) }}" class="ui-link-strong" wire:navigate>{{ $tenant->name }}</a>
                            <div class="ui-meta">{{ optional($tenant->created_at)->format('d/m/Y') ?? '—' }}</div>
                        </div>
                    </div>
                </td>
                <td><code class="ui-code">{{ $tenant->slug }}</code></td>
                <td>
                    @if ($primaryDomain)
                        <a href="{{ $primaryDomain->url() }}" target="_blank" rel="noopener noreferrer"
                           class="t-mono ui-link-strong inline-flex items-center gap-1"
                           title="Abrir {{ $primaryDomain->domain }}">
                            {{ $primaryDomain->domain }}
                            <flux:icon name="arrow-top-right-on-square" variant="mini" class="size-3.5" />
                        </a>
                        @if ($extraDomains > 0)
                            <span class="ui-meta">+{{ $extraDomains }}</span>
                        @endif
                    @else
                        <span class="ui-meta">sem domínio</span>
                    @endif
                </td>
                <td>
                    @php($statusValue = $tenant->status?->value ?? 'active')
                    @if ($tenant->deleted_at)
                        <x-ui.badge color="red">Excluído</x-ui.badge>
                    @elseif ($statusValue === 'active')
                        <x-ui.badge color="green">Ativo</x-ui.badge>
                    @elseif ($statusValue === 'pending')
                        <x-ui.badge color="blue">Provisionando</x-ui.badge>
                    @elseif ($statusValue === 'failed')
                        <x-ui.badge color="red">Falhou</x-ui.badge>
                    @else
                        <x-ui.badge color="amber">Inativo</x-ui.badge>
                    @endif
                </td>
                <td class="is-end">
                    <div class="ui-row-actions">
                        @if ($tenant->deleted_at)
                            <form method="POST" action="{{ route('tenants.restore', $tenant) }}">
                                @csrf
                                <flux:button type="submit" variant="ghost" size="sm" icon="arrow-path">Restaurar</flux:button>
                            </form>
                        @else
                            <flux:button :href="route('tenants.edit', $tenant)" variant="ghost" size="sm" icon="pencil-square" wire:navigate>Editar</flux:button>
                            <form method="POST" action="{{ route('tenants.destroy', $tenant) }}"
                                  onsubmit="return confirm('Excluir o inquilino “{{ $tenant->name }}”? O banco de dados é preservado e pode ser restaurado.');">
                                @csrf @method('DELETE')
                                <flux:button type="submit" variant="ghost" size="sm" icon="trash" class="text-red-600 hover:text-red-700" :aria-label="'Excluir '.$tenant->name">Excluir</flux:button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6">
                    <x-ui.empty icon="building-office-2" title="Nenhum inquilino encontrado.">
                        @if ($q !== '')
                            Ajuste o termo de busca “{{ $q }}” ou limpe o filtro.
                        @else
                            Cadastre o primeiro inquilino para começar.
                        @endif
                    </x-ui.empty>
                </td>
            </tr>
        @endforelse
    </x-ui.table>

    @if ($tenants->hasPages())
        <div class="mt-6">{{ $tenants->links() }}</div>
    @endif
</div>
