@props([
    'name' => null,
    'label' => null,
    'type' => 'text',
    'hint' => null,
    'icon' => null,
    'mono' => false,
])

@php
    $hasError = $name ? $errors->has($name) : false;
    $inputId = $name ? 'f-'.$name : null;
    $hasLabel = filled((string) $label);
@endphp

<div class="field">
    @if ($hasLabel)
        <label class="field-label" @if ($inputId) for="{{ $inputId }}" @endif>{{ $label }}</label>
    @endif

    <div @class(['ui-input-affix' => (bool) $icon])>
        @if ($icon)
            <flux:icon :name="$icon" class="ui-input-icon" />
        @endif
        <input
            type="{{ $type }}"
            @if ($inputId) id="{{ $inputId }}" @endif
            {{ $attributes->class(['input', 'w-full', 'is-mono' => $mono, 'error' => $hasError]) }}
        />
    </div>

    @if ($hasError)
        <p class="ui-hint ui-hint--error">{{ $errors->first($name) }}</p>
    @elseif ($hint)
        <p class="ui-hint">{{ $hint }}</p>
    @endif
</div>
