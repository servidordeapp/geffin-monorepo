@props([
    'label',
    'value',
    'hint' => null,
    'tone' => null,
    'dot' => null,
])

@php
    $toneClass = match ($tone) {
        'success' => 'ui-stat--success',
        'danger' => 'ui-stat--danger',
        default => '',
    };
@endphp

<div {{ $attributes->class(['ui-stat', $toneClass]) }}>
    <div class="ui-stat__label">
        @if ($dot)
            <span class="ui-dot" style="color: {{ $dot }};"></span>
        @endif
        {{ $label }}
    </div>
    <div class="ui-stat__value">{{ $value }}</div>
    @if ($hint)
        <div class="ui-stat__hint">{{ $hint }}</div>
    @endif
</div>
