<div>
    <div class="mb-6 grid gap-4 md:grid-cols-3">
        <div class="border border-gm-gray-line bg-white p-5">
            <div class="gm-meta">Abonnés confirmés</div>
            <div class="mt-2 font-slab text-2xl font-bold italic text-gm-ink">{{ $this->kpis['subscribers'] }}</div>
        </div>
        <div class="border border-gm-gray-line bg-white p-5">
            <div class="gm-meta">Campagnes envoyées</div>
            <div class="mt-2 font-slab text-2xl font-bold italic text-gm-ink">{{ $this->kpis['campaigns_sent'] }}</div>
        </div>
        <div class="border border-gm-gray-line bg-white p-5">
            <div class="gm-meta">Brouillons</div>
            <div class="mt-2 font-slab text-2xl font-bold italic text-gm-charcoal-2">{{ $this->kpis['drafts'] }}</div>
        </div>
    </div>

    <div class="mb-6 grid gap-4 md:grid-cols-3">
        @foreach ($this->newsletters as $list)
            <div class="border border-gm-gray-line bg-white p-4">
                <div class="gm-meta">{{ $list->code }}</div>
                <div class="mt-1 font-slab text-lg font-bold italic text-gm-ink">{{ $list->name }}</div>
                <div class="gm-meta mt-2 normal-case tracking-normal">{{ $list->description }}</div>
                <div class="mt-3 text-gm-red font-bold">{{ $list->confirmed_subscriptions_count }} abonnés confirmés</div>
            </div>
        @endforeach
    </div>

    <div class="mb-6 flex flex-wrap items-center gap-3">
        <div class="relative flex-1 min-w-[260px]">
            <input
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="Rechercher par objet…"
                class="w-full border border-gm-gray-line bg-white px-4 py-2.5 font-sans text-sm focus:border-gm-red focus:outline-none"
            />
        </div>

        <div class="flex items-center gap-1 border border-gm-gray-line bg-white p-1 font-mono text-[11px] uppercase tracking-[0.1em]">
            @foreach (['all' => 'Toutes', 'draft' => 'Brouillon', 'scheduled' => 'Planifié', 'sending' => 'En cours', 'sent' => 'Envoyé', 'failed' => 'Échec'] as $key => $label)
                <button wire:click="$set('status', '{{ $key }}')" class="px-3 py-1.5 transition-colors {{ $status === $key ? 'bg-gm-ink text-white' : 'text-gm-charcoal hover:bg-gm-paper' }}">{{ $label }}</button>
            @endforeach
        </div>

        <div class="ml-auto">
            <a href="{{ route('admin.newsletters.create') }}" class="gm-btn-primary">Nouvelle campagne</a>
        </div>
    </div>

    <div class="border border-gm-gray-line bg-white">
        <table class="w-full">
            <thead class="border-b border-gm-gray-line bg-gm-paper">
                <tr class="gm-meta text-left">
                    <th class="px-6 py-3">Objet</th>
                    <th class="px-6 py-3">Liste</th>
                    <th class="px-6 py-3">Statut</th>
                    <th class="px-6 py-3 text-right">Envoyés</th>
                    <th class="px-6 py-3">Date</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gm-gray-line">
                @forelse ($campaigns as $campaign)
                    <tr class="hover:bg-gm-paper">
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.newsletters.edit', $campaign) }}" class="font-slab font-bold italic text-gm-ink hover:text-gm-red">
                                {{ $campaign->subject }}
                            </a>
                            @if ($campaign->preheader)
                                <div class="gm-meta normal-case tracking-normal mt-1">{{ Str::limit($campaign->preheader, 80) }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gm-charcoal">{{ $campaign->newsletter->name }}</td>
                        <td class="px-6 py-4">
                            <span class="gm-meta border border-gm-gray-line bg-gm-cream px-2 py-0.5">{{ ucfirst($campaign->status) }}</span>
                        </td>
                        <td class="px-6 py-4 text-right font-mono text-sm">
                            {{ $campaign->sent_count }} / {{ $campaign->recipients_count }}
                        </td>
                        <td class="gm-meta px-6 py-4">
                            {{ $campaign->sent_at?->locale('fr')->translatedFormat('j M Y H:i') ?? '—' }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('admin.newsletters.edit', $campaign) }}" class="gm-meta hover:text-gm-red">Éditer</a>
                            @if ($campaign->isDraft())
                                <span class="gm-meta mx-2">·</span>
                                <button wire:click="send({{ $campaign->id }})" wire:confirm="Lancer l'envoi maintenant ?" class="gm-meta hover:text-gm-red">Envoyer</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center">
                            <p class="gm-meta">Aucune campagne.</p>
                            <a href="{{ route('admin.newsletters.create') }}" class="gm-btn-primary mt-4 inline-block">Créer la première campagne</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($campaigns->hasPages())
        <div class="mt-6">{{ $campaigns->links() }}</div>
    @endif
</div>
