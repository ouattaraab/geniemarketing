<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <a href="{{ route('admin.issues.index') }}" class="gm-meta hover:text-gm-red">← Retour à la liste</a>
            <h2 class="mt-2 font-slab text-3xl font-bold italic text-gm-ink">
                {{ $issue?->exists ? 'Édition numéro #'.$number : 'Nouveau numéro' }}
            </h2>
        </div>
        <button wire:click="save" wire:loading.attr="disabled" class="gm-btn-primary">Enregistrer</button>
    </div>

    <form wire:submit="save" class="grid gap-6 lg:grid-cols-[2fr_1fr]">
        <div class="space-y-6">
            <div class="border border-gm-gray-line bg-white p-6">
                <div class="grid gap-4 md:grid-cols-[1fr_3fr]">
                    <div>
                        <label class="gm-meta mb-2 block">Numéro</label>
                        <input type="number" wire:model="number" class="w-full border border-gm-gray-line px-3 py-2 font-slab text-2xl font-bold text-gm-red focus:border-gm-red focus:ring-0" />
                        @error('number')<p class="gm-meta mt-1 text-gm-red">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="gm-meta mb-2 block">Titre</label>
                        <input type="text" wire:model.live.debounce.500ms="title" class="w-full border border-gm-gray-line px-3 py-2 font-slab text-xl italic text-gm-ink focus:border-gm-red focus:ring-0" placeholder="Mai 2026 — Spécial IA" />
                        @error('title')<p class="gm-meta mt-1 text-gm-red">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="mt-4">
                    <label class="gm-meta mb-2 block">Thème / accroche</label>
                    <textarea wire:model="theme" rows="3" class="w-full border border-gm-gray-line px-3 py-2 font-sans text-sm focus:border-gm-red focus:ring-0" placeholder="Le marketing ivoirien face à l'IA générative…"></textarea>
                </div>

                <div class="mt-4">
                    <label class="gm-meta mb-2 block">Slug (URL)</label>
                    <div class="flex items-center gap-2">
                        <span class="font-mono text-sm text-gm-gray">/magazine/</span>
                        <input type="text" wire:model="slug" class="flex-1 border border-gm-gray-line px-3 py-2 font-mono text-sm focus:border-gm-red focus:ring-0" />
                    </div>
                </div>
            </div>

            {{-- Upload PDF --}}
            <div class="border border-gm-gray-line bg-white p-6">
                <h3 class="font-slab text-lg font-bold italic text-gm-ink">PDF du numéro</h3>
                @if ($issue?->hasPdf())
                    <p class="gm-meta mt-2 normal-case tracking-normal">
                        PDF actuel : {{ round($issue->pdf_size_bytes / 1048576, 1) }} MB · {{ $issue->pdf_path }}
                    </p>
                @endif
                <label class="mt-4 block cursor-pointer border-2 border-dashed border-gm-gray-line bg-gm-paper p-6 text-center transition hover:border-gm-red">
                    <input type="file" wire:model="pdfUpload" accept="application/pdf" class="hidden" />
                    <div wire:loading wire:target="pdfUpload" class="gm-meta text-gm-red">Upload en cours…</div>
                    <div wire:loading.remove wire:target="pdfUpload">
                        <div class="font-slab text-lg italic text-gm-ink">
                            {{ $issue?->hasPdf() ? 'Remplacer le PDF' : 'Déposer le PDF' }}
                        </div>
                        <p class="gm-meta mt-1">40 Mo max</p>
                    </div>
                </label>
                @error('pdfUpload')<p class="gm-meta mt-2 text-gm-red">{{ $message }}</p>@enderror
            </div>
        </div>

        <aside class="space-y-6">
            <div class="border border-gm-gray-line bg-white p-6">
                <h3 class="font-slab text-lg font-bold italic text-gm-ink">Publication</h3>

                <label class="gm-meta mt-4 block mb-2">Date de parution</label>
                <input type="date" wire:model="publicationDate" class="w-full border border-gm-gray-line px-3 py-2 font-sans text-sm focus:border-gm-red focus:ring-0" />

                <label class="gm-meta mt-4 block mb-2">Statut</label>
                <select wire:model="status" class="w-full border border-gm-gray-line px-3 py-2 font-sans text-sm focus:border-gm-red focus:ring-0">
                    <option value="draft">Brouillon</option>
                    <option value="scheduled">Planifié</option>
                    <option value="published">Publié</option>
                    <option value="archived">Archivé</option>
                </select>
            </div>

            <div class="border border-gm-gray-line bg-white p-6">
                <h3 class="font-slab text-lg font-bold italic text-gm-ink">Prix & stock</h3>

                <label class="gm-meta mt-4 block mb-2">Prix papier (FCFA)</label>
                <input type="number" wire:model="pricePaper" min="0" class="w-full border border-gm-gray-line px-3 py-2 font-sans text-sm focus:border-gm-red focus:ring-0" placeholder="3500" />

                <label class="gm-meta mt-4 block mb-2">Stock papier</label>
                <input type="number" wire:model="stockPaper" min="0" class="w-full border border-gm-gray-line px-3 py-2 font-sans text-sm focus:border-gm-red focus:ring-0" />

                <label class="gm-meta mt-4 block mb-2">Prix PDF (FCFA)</label>
                <input type="number" wire:model="pricePdf" min="0" class="w-full border border-gm-gray-line px-3 py-2 font-sans text-sm focus:border-gm-red focus:ring-0" placeholder="2000" />
            </div>

            {{-- Cover --}}
            <div class="border border-gm-gray-line bg-white p-6">
                <h3 class="font-slab text-lg font-bold italic text-gm-ink">Couverture</h3>
                @if ($coverUpload)
                    <img src="{{ $coverUpload->temporaryUrl() }}" alt="" class="mt-3 aspect-[3/4] w-full border border-gm-gray-line object-cover" />
                @elseif ($issue?->cover)
                    <img src="{{ $issue->cover->url() }}" alt="" class="mt-3 aspect-[3/4] w-full border border-gm-gray-line object-cover" />
                @endif
                <label class="mt-3 block cursor-pointer border border-dashed border-gm-gray-line bg-gm-paper p-4 text-center transition hover:border-gm-red">
                    <input type="file" wire:model="coverUpload" accept="image/*" class="hidden" />
                    <div wire:loading wire:target="coverUpload" class="gm-meta text-gm-red">Upload…</div>
                    <div wire:loading.remove wire:target="coverUpload" class="gm-meta">
                        {{ $issue?->cover ? 'Remplacer la couverture' : 'Choisir une couverture' }}
                    </div>
                </label>
                @error('coverUpload')<p class="gm-meta mt-2 text-gm-red">{{ $message }}</p>@enderror

                @if ($coverUpload || $issue?->cover)
                    <label class="gm-meta mt-3 block mb-2">Alt couverture</label>
                    <input type="text" wire:model="coverAlt" class="w-full border border-gm-gray-line px-3 py-2 font-sans text-sm focus:border-gm-red focus:ring-0" />
                @endif
            </div>
        </aside>
    </form>
</div>
