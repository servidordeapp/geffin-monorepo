@props([
    'variant' => 'danger',
    'icon' => null,
])

@php
    $resolvedIcon = $icon ?? match ($variant) {
        'success' => 'check-circle',
        'warning', 'danger' => 'exclamation-triangle',
        default => 'information-circle',
    };
@endphp

<div {{ $attributes->class(['ui-alert', 'ui-alert--'.$variant]) }} role="alert">
    <flux:icon :name="$resolvedIcon" variant="mini" />
    <div>{{ $slot }}</div>
</div>
