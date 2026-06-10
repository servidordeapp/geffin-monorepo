@props([
    'label',
    'value' => null,
    'mono' => false,
])

<div {{ $attributes->class('ui-detail-cell') }}>
    <div class="ui-detail-cell__k">{{ $label }}</div>
    <div @class(['ui-detail-cell__v', 'mono' => $mono])>{{ $value ?? $slot }}</div>
</div>
