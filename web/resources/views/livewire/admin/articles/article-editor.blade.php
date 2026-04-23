@php
    $pageTitle = $article?->exists ? 'Éditer · '.$title : 'Nouvel article';
@endphp

<div>
    {{-- Breadcrumb + actions --}}
    <div class="mb-6 flex items-center justify-between gap-6">
        <div>
            <a href="{{ route('admin.articles.index') }}" class="gm-meta hover:text-gm-red">← Retour à la liste</a>
            <h2 class="mt-2 font-slab text-3xl font-bold italic text-gm-ink">
                {{ $article?->exists ? 'Édition d\'article' : 'Nouvel article' }}
            </h2>
            @if ($lastSavedAt)
                <p class="gm-meta mt-1">Enregistré à {{ $lastSavedAt }}</p>
            @endif
        </div>

        <div class="flex flex-wrap items-center gap-3">
            @if ($article?->exists && $article->status === \App\Enums\ArticleStatus::Published)
                <a href="{{ route('article.show', $article) }}" target="_blank" class="gm-meta hover:text-gm-red">Voir sur le site ↗</a>
            @endif
            <button wire:click="save" wire:loading.attr="disabled" class="gm-btn-primary">
                <span wire:loading.remove wire:target="save">Enregistrer</span>
                <span wire:loading wire:target="save">Enregistrement…</span>
            </button>
        </div>
    </div>

    {{-- Transitions de statut --}}
    @if ($article?->exists && ! empty($this->availableTransitions))
        <div class="mb-6 flex flex-wrap items-center gap-3 border-y border-gm-gray-line bg-white px-6 py-4">
            <span class="gm-meta">Workflow</span>
            @foreach ($this->availableTransitions as $target => $label)
                <button
                    wire:click="applyTransition('{{ $target }}')"
                    wire:loading.attr="disabled"
                    class="gm-meta border border-gm-gray-line bg-gm-paper px-3 py-1.5 transition hover:border-gm-red hover:bg-gm-red-soft hover:text-gm-red-deep"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>
    @endif

    <form wire:submit="save" class="grid gap-6 lg:grid-cols-[2fr_1fr]">
        {{-- Colonne principale --}}
        <div class="space-y-6">
            <div class="border border-gm-gray-line bg-white p-6">
                <label class="gm-meta block mb-2">Titre</label>
                <input
                    type="text"
                    wire:model.live.debounce.500ms="title"
                    class="w-full border-0 border-b border-gm-gray-line p-0 pb-2 font-slab text-3xl font-bold italic text-gm-ink focus:border-gm-red focus:ring-0"
                    placeholder="Titre accrocheur…"
                />
                @error('title')<p class="gm-meta mt-2 text-gm-red">{{ $message }}</p>@enderror

                <label class="gm-meta mt-6 block mb-2">Slug (URL)</label>
                <div class="flex items-center gap-2">
                    <span class="font-mono text-sm text-gm-gray">/articles/</span>
                    <input
                        type="text"
                        wire:model="slug"
                        class="flex-1 border border-gm-gray-line px-3 py-2 font-mono text-sm focus:border-gm-red focus:ring-0"
                    />
                </div>
                @error('slug')<p class="gm-meta mt-2 text-gm-red">{{ $message }}</p>@enderror

                <label class="gm-meta mt-6 block mb-2 flex justify-between">
                    <span>Chapô ({{ $this->ledeCount }} / 500)</span>
                    @if ($this->ledeCount > 0 && ($this->ledeCount < 150 || $this->ledeCount > 300))
                        <span class="text-gm-red">Recommandé : 150–300 caractères</span>
                    @endif
                </label>
                <textarea
                    wire:model.live.debounce.500ms="lede"
                    rows="3"
                    class="w-full border border-gm-gray-line px-3 py-2 font-sans text-base focus:border-gm-red focus:ring-0"
                    placeholder="Résumé percutant en quelques lignes…"
                ></textarea>
                @error('lede')<p class="gm-meta mt-2 text-gm-red">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="gm-meta mb-2 block">Corps de l'article</label>
                <x-gm.rich-editor model="body" placeholder="Commencez à rédiger votre article…" />
                @error('body')<p class="gm-meta mt-2 text-gm-red">{{ $message }}</p>@enderror
            </div>

            {{-- Cover --}}
            <div class="border border-gm-gray-line bg-white p-6">
                <h3 class="font-slab text-lg font-bold italic text-gm-ink">Image de couverture</h3>
                <p class="gm-meta mt-1 normal-case tracking-normal">
                    Obligatoire pour publier (US-013) · JPG/PNG/WebP · 8 Mo max · ratio 16:9 recommandé.
                </p>

                @if ($this->coverPreviewUrl)
                    <div class="mt-4 overflow-hidden border border-gm-gray-line">
                        <img src="{{ $this->coverPreviewUrl }}" alt="{{ $coverAlt }}" class="aspect-[16/9] w-full object-cover" />
                    </div>

                    <div class="mt-4 grid gap-3">
                        <div>
                            <label class="gm-meta mb-1 block">Texte alternatif (alt) <span class="text-gm-red">*</span></label>
                            <input type="text" wire:model="coverAlt" maxlength="255" class="w-full border border-gm-gray-line px-3 py-2 font-sans text-sm focus:border-gm-red focus:ring-0" placeholder="Décrit l'image pour l'accessibilité et le SEO" />
                            @error('coverAlt')<p class="gm-meta mt-1 text-gm-red">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="gm-meta mb-1 block">Légende</label>
                            <input type="text" wire:model="coverCaption" maxlength="500" class="w-full border border-gm-gray-line px-3 py-2 font-sans text-sm focus:border-gm-red focus:ring-0" />
                        </div>
                        <div>
                            <label class="gm-meta mb-1 block">Crédit photo</label>
                            <input type="text" wire:model="coverCredit" maxlength="255" class="w-full border border-gm-gray-line px-3 py-2 font-sans text-sm focus:border-gm-red focus:ring-0" placeholder="© Agence · DR · Nom du photographe" />
                        </div>
                        <button type="button" wire:click="removeCover" class="gm-meta self-start text-gm-red hover:text-gm-red-deep">Retirer l'image</button>
                    </div>
                @else
                    <label class="mt-4 block cursor-pointer border-2 border-dashed border-gm-gray-line bg-gm-paper p-8 text-center transition hover:border-gm-red">
                        <input type="file" wire:model="coverUpload" accept="image/*" class="hidden" />
                        <div wire:loading wire:target="coverUpload" class="gm-meta text-gm-red">Upload en cours…</div>
                        <div wire:loading.remove wire:target="coverUpload">
                            <div class="font-slab text-lg italic text-gm-ink">Déposer une image</div>
                            <p class="gm-meta mt-2">JPG, PNG, WebP · 8 Mo max</p>
                        </div>
                    </label>
                    @error('coverUpload')<p class="gm-meta mt-2 text-gm-red">{{ $message }}</p>@enderror
                @endif
            </div>

            <div class="border border-gm-gray-line bg-white p-6">
                <h3 class="font-slab text-lg font-bold italic text-gm-ink">Référencement (SEO)</h3>
                <p class="gm-meta mt-1 normal-case tracking-normal">
                    Optionnel — si vide, le titre et le chapô sont utilisés par défaut.
                </p>

                <label class="gm-meta mt-4 block mb-2">Titre SEO (60 car. max conseillés)</label>
                <input type="text" wire:model="metaTitle" maxlength="255" class="w-full border border-gm-gray-line px-3 py-2 font-sans text-sm focus:border-gm-red focus:ring-0" />

                <label class="gm-meta mt-4 block mb-2">Meta description (160 car. max conseillés)</label>
                <textarea wire:model="metaDescription" rows="2" maxlength="320" class="w-full border border-gm-gray-line px-3 py-2 font-sans text-sm focus:border-gm-red focus:ring-0"></textarea>
            </div>
        </div>

        {{-- Colonne latérale --}}
        <aside class="space-y-6">
            <div class="border border-gm-gray-line bg-white p-6">
                <h3 class="font-slab text-lg font-bold italic text-gm-ink">Publication</h3>

                <label class="gm-meta mt-4 block mb-2">Statut</label>
                <select wire:model="status" class="w-full border border-gm-gray-line px-3 py-2 font-sans text-sm focus:border-gm-red focus:ring-0">
                    <option value="draft">Brouillon</option>
                    <option value="review">En relecture</option>
                    <option value="scheduled">Planifié</option>
                    <option value="published">Publié</option>
                    <option value="archived">Archivé</option>
                </select>

                @if ($status === 'scheduled')
                    <label class="gm-meta mt-4 block mb-2">Date de publication</label>
                    <input type="datetime-local" wire:model="scheduledAt" class="w-full border border-gm-gray-line px-3 py-2 font-sans text-sm focus:border-gm-red focus:ring-0" />
                @endif

                <label class="gm-meta mt-4 block mb-2">Niveau d'accès</label>
                <select wire:model.live="accessLevel" class="w-full border border-gm-gray-line px-3 py-2 font-sans text-sm focus:border-gm-red focus:ring-0">
                    <option value="free">Libre — tous les visiteurs</option>
                    <option value="registered">Inscrits (compte gratuit)</option>
                    <option value="subscriber">Abonnés Digital / Combo</option>
                    <option value="premium">Premium (abonné OU achat à l'unité)</option>
                </select>

                @if ($accessLevel === 'premium')
                    <div class="mt-4 border border-gm-red-soft bg-gm-red-soft/50 p-3">
                        <label class="gm-meta block mb-2">Prix à l'unité (0 = non vendable, uniquement inclus dans l'abonnement)</label>
                        <div class="flex gap-2">
                            <input type="number" min="0" max="10000000" wire:model="price"
                                   class="flex-1 border border-gm-gray-line px-3 py-2 font-sans text-sm focus:border-gm-red focus:ring-0"
                                   placeholder="Ex. 500" />
                            <input type="text" maxlength="3" wire:model="priceCurrency"
                                   class="w-20 border border-gm-gray-line px-3 py-2 font-mono text-sm uppercase focus:border-gm-red focus:ring-0"
                                   placeholder="XOF" />
                        </div>
                        @error('price')<p class="gm-meta mt-2 text-gm-red">{{ $message }}</p>@enderror
                        <p class="gm-meta mt-2 normal-case tracking-normal text-gm-gray">
                            Si &gt; 0 : les visiteurs non abonnés pourront l'acheter à l'unité. L'article reste inclus dans l'abonnement.
                        </p>
                    </div>
                @endif

                <label class="gm-meta mt-4 block mb-2">Temps de lecture (min)</label>
                <input type="number" wire:model="readingTime" min="1" max="120" class="w-full border border-gm-gray-line px-3 py-2 font-sans text-sm focus:border-gm-red focus:ring-0" />
            </div>

            <div class="border border-gm-gray-line bg-white p-6">
                <h3 class="font-slab text-lg font-bold italic text-gm-ink">Classement</h3>

                <label class="gm-meta mt-4 block mb-2">Rubrique</label>
                <select wire:model="categoryId" class="w-full border border-gm-gray-line px-3 py-2 font-sans text-sm focus:border-gm-red focus:ring-0">
                    <option value="">Choisir…</option>
                    @foreach ($this->categories as $cat)
                        <option value="{{ $cat->id }}">
                            {{ str_repeat('— ', $cat->parent_id ? 1 : 0) }}{{ $cat->name }}
                        </option>
                    @endforeach
                </select>
                @error('categoryId')<p class="gm-meta mt-2 text-gm-red">{{ $message }}</p>@enderror

                <label class="gm-meta mt-4 block mb-2">Catégorie éditoriale</label>
                <select wire:model="editorialCategoryId" class="w-full border border-gm-gray-line px-3 py-2 font-sans text-sm focus:border-gm-red focus:ring-0">
                    <option value="">—</option>
                    @foreach ($this->editorialCategories as $ec)
                        <option value="{{ $ec->id }}">{{ $ec->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="border border-gm-gray-line bg-white p-6">
                <h3 class="font-slab text-lg font-bold italic text-gm-ink">Auteurs</h3>
                <div class="mt-3 space-y-1 max-h-40 overflow-y-auto">
                    @foreach ($this->authors as $auth)
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" wire:model="selectedAuthorIds" value="{{ $auth->id }}" class="border-gm-gray-line text-gm-red focus:ring-gm-red" />
                            <span>{{ $auth->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="border border-gm-gray-line bg-white p-6">
                <h3 class="font-slab text-lg font-bold italic text-gm-ink">Tags</h3>
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach ($this->tags as $tag)
                        @php $checked = in_array($tag->id, $selectedTagIds); @endphp
                        <button
                            type="button"
                            wire:click="toggleTag({{ $tag->id }})"
                            class="gm-meta border px-2 py-1 transition
                                {{ $checked ? 'border-gm-red bg-gm-red-soft text-gm-red-deep' : 'border-gm-gray-line text-gm-charcoal hover:border-gm-red' }}"
                        >
                            #{{ $tag->name }}
                        </button>
                    @endforeach
                </div>
            </div>
        </aside>

        {{-- Bouton submit caché pour support Entrée --}}
        <button type="submit" class="hidden">Enregistrer</button>
    </form>
</div>
