@props([
    'label' => null,
    'icon' => 'lock-closed',
    'hint' => null,
])

<div class="field">
    @if ($label)
        <span class="field-label">{{ $label }}</span>
    @endif
    <div class="ui-locked">
        @if ($icon)
            <flux:icon :name="$icon" variant="mini" />
        @endif
        {{ $slot }}
    </div>
    @if ($hint)
        <p class="ui-hint">{{ $hint }}</p>
    @endif
</div>
