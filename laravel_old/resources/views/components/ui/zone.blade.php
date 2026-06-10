@props([
    'variant' => 'info',
    'title' => null,
    'description' => null,
])

<div {{ $attributes->class(['ui-zone', 'ui-zone--'.$variant]) }}>
    <div>
        @if ($title)
            <div class="ui-zone__title">{{ $title }}</div>
        @endif
        @if ($description)
            <div class="ui-zone__sub">{{ $description }}</div>
        @endif
        {{ $slot }}
    </div>
    @isset($action)
        <div>{{ $action }}</div>
    @endisset
</div>
