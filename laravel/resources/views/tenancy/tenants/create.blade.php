<div class="ui-form">
    <x-ui.page-header
        overline="Gestão do sistema · Provisionamento"
        title="Novo inquilino"
        subtitle="Cria o registro do inquilino e enfileira o provisionamento do banco de dados isolado em segundo plano."
    />

    @if ($errorMessage)
        <x-ui.alert variant="danger" class="mb-5">{{ $errorMessage }}</x-ui.alert>
    @endif

    <form wire:submit="save" class="ui-form-card">
        <div class="ui-form-card__body">
            <x-ui.input
                name="name"
                label="Nome"
                wire:model="name"
                placeholder="Colégio Dom Bosco"
                autofocus
            />
            <x-ui.input
                name="slug"
                label="Slug"
                wire:model="slug"
                placeholder="colegio-dom-bosco"
                mono
                hint="Identificador único — usado no nome do banco e nas URLs. Não muda depois."
            />
            <x-ui.input
                name="domain"
                label="Domínio"
                wire:model="domain"
                placeholder="dombosco.geffin.app"
                mono
                hint="Domínio de acesso do inquilino."
            />
        </div>

        <div class="ui-form-card__foot">
            <flux:button :href="route('tenants.index')" variant="ghost" wire:navigate>Cancelar</flux:button>
            <flux:button type="submit" variant="primary" :loading="false" wire:loading.attr="disabled" wire:target="save">
                <span wire:loading.remove wire:target="save">Criar inquilino</span>
                <span wire:loading wire:target="save">Enfileirando…</span>
            </flux:button>
        </div>
    </form>
</div>
