@props([
    'number' => null,
    'subtitle' => null,
])

<div class="gm-section-heading flex items-baseline gap-6">
    @if ($number)
        <span class="font-mono text-xs font-bold tracking-[0.15em] text-gm-red">{{ $number }}</span>
    @endif
    <h2>{{ $slot }}</h2>
    @if ($subtitle)
        <span class="gm-meta ml-auto hidden md:inline">{{ $subtitle }}</span>
    @endif
</div>
