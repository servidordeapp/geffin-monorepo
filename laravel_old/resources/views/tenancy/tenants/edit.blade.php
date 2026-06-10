<div class="ui-form">
    <x-ui.page-header
        overline="Gestão do sistema · Edição"
        title="Editar inquilino"
        :subtitle="'Alterando '.$tenant->name.' — o slug e o banco permanecem intactos.'"
    />

    <form wire:submit="save" class="ui-form-card">
        <div class="ui-form-card__body">
            <x-ui.input
                name="name"
                label="Nome"
                wire:model="name"
                autofocus
            />
            <x-ui.locked label="Slug" hint="O slug não pode ser alterado após o provisionamento.">
                {{ $tenant->slug }}
            </x-ui.locked>
        </div>

        <div class="ui-form-card__foot">
            <flux:button :href="route('tenants.show', $tenant)" variant="ghost" wire:navigate>Cancelar</flux:button>
            <flux:button type="submit" variant="primary" :loading="false" wire:loading.attr="disabled" wire:target="save">
                <span wire:loading.remove wire:target="save">Salvar alterações</span>
                <span wire:loading wire:target="save">Salvando…</span>
            </flux:button>
        </div>
    </form>
</div>
