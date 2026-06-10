@props([
    'head' => null,
])

<div {{ $attributes->class('ui-table-wrap') }}>
    <table class="ui-table">
        @isset($head)
            <thead>{{ $head }}</thead>
        @endisset
        <tbody>{{ $slot }}</tbody>
    </table>
</div>
