<div>
    {{-- KPIs --}}
    <div class="mb-6 grid gap-4 md:grid-cols-4">
        <div class="border border-gm-gray-line bg-white p-5">
            <div class="gm-meta">Abonnements actifs</div>
            <div class="mt-2 font-slab text-2xl font-bold italic text-gm-ink">{{ $this->kpis['active'] }}</div>
        </div>
        <div class="border border-gm-gray-line bg-white p-5">
            <div class="gm-meta">En période d'essai</div>
            <div class="mt-2 font-slab text-2xl font-bold italic text-gm-red">{{ $this->kpis['trialing'] }}</div>
        </div>
        <div class="border border-gm-gray-line bg-white p-5">
            <div class="gm-meta">Expirations ≤ 30j</div>
            <div class="mt-2 font-slab text-2xl font-bold italic text-gm-charcoal-2">{{ $this->kpis['expiring_soon'] }}</div>
        </div>
        <div class="border border-gm-gray-line bg-white p-5">
            <div class="gm-meta">MRR estimé</div>
            <div class="mt-2 font-slab text-2xl font-bold italic text-gm-ink">
                {{ number_format((int) $this->kpis['mrr_cents'] / 100, 0, ',', ' ') }} <span class="gm-meta">FCFA</span>
            </div>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="mb-6 flex flex-wrap items-center gap-3">
        <div class="relative flex-1 min-w-[260px]">
            <input
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="Rechercher un abonné (email, nom)…"
                class="w-full border border-gm-gray-line bg-white px-4 py-2.5 font-sans text-sm focus:border-gm-red focus:outline-none"
            />
        </div>

        <select wire:model.live="planId" class="border border-gm-gray-line bg-white px-3 py-2.5 font-sans text-sm">
            <option value="">Toutes formules</option>
            @foreach ($this->plans as $p)
                <option value="{{ $p->id }}">{{ $p->name }}</option>
            @endforeach
        </select>

        <div class="flex items-center gap-1 border border-gm-gray-line bg-white p-1 font-mono text-[11px] uppercase tracking-[0.1em]">
            @foreach (['active' => 'Actifs', 'expired' => 'Expirés', 'cancelled' => 'Annulés'] as $key => $label)
                <button wire:click="$set('status', '{{ $key }}')" class="px-3 py-1.5 transition-colors {{ $status === $key ? 'bg-gm-ink text-white' : 'text-gm-charcoal hover:bg-gm-paper' }}">{{ $label }}</button>
            @endforeach
        </div>
    </div>

    {{-- Table --}}
    <div class="border border-gm-gray-line bg-white">
        <table class="w-full">
            <thead class="border-b border-gm-gray-line bg-gm-paper">
                <tr class="gm-meta text-left">
                    <th class="px-6 py-3">Abonné</th>
                    <th class="px-6 py-3">Formule</th>
                    <th class="px-6 py-3">Début</th>
                    <th class="px-6 py-3">Fin</th>
                    <th class="px-6 py-3 text-center">Statut</th>
                    <th class="px-6 py-3 text-center">Auto-renouv.</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gm-gray-line">
                @forelse ($subscriptions as $sub)
                    <tr class="hover:bg-gm-paper">
                        <td class="px-6 py-4">
                            <div class="font-slab text-base font-bold italic text-gm-ink">{{ $sub->user->fullName() }}</div>
                            <div class="gm-meta normal-case tracking-normal">{{ $sub->user->email }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gm-charcoal">{{ $sub->plan->name }}</td>
                        <td class="gm-meta px-6 py-4">{{ $sub->start_date->locale('fr')->translatedFormat('j M Y') }}</td>
                        <td class="px-6 py-4">
                            <div class="gm-meta">{{ $sub->end_date->locale('fr')->translatedFormat('j M Y') }}</div>
                            <div class="text-xs mt-1 {{ $sub->end_date->isPast() ? 'text-gm-red' : ($sub->end_date->diffInDays(now(), absolute: true) < 30 ? 'text-gm-red-deep' : 'text-gm-gray') }}">
                                {{ $sub->end_date->locale('fr')->diffForHumans() }}
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @switch($sub->status->value)
                                @case('active')
                                    <span class="gm-meta inline-block border border-gm-ink bg-gm-ink px-2 py-0.5 text-white">Actif</span>
                                    @break
                                @case('trialing')
                                    <span class="gm-meta inline-block border border-gm-red bg-gm-red-soft px-2 py-0.5 text-gm-red-deep">Essai</span>
                                    @break
                                @case('cancelled')
                                    <span class="gm-meta inline-block border border-gm-gray-line px-2 py-0.5 text-gm-gray">Annulé</span>
                                    @break
                                @case('expired')
                                    <span class="gm-meta inline-block border border-gm-charcoal-2 px-2 py-0.5 text-gm-charcoal-2">Expiré</span>
                                    @break
                                @case('past_due')
                                    <span class="gm-meta inline-block border border-gm-red bg-gm-red-soft px-2 py-0.5 text-gm-red">Impayé</span>
                                    @break
                            @endswitch
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if ($sub->auto_renewal)
                                <span class="gm-meta text-gm-ink">✓</span>
                            @else
                                <span class="gm-meta text-gm-gray">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center">
                            <p class="gm-meta">Aucun abonnement ne correspond aux filtres.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($subscriptions->hasPages())
        <div class="mt-6">{{ $subscriptions->links() }}</div>
    @endif
</div>
