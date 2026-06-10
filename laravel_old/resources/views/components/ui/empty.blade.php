@props([
    'icon' => 'inbox',
    'title' => null,
])

<div class="ui-empty">
    <flux:icon :name="$icon" variant="outline" />
    @if ($title)
        <p class="ui-empty__title">{{ $title }}</p>
    @endif
    @if (trim($slot) !== '')
        <p class="ui-empty__hint">{{ $slot }}</p>
    @endif
</div>
