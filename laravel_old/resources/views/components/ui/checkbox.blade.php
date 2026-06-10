@props([
    'label' => null,
])

<label class="ui-check">
    <input type="checkbox" {{ $attributes }} />
    {{ $label ?? $slot }}
</label>
