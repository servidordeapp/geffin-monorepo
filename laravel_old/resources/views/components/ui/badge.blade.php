@props([
    'color' => 'slate',
    'dot' => true,
])

@php
    $variantClass = match ($color) {
        'green', 'success' => 'pill-paid',
        'amber', 'warning' => 'pill-pending',
        'red', 'danger' => 'pill-overdue',
        'blue', 'info' => 'pill-info',
        default => 'pill-draft',
    };
@endphp

<span {{ $attributes->class(['pill', $variantClass]) }}>
    @if ($dot)
        <span class="dot"></span>
    @endif
    {{ $slot }}
</span>
