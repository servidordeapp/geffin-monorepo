{{-- Single source of truth lives in the <x-layouts.app> component. This view exists
     only so Livewire's `component_layout` (layouts::app) renders the same shell. --}}
<x-layouts.app :title="$title ?? null">
    {{ $slot }}
</x-layouts.app>
