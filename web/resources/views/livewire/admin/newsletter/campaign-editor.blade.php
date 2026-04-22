<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <a href="{{ route('admin.newsletters.index') }}" class="gm-meta hover:text-gm-red">← Campagnes</a>
            <h2 class="mt-2 font-slab text-3xl font-bold italic text-gm-ink">
                {{ $campaign?->exists ? 'Édition de campagne' : 'Nouvelle campagne' }}
            </h2>
            @if ($campaign?->exists && $campaign->status !== 'draft')
                <span class="gm-meta mt-1 inline-block border border-gm-gray-line bg-gm-cream px-2 py-0.5">
                    {{ ucfirst($campaign->status) }} · {{ $campaign->sent_count }}/{{ $campaign->recipients_count }} destinataires
                </span>
            @endif
        </div>
        <div class="flex items-center gap-3">
            <button wire:click="saveAndGo" class="gm-meta border border-gm-gray-line bg-white px-4 py-2 hover:border-gm-red hover:text-gm-red">Enregistrer brouillon</button>
            @if (! $campaign?->exists || $campaign->isDraft())
                <button wire:click="sendNow" wire:confirm="Envoyer cette campagne à tous les abonnés confirmés de la liste ?" class="gm-btn-primary">Envoyer maintenant</button>
            @endif
        </div>
    </div>

    <form wire:submit.prevent class="grid gap-6 lg:grid-cols-[2fr_1fr]">
        <div class="space-y-6">
            <div class="border border-gm-gray-line bg-white p-6">
                <label class="gm-meta mb-2 block">Objet du mail</label>
                <input type="text" wire:model="subject" maxlength="200" class="w-full border-0 border-b border-gm-gray-line p-0 pb-2 font-slab text-2xl font-bold italic text-gm-ink focus:border-gm-red focus:ring-0" placeholder="Ce qui a fait l'actu du marketing ivoirien cette semaine" />
                @error('subject')<p class="gm-meta mt-2 text-gm-red">{{ $message }}</p>@enderror

                <label class="gm-meta mt-4 block mb-2">Preheader (prévisualisation client mail)</label>
                <input type="text" wire:model="preheader" maxlength="255" class="w-full border border-gm-gray-line px-3 py-2 font-sans text-sm focus:border-gm-red focus:ring-0" placeholder="Une phrase qui donne envie d'ouvrir l'email" />
            </div>

            <div class="border border-gm-gray-line bg-white p-6">
                <label class="gm-meta mb-2 block">Corps du message (Markdown supporté)</label>
                <textarea wire:model="content" rows="18" class="w-full border border-gm-gray-line px-3 py-2 font-slab text-base leading-relaxed focus:border-gm-red focus:ring-0" placeholder="## Titre h2&#10;&#10;Paragraphe avec **gras** et [liens](https://…).&#10;&#10;- Puce 1&#10;- Puce 2"></textarea>
                @error('content')<p class="gm-meta mt-2 text-gm-red">{{ $message }}</p>@enderror
            </div>
        </div>

        <aside class="space-y-6">
            <div class="border border-gm-gray-line bg-white p-6">
                <h3 class="font-slab text-lg font-bold italic text-gm-ink">Diffusion</h3>
                <label class="gm-meta mt-4 block mb-2">Liste de diffusion</label>
                <select wire:model="newsletterId" class="w-full border border-gm-gray-line px-3 py-2 font-sans text-sm focus:border-gm-red focus:ring-0">
                    @foreach ($this->newsletters as $nl)
                        <option value="{{ $nl->id }}">{{ $nl->name }} ({{ $nl->code }})</option>
                    @endforeach
                </select>
                @error('newsletterId')<p class="gm-meta mt-2 text-gm-red">{{ $message }}</p>@enderror
            </div>

            <div class="border border-gm-gray-line bg-white p-6">
                <h3 class="font-slab text-lg font-bold italic text-gm-ink">Bouton d'appel à l'action (optionnel)</h3>
                <label class="gm-meta mt-4 block mb-2">Libellé</label>
                <input type="text" wire:model="ctaLabel" maxlength="60" class="w-full border border-gm-gray-line px-3 py-2 font-sans text-sm focus:border-gm-red focus:ring-0" placeholder="Lire sur le site" />

                <label class="gm-meta mt-4 block mb-2">URL cible</label>
                <input type="url" wire:model="ctaUrl" maxlength="500" class="w-full border border-gm-gray-line px-3 py-2 font-sans text-sm focus:border-gm-red focus:ring-0" placeholder="https://geniemag.ci/articles/…" />
                @error('ctaUrl')<p class="gm-meta mt-2 text-gm-red">{{ $message }}</p>@enderror
            </div>
        </aside>
    </form>
</div>
