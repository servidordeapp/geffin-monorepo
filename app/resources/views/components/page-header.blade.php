@props(['eyebrow' => null, 'heading', 'subtitle' => null])

<div {{ $attributes->class(['flex flex-wrap items-end justify-between gap-4']) }}>
    <div>
        @if ($eyebrow)
            <div class="font-mono text-[11px] uppercase tracking-[0.22em] text-primary">{{ $eyebrow }}</div>
        @endif
        <h1 class="mt-2.5 font-display text-[2rem] font-bold leading-tight tracking-tight text-base-content">
            {{ $heading }}
        </h1>
        @if ($subtitle)
            <p class="mt-2.5 text-[0.9375rem] leading-relaxed text-base-content/70">{{ $subtitle }}</p>
        @endif
    </div>

    @if ($slot->isNotEmpty())
        <div class="flex items-center gap-2">
            {{ $slot }}
        </div>
    @endif
</div>
