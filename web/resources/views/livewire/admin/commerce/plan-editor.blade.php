<div>
    <form wire:submit="save" class="space-y-6">
        <div class="border border-gm-gray-line bg-white p-6 sm:p-8">
            <h3 class="font-slab text-xl font-bold italic text-gm-ink">Identification</h3>
            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="code" class="gm-meta block mb-2">Code <span class="text-gm-red">*</span></label>
                    <input id="code" type="text" wire:model="code"
                           class="w-full border border-gm-gray-line bg-white px-4 py-3 font-mono text-sm text-gm-ink focus:border-gm-red focus:outline-none focus:ring-0"
                           placeholder="digital" {{ $planId ? 'readonly' : '' }} />
                    @error('code')<p class="gm-meta mt-2 text-gm-red">{{ $message }}</p>@enderror
                    <p class="gm-meta mt-1 normal-case tracking-normal text-gm-gray">Minuscules, chiffres, tirets. Non modifiable après création.</p>
                </div>
                <div>
                    <label for="name" class="gm-meta block mb-2">Nom public <span class="text-gm-red">*</span></label>
                    <input id="name" type="text" wire:model="name"
                           class="w-full border border-gm-gray-line bg-white px-4 py-3 text-gm-ink focus:border-gm-red focus:outline-none focus:ring-0"
                           placeholder="Digital" />
                    @error('name')<p class="gm-meta mt-2 text-gm-red">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="mt-5">
                <label for="description" class="gm-meta block mb-2">Description courte</label>
                <textarea id="description" rows="2" wire:model="description"
                          class="w-full border border-gm-gray-line bg-white px-4 py-3 text-gm-ink focus:border-gm-red focus:outline-none focus:ring-0"
                          placeholder="Accès illimité aux articles premium et aux numéros PDF."></textarea>
                @error('description')<p class="gm-meta mt-2 text-gm-red">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="border border-gm-gray-line bg-white p-6 sm:p-8">
            <h3 class="font-slab text-xl font-bold italic text-gm-ink">Tarif & durée</h3>
            <div class="mt-5 grid gap-5 sm:grid-cols-4">
                <div>
                    <label for="price" class="gm-meta block mb-2">Prix <span class="text-gm-red">*</span></label>
                    <input id="price" type="number" min="0" wire:model="price"
                           class="w-full border border-gm-gray-line bg-white px-4 py-3 text-gm-ink focus:border-gm-red focus:outline-none focus:ring-0" />
                    @error('price')<p class="gm-meta mt-2 text-gm-red">{{ $message }}</p>@enderror
                    <p class="gm-meta mt-1 normal-case tracking-normal text-gm-gray">En unité principale (ex. 24000 pour 24 000 XOF).</p>
                </div>
                <div>
                    <label for="currency" class="gm-meta block mb-2">Devise</label>
                    <input id="currency" type="text" maxlength="3" wire:model="currency"
                           class="w-full border border-gm-gray-line bg-white px-4 py-3 font-mono uppercase text-gm-ink focus:border-gm-red focus:outline-none focus:ring-0" />
                    @error('currency')<p class="gm-meta mt-2 text-gm-red">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="duration_months" class="gm-meta block mb-2">Durée (mois) <span class="text-gm-red">*</span></label>
                    <input id="duration_months" type="number" min="1" max="60" wire:model="duration_months"
                           class="w-full border border-gm-gray-line bg-white px-4 py-3 text-gm-ink focus:border-gm-red focus:outline-none focus:ring-0" />
                    @error('duration_months')<p class="gm-meta mt-2 text-gm-red">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="trial_days" class="gm-meta block mb-2">Essai gratuit (jours)</label>
                    <input id="trial_days" type="number" min="0" max="365" wire:model="trial_days"
                           class="w-full border border-gm-gray-line bg-white px-4 py-3 text-gm-ink focus:border-gm-red focus:outline-none focus:ring-0" />
                    @error('trial_days')<p class="gm-meta mt-2 text-gm-red">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div class="border border-gm-gray-line bg-white p-6 sm:p-8">
            <h3 class="font-slab text-xl font-bold italic text-gm-ink">Contenu de l'offre</h3>
            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="licenses_included" class="gm-meta block mb-2">Licences incluses <span class="text-gm-red">*</span></label>
                    <input id="licenses_included" type="number" min="1" wire:model="licenses_included"
                           class="w-full border border-gm-gray-line bg-white px-4 py-3 text-gm-ink focus:border-gm-red focus:outline-none focus:ring-0" />
                    @error('licenses_included')<p class="gm-meta mt-2 text-gm-red">{{ $message }}</p>@enderror
                    <p class="gm-meta mt-1 normal-case tracking-normal text-gm-gray">1 pour une formule individuelle, 10 pour Entreprise, etc.</p>
                </div>
                <div>
                    <label for="position" class="gm-meta block mb-2">Position d'affichage</label>
                    <input id="position" type="number" min="0" wire:model="position"
                           class="w-full border border-gm-gray-line bg-white px-4 py-3 text-gm-ink focus:border-gm-red focus:outline-none focus:ring-0" />
                    @error('position')<p class="gm-meta mt-2 text-gm-red">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="mt-5">
                <label for="featuresText" class="gm-meta block mb-2">Avantages (1 par ligne)</label>
                <textarea id="featuresText" rows="6" wire:model="featuresText"
                          class="w-full border border-gm-gray-line bg-white px-4 py-3 text-gm-ink focus:border-gm-red focus:outline-none focus:ring-0"
                          placeholder="Articles premium en illimité&#10;Téléchargement des numéros PDF&#10;Newsletter premium hebdomadaire"></textarea>
                @error('featuresText')<p class="gm-meta mt-2 text-gm-red">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="border border-gm-gray-line bg-white p-6 sm:p-8">
            <h3 class="font-slab text-xl font-bold italic text-gm-ink">Visibilité</h3>
            <div class="mt-5 space-y-3">
                <label class="flex items-center gap-3 text-sm text-gm-ink">
                    <input type="checkbox" wire:model="is_active"
                           class="h-4 w-4 border-gm-gray-line text-gm-red focus:ring-gm-red" />
                    <span>Formule active (visible sur /abonnement)</span>
                </label>
                <label class="flex items-center gap-3 text-sm text-gm-ink">
                    <input type="checkbox" wire:model="is_featured"
                           class="h-4 w-4 border-gm-gray-line text-gm-red focus:ring-gm-red" />
                    <span>Mettre en avant (badge « Recommandé »)</span>
                </label>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.plans.index') }}" class="gm-meta normal-case tracking-normal hover:text-gm-red" wire:navigate>
                Annuler
            </a>
            <button type="submit" class="gm-btn-primary">
                {{ $planId ? 'Enregistrer les modifications' : 'Créer la formule' }}
            </button>
        </div>
    </form>
</div>
