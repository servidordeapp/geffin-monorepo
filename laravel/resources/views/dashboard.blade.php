<x-layouts.app title="Dashboard">
    <x-ui.page-header
        overline="Painel"
        title="Dashboard"
        :subtitle="'Bem-vindo, '.auth()->user()->name.'.'"
    />
</x-layouts.app>
