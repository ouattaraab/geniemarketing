<div>
    {{-- KPIs --}}
    <div class="mb-6 grid gap-4 md:grid-cols-4">
        <div class="border border-gm-gray-line bg-white p-5">
            <div class="gm-meta">Médias au total</div>
            <div class="mt-2 font-slab text-2xl font-bold italic text-gm-ink">{{ $this->kpis['total'] }}</div>
        </div>
        <div class="border border-gm-gray-line bg-white p-5">
            <div class="gm-meta">Images</div>
            <div class="mt-2 font-slab text-2xl font-bold italic text-gm-ink">{{ $this->kpis['images'] }}</div>
        </div>
        <div class="border border-gm-gray-line bg-white p-5">
            <div class="gm-meta">PDFs</div>
            <div class="mt-2 font-slab text-2xl font-bold italic text-gm-ink">{{ $this->kpis['pdfs'] }}</div>
        </div>
        <div class="border border-gm-gray-line bg-white p-5">
            <div class="gm-meta">Espace occupé</div>
            <div class="mt-2 font-slab text-2xl font-bold italic text-gm-ink">{{ $this->kpis['total_size_mb'] }} <span class="gm-meta">MB</span></div>
        </div>
    </div>

    {{-- Upload zone --}}
    <div class="mb-6 border-2 border-dashed border-gm-gray-line bg-white">
        <label class="flex cursor-pointer items-center justify-between gap-4 px-6 py-6 transition hover:border-gm-red hover:bg-gm-paper">
            <div>
                <div class="font-slab text-lg font-bold italic text-gm-ink">Importer des médias</div>
                <p class="gm-meta normal-case tracking-normal mt-1">
                    Glisser-déposer ou cliquer · JPG/PNG/GIF/WEBP/PDF/MP4 · 16 Mo max · upload multiple supporté
                </p>
            </div>
            <div class="flex items-center gap-3">
                <span wire:loading wire:target="uploads" class="gm-meta text-gm-red">Upload…</span>
                <span wire:loading.remove wire:target="uploads" class="gm-btn-primary">Parcourir</span>
            </div>
            <input type="file" wire:model="uploads" multiple class="hidden" />
        </label>
        @error('uploads.*')<p class="gm-meta px-6 pb-3 text-gm-red">{{ $message }}</p>@enderror
    </div>

    {{-- Filtres --}}
    <div class="mb-6 flex flex-wrap items-center gap-3">
        <div class="relative flex-1 min-w-[260px]">
            <input
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="Rechercher par alt, légende, crédit…"
                class="w-full border border-gm-gray-line bg-white px-4 py-2.5 font-sans text-sm focus:border-gm-red focus:outline-none"
            />
        </div>

        <div class="flex items-center gap-1 border border-gm-gray-line bg-white p-1 font-mono text-[11px] uppercase tracking-[0.1em]">
            @foreach (['all' => 'Tous', 'image' => 'Images', 'video' => 'Vidéos', 'pdf' => 'PDFs', 'audio' => 'Audio'] as $key => $label)
                <button wire:click="$set('type', '{{ $key }}')" class="px-3 py-1.5 transition-colors {{ $type === $key ? 'bg-gm-ink text-white' : 'text-gm-charcoal hover:bg-gm-paper' }}">{{ $label }}</button>
            @endforeach
        </div>
    </div>

    {{-- Grille --}}
    @if ($items->isEmpty())
        <div class="border border-dashed border-gm-gray-line bg-gm-cream p-10 text-center">
            <p class="gm-meta">Aucun média ne correspond à vos filtres.</p>
        </div>
    @else
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
            @foreach ($items as $m)
                <div class="border border-gm-gray-line bg-white group">
                    <div class="relative aspect-square bg-gm-cream overflow-hidden">
                        @if ($m->type === \App\Enums\MediaType::Image)
                            <img src="{{ $m->url() }}" alt="{{ $m->alt }}" class="h-full w-full object-cover" loading="lazy" />
                        @elseif ($m->type === \App\Enums\MediaType::Pdf)
                            <div class="flex h-full items-center justify-center font-slab text-4xl italic text-gm-red">PDF</div>
                        @elseif ($m->type === \App\Enums\MediaType::Video)
                            <div class="flex h-full items-center justify-center font-slab text-4xl italic text-gm-ink">▶</div>
                        @else
                            <div class="flex h-full items-center justify-center font-slab text-4xl italic text-gm-gray">?</div>
                        @endif

                        <div class="absolute inset-x-0 bottom-0 flex gap-2 bg-gm-ink/80 p-2 opacity-0 transition-opacity group-hover:opacity-100">
                            <button wire:click="startEdit({{ $m->id }})" class="flex-1 bg-white px-2 py-1 font-mono text-[10px] font-bold uppercase tracking-[0.1em] text-gm-ink hover:text-gm-red">Éditer</button>
                            <button wire:click="deleteMedia({{ $m->id }})" wire:confirm="Supprimer ce média ?" class="flex-1 bg-gm-red px-2 py-1 font-mono text-[10px] font-bold uppercase tracking-[0.1em] text-white hover:bg-gm-red-bright">Suppr.</button>
                        </div>
                    </div>
                    <div class="p-2">
                        <div class="gm-meta truncate" title="{{ $m->original_filename }}">{{ $m->original_filename }}</div>
                        @if ($m->width && $m->height)
                            <div class="gm-meta normal-case tracking-normal text-[10px]">{{ $m->width }}×{{ $m->height }} · {{ round($m->size_bytes / 1024) }} KB</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if ($items->hasPages())
        <div class="mt-6">{{ $items->links() }}</div>
    @endif

    {{-- Modale d'édition --}}
    @if ($editingMediaId)
        @php $mediaBeingEdited = \App\Models\Media::find($editingMediaId); @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gm-ink/80 px-4" wire:click.self="cancelEdit">
            <div class="w-full max-w-2xl border border-gm-gray-line bg-white">
                <div class="flex items-center justify-between border-b border-gm-gray-line bg-gm-paper px-6 py-4">
                    <h3 class="font-slab text-xl font-bold italic text-gm-ink">Éditer les métadonnées</h3>
                    <button wire:click="cancelEdit" class="gm-meta hover:text-gm-red">Fermer ✕</button>
                </div>

                <div class="grid gap-0 md:grid-cols-[1fr_2fr]">
                    <div class="bg-gm-cream p-4">
                        @if ($mediaBeingEdited && $mediaBeingEdited->type === \App\Enums\MediaType::Image)
                            <img src="{{ $mediaBeingEdited->url() }}" alt="" class="w-full border border-gm-gray-line" />
                        @endif
                    </div>

                    <div class="p-6 space-y-4">
                        <div>
                            <label class="gm-meta mb-2 block">Texte alternatif (alt) <span class="text-gm-red">*</span></label>
                            <input type="text" wire:model="editAlt" maxlength="255" class="w-full border border-gm-gray-line px-3 py-2 font-sans text-sm focus:border-gm-red focus:ring-0" placeholder="Décrit l'image pour l'accessibilité" />
                            @error('editAlt')<p class="gm-meta mt-1 text-gm-red">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="gm-meta mb-2 block">Légende</label>
                            <input type="text" wire:model="editCaption" maxlength="500" class="w-full border border-gm-gray-line px-3 py-2 font-sans text-sm focus:border-gm-red focus:ring-0" />
                        </div>
                        <div>
                            <label class="gm-meta mb-2 block">Crédit photo</label>
                            <input type="text" wire:model="editCredit" maxlength="255" class="w-full border border-gm-gray-line px-3 py-2 font-sans text-sm focus:border-gm-red focus:ring-0" placeholder="© Agence · DR · Nom du photographe" />
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 border-t border-gm-gray-line bg-gm-paper px-6 py-4">
                    <button wire:click="cancelEdit" class="gm-meta hover:text-gm-red">Annuler</button>
                    <button wire:click="saveEdit" class="gm-btn-primary">Enregistrer</button>
                </div>
            </div>
        </div>
    @endif
</div>
