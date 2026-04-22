<div>
    <div class="mb-6 flex flex-wrap items-center gap-3">
        <div class="relative flex-1 min-w-[260px]">
            <input
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="Rechercher (titre, numéro, thème)…"
                class="w-full border border-gm-gray-line bg-white px-4 py-2.5 font-sans text-sm focus:border-gm-red focus:outline-none"
            />
        </div>
        <div class="flex items-center gap-1 border border-gm-gray-line bg-white p-1 font-mono text-[11px] uppercase tracking-[0.1em]">
            @foreach (['all' => 'Tous', 'draft' => 'Brouillon', 'scheduled' => 'Planifié', 'published' => 'Publié', 'archived' => 'Archivé'] as $key => $label)
                <button wire:click="$set('status', '{{ $key }}')" class="px-3 py-1.5 transition-colors {{ $status === $key ? 'bg-gm-ink text-white' : 'text-gm-charcoal hover:bg-gm-paper' }}">{{ $label }}</button>
            @endforeach
        </div>
        <div class="ml-auto">
            <a href="{{ route('admin.issues.create') }}" class="gm-btn-primary">Nouveau numéro</a>
        </div>
    </div>

    @if ($issues->isEmpty())
        <div class="border border-dashed border-gm-gray-line bg-gm-cream p-10 text-center">
            <p class="gm-meta">Aucun numéro enregistré.</p>
            <a href="{{ route('admin.issues.create') }}" class="gm-btn-primary mt-4 inline-block">Créer le premier numéro</a>
        </div>
    @else
        <div class="grid gap-6 md:grid-cols-3 lg:grid-cols-4">
            @foreach ($issues as $issue)
                <div class="border border-gm-gray-line bg-white">
                    <a href="{{ route('admin.issues.edit', $issue) }}" class="block aspect-[3/4] overflow-hidden bg-gm-cream">
                        @if ($issue->cover)
                            <img src="{{ $issue->cover->url() }}" alt="" class="h-full w-full object-cover" />
                        @else
                            <div class="flex h-full items-center justify-center">
                                <span class="font-slab text-6xl italic text-gm-red">#{{ $issue->number }}</span>
                            </div>
                        @endif
                    </a>
                    <div class="p-4">
                        <div class="gm-meta">N° {{ $issue->number }} · {{ $issue->publication_date?->locale('fr')->translatedFormat('M Y') }}</div>
                        <h3 class="mt-1 font-slab text-lg font-bold italic text-gm-ink">
                            <a href="{{ route('admin.issues.edit', $issue) }}" class="hover:text-gm-red">{{ $issue->title }}</a>
                        </h3>
                        <div class="mt-3 flex items-center justify-between">
                            <span class="gm-meta inline-block border border-gm-gray-line px-2 py-0.5">{{ ucfirst($issue->status) }}</span>
                            @if ($issue->hasPdf())
                                <span class="gm-meta text-gm-red">PDF ✓</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($issues->hasPages())
            <div class="mt-6">{{ $issues->links() }}</div>
        @endif
    @endif
</div>
