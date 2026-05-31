@props([
    'label' => null,
])

<section {{ $attributes->class('ui-stats') }} @if ($label) aria-label="{{ $label }}" @endif>
    {{ $slot }}
</section>
