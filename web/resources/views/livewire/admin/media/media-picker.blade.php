<div>
    @if ($open)
        <div
            x-data
            x-init="
                window.addEventListener('media-picker:selected', (e) => {
                    // event Livewire → event navigateur global pour le toolbar TipTap
                    window.dispatchEvent(new CustomEvent('media-picker:selected', { detail: e.detail?.[0] ?? e.detail }));
                });
            "
            class="fixed inset-0 z-50 flex items-center justify-center bg-gm-ink/80 px-4"
            wire:click.self="close"
        >
            <div class="w-full max-w-4xl border border-gm-gray-line bg-white max-h-[85vh] flex flex-col">
                <header class="flex items-center justify-between border-b border-gm-gray-line bg-gm-paper px-6 py-4">
                    <h3 class="font-slab text-xl font-bold italic text-gm-ink">Insérer une image</h3>
                    <button wire:click="close" class="gm-meta hover:text-gm-red">Fermer ✕</button>
                </header>

                <div class="border-b border-gm-gray-line p-4">
                    <input
                        type="search"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Rechercher (alt, légende, filename)…"
                        class="w-full border border-gm-gray-line bg-white px-4 py-2.5 font-sans text-sm focus:border-gm-red focus:outline-none"
                    />
                </div>

                <div class="overflow-y-auto p-4">
                    @if ($items->isEmpty())
                        <div class="p-10 text-center">
                            <p class="gm-meta">Aucune image ne correspond.</p>
                            <p class="gm-meta mt-2 normal-case tracking-normal">
                                <a href="{{ route('admin.media.index') }}" class="text-gm-red hover:underline">Importer des médias dans la bibliothèque ↗</a>
                            </p>
                        </div>
                    @else
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
                            @foreach ($items as $m)
                                <button
                                    type="button"
                                    wire:click="pick({{ $m->id }})"
                                    class="group relative aspect-square overflow-hidden border border-gm-gray-line bg-gm-cream transition hover:border-gm-red"
                                >
                                    <img src="{{ $m->url() }}" alt="{{ $m->alt }}" class="h-full w-full object-cover" loading="lazy" />
                                    <div class="absolute inset-x-0 bottom-0 bg-gm-ink/80 px-2 py-1 opacity-0 transition-opacity group-hover:opacity-100">
                                        <div class="gm-meta truncate text-white">{{ $m->alt ?: $m->original_filename }}</div>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <footer class="border-t border-gm-gray-line bg-gm-paper px-6 py-3 text-right">
                    <p class="gm-meta">
                        Les images proviennent de votre bibliothèque média. <a href="{{ route('admin.media.index') }}" class="text-gm-red hover:underline">Gérer →</a>
                    </p>
                </footer>
            </div>
        </div>
    @endif
</div>
