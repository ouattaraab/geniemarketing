@props([
    'issue' => 'Édition #12 · Avril 2026',
    'live' => null,
])

<div class="bg-gm-ink text-gm-paper border-b-[3px] border-gm-red">
    <div class="mx-auto flex max-w-container items-center justify-between gap-6 px-8 py-2.5 font-mono text-[11px] uppercase tracking-[0.08em]">
        <div class="flex flex-wrap items-center gap-6">
            <span class="opacity-75">Abidjan · Côte d'Ivoire</span>
            <span class="hidden opacity-75 md:inline">{{ $issue }}</span>
            @if ($live)
                <span class="flex items-center gap-1.5 text-[#FF6B6B]">
                    <span class="relative inline-block h-1.5 w-1.5 rounded-full bg-gm-red-bright animate-gm-pulse"></span>
                    {{ $live }}
                </span>
            @endif
        </div>
        <div class="flex gap-4 opacity-75">
            <a href="#" class="hover:opacity-100">FR</a>
            <span>·</span>
            <a href="#" class="hover:opacity-100">Newsletter</a>
        </div>
    </div>
</div>
