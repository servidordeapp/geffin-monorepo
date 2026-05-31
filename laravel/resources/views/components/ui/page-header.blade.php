@props([
    'title',
    'overline' => null,
    'subtitle' => null,
])

<header {{ $attributes->class('ui-page-head') }}>
    <div class="ui-page-head__main">
        @if ($overline)
            <p class="ui-overline">{{ $overline }}</p>
        @endif
        <h1 class="ui-page-title">
            {{ $title }}
            {{ $badge ?? '' }}
        </h1>
        @if ($subtitle)
            <p class="ui-page-sub">{{ $subtitle }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="ui-page-head__actions">{{ $actions }}</div>
    @endisset
</header>
