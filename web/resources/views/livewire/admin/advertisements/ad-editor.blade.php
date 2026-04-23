<div>
    <form wire:submit="save" class="space-y-6">
        <div class="border border-gm-gray-line bg-white p-6 sm:p-8">
            <h3 class="font-slab text-xl font-bold italic text-gm-ink">Identification</h3>
            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="title" class="gm-meta block mb-2">Titre interne <span class="text-gm-red">*</span></label>
                    <input id="title" type="text" wire:model="title"
                           class="w-full border border-gm-gray-line bg-white px-4 py-3 text-gm-ink focus:border-gm-red focus:outline-none focus:ring-0"
                           placeholder="Ex. Campagne Orange Money — mai 2026" />
                    @error('title')<p class="gm-meta mt-2 text-gm-red">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="placement" class="gm-meta block mb-2">Emplacement <span class="text-gm-red">*</span></label>
                    <select id="placement" wire:model="placement"
                            class="w-full border border-gm-gray-line bg-white px-4 py-3 text-gm-ink focus:border-gm-red focus:outline-none focus:ring-0">
                        <option value="article_top">Article — haut</option>
                        <option value="article_bottom">Article — bas</option>
                        <option value="article_sidebar">Article — colonne</option>
                        <option value="home_leaderboard">Accueil — bandeau</option>
                        <option value="home_sidebar">Accueil — colonne</option>
                    </select>
                    @error('placement')<p class="gm-meta mt-2 text-gm-red">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="mt-5">
                <label for="sponsorName" class="gm-meta block mb-2">Sponsor (optionnel)</label>
                <input id="sponsorName" type="text" wire:model="sponsorName"
                       class="w-full border border-gm-gray-line bg-white px-4 py-3 text-gm-ink focus:border-gm-red focus:outline-none focus:ring-0"
                       placeholder="Ex. Orange Côte d'Ivoire" />
                <p class="gm-meta mt-1 normal-case tracking-normal text-gm-gray">Affiché avec la mention « Sponsorisé par… » pour transparence éditoriale.</p>
            </div>
        </div>

        <div class="border border-gm-gray-line bg-white p-6 sm:p-8">
            <h3 class="font-slab text-xl font-bold italic text-gm-ink">Visuel</h3>
            <div class="mt-5">
                @if ($this->previewUrl)
                    <div class="mb-4">
                        <img src="{{ $this->previewUrl }}" alt="Aperçu" class="max-h-48 border border-gm-gray-line" />
                        <button type="button" wire:click="removeImage"
                                class="gm-meta mt-2 normal-case tracking-normal text-gm-red hover:text-gm-red-deep">
                            Retirer l'image
                        </button>
                    </div>
                @endif
                <label for="imageUpload" class="gm-meta block mb-2">Uploader une image</label>
                <input id="imageUpload" type="file" wire:model="imageUpload" accept="image/*"
                       class="block w-full text-sm text-gm-charcoal
                              file:mr-4 file:border-0 file:bg-gm-ink file:px-4 file:py-2 file:font-mono file:text-[11px] file:font-bold file:uppercase file:tracking-[0.15em] file:text-white
                              hover:file:bg-gm-charcoal" />
                @error('imageUpload')<p class="gm-meta mt-2 text-gm-red">{{ $message }}</p>@enderror
                <p class="gm-meta mt-1 normal-case tracking-normal text-gm-gray">PNG/JPG/WebP, ≤ 4 Mo. Conseillé : 728×90 (leaderboard), 300×250 (carré) ou 300×600 (skyscraper).</p>

                <div class="mt-5">
                    <label for="imageUrl" class="gm-meta block mb-2">Ou URL externe d'image</label>
                    <input id="imageUrl" type="url" wire:model="imageUrl"
                           class="w-full border border-gm-gray-line bg-white px-4 py-3 text-gm-ink focus:border-gm-red focus:outline-none focus:ring-0"
                           placeholder="https://cdn.exemple.ci/banniere.png" />
                    @error('imageUrl')<p class="gm-meta mt-2 text-gm-red">{{ $message }}</p>@enderror
                </div>

                <div class="mt-5">
                    <label for="altText" class="gm-meta block mb-2">Texte alternatif (alt) <span class="text-gm-red">*</span></label>
                    <input id="altText" type="text" wire:model="altText" maxlength="200"
                           class="w-full border border-gm-gray-line bg-white px-4 py-3 text-gm-ink focus:border-gm-red focus:outline-none focus:ring-0"
                           placeholder="Ex. Offre Mobile Money Orange — souscrire en ligne" />
                    @error('altText')<p class="gm-meta mt-2 text-gm-red">{{ $message }}</p>@enderror
                    <p class="gm-meta mt-1 normal-case tracking-normal text-gm-gray">Obligatoire pour l'accessibilité (RGAA) et le SEO.</p>
                </div>
            </div>
        </div>

        <div class="border border-gm-gray-line bg-white p-6 sm:p-8">
            <h3 class="font-slab text-xl font-bold italic text-gm-ink">Lien</h3>
            <div class="mt-5 space-y-5">
                <div>
                    <label for="linkUrl" class="gm-meta block mb-2">URL de destination <span class="text-gm-red">*</span></label>
                    <input id="linkUrl" type="url" wire:model="linkUrl"
                           class="w-full border border-gm-gray-line bg-white px-4 py-3 text-gm-ink focus:border-gm-red focus:outline-none focus:ring-0"
                           placeholder="https://orange.ci/offres" />
                    @error('linkUrl')<p class="gm-meta mt-2 text-gm-red">{{ $message }}</p>@enderror
                </div>
                <label class="flex items-center gap-3 text-sm text-gm-ink">
                    <input type="checkbox" wire:model="linkNewTab" class="h-4 w-4 border-gm-gray-line text-gm-red focus:ring-gm-red" />
                    <span>Ouvrir dans un nouvel onglet (recommandé)</span>
                </label>
                <label class="flex items-center gap-3 text-sm text-gm-ink">
                    <input type="checkbox" wire:model="linkNofollow" class="h-4 w-4 border-gm-gray-line text-gm-red focus:ring-gm-red" />
                    <span>Marquer le lien <code class="font-mono text-xs">rel="nofollow sponsored"</code> (recommandé pour publicité)</span>
                </label>
            </div>
        </div>

        <div class="border border-gm-gray-line bg-white p-6 sm:p-8">
            <h3 class="font-slab text-xl font-bold italic text-gm-ink">Programmation & priorité</h3>
            <div class="mt-5 grid gap-5 sm:grid-cols-3">
                <div>
                    <label for="startsAt" class="gm-meta block mb-2">Début (optionnel)</label>
                    <input id="startsAt" type="datetime-local" wire:model="startsAt"
                           class="w-full border border-gm-gray-line bg-white px-4 py-3 text-gm-ink focus:border-gm-red focus:outline-none focus:ring-0" />
                    @error('startsAt')<p class="gm-meta mt-2 text-gm-red">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="endsAt" class="gm-meta block mb-2">Fin (optionnel)</label>
                    <input id="endsAt" type="datetime-local" wire:model="endsAt"
                           class="w-full border border-gm-gray-line bg-white px-4 py-3 text-gm-ink focus:border-gm-red focus:outline-none focus:ring-0" />
                    @error('endsAt')<p class="gm-meta mt-2 text-gm-red">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="priority" class="gm-meta block mb-2">Priorité (0-100)</label>
                    <input id="priority" type="number" min="0" max="100" wire:model="priority"
                           class="w-full border border-gm-gray-line bg-white px-4 py-3 text-gm-ink focus:border-gm-red focus:outline-none focus:ring-0" />
                    @error('priority')<p class="gm-meta mt-2 text-gm-red">{{ $message }}</p>@enderror
                    <p class="gm-meta mt-1 normal-case tracking-normal text-gm-gray">Poids dans la rotation aléatoire. 10 = standard, 50 = 5× plus visible.</p>
                </div>
            </div>
            <div class="mt-5">
                <label class="flex items-center gap-3 text-sm text-gm-ink">
                    <input type="checkbox" wire:model="isActive" class="h-4 w-4 border-gm-gray-line text-gm-red focus:ring-gm-red" />
                    <span>Bannière active (sinon jamais affichée, même dans la fenêtre de programmation)</span>
                </label>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.ads.index') }}" class="gm-meta normal-case tracking-normal hover:text-gm-red" wire:navigate>
                Annuler
            </a>
            <button type="submit" class="gm-btn-primary">
                {{ $adId ? 'Enregistrer les modifications' : 'Créer la bannière' }}
            </button>
        </div>
    </form>
</div>
