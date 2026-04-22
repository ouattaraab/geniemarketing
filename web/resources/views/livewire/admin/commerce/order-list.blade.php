<div>
    {{-- KPIs --}}
    <div class="mb-6 grid gap-4 md:grid-cols-4">
        <div class="border border-gm-gray-line bg-white p-5">
            <div class="gm-meta">Chiffre d'affaires (payé)</div>
            <div class="mt-2 font-slab text-2xl font-bold italic text-gm-ink">
                {{ number_format($this->kpis['revenue'] / 100, 0, ',', ' ') }} <span class="gm-meta">FCFA</span>
            </div>
        </div>
        <div class="border border-gm-gray-line bg-white p-5">
            <div class="gm-meta">Commandes payées</div>
            <div class="mt-2 font-slab text-2xl font-bold italic text-gm-ink">{{ $this->kpis['paid'] }}</div>
        </div>
        <div class="border border-gm-gray-line bg-white p-5">
            <div class="gm-meta">En attente</div>
            <div class="mt-2 font-slab text-2xl font-bold italic text-gm-charcoal-2">{{ $this->kpis['pending'] }}</div>
        </div>
        <div class="border border-gm-gray-line bg-white p-5">
            <div class="gm-meta">Échecs</div>
            <div class="mt-2 font-slab text-2xl font-bold italic text-gm-red">{{ $this->kpis['failed'] }}</div>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="mb-6 flex flex-wrap items-center gap-3">
        <div class="relative flex-1 min-w-[260px]">
            <input
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="Rechercher par référence, email ou nom…"
                class="w-full border border-gm-gray-line bg-white px-4 py-2.5 font-sans text-sm focus:border-gm-red focus:outline-none"
            />
        </div>

        <select wire:model.live="planId" class="border border-gm-gray-line bg-white px-3 py-2.5 font-sans text-sm">
            <option value="">Toutes formules</option>
            @foreach ($this->plans as $p)
                <option value="{{ $p->id }}">{{ $p->name }}</option>
            @endforeach
        </select>

        <div class="flex flex-wrap items-center gap-1 border border-gm-gray-line bg-white p-1 font-mono text-[11px] uppercase tracking-[0.1em]">
            <button wire:click="$set('status', 'all')" class="px-3 py-1.5 transition-colors {{ $status === 'all' ? 'bg-gm-ink text-white' : 'text-gm-charcoal hover:bg-gm-paper' }}">Toutes</button>
            @foreach ($statuses as $st)
                <button wire:click="$set('status', '{{ $st->value }}')" class="px-3 py-1.5 transition-colors {{ $status === $st->value ? 'bg-gm-ink text-white' : 'text-gm-charcoal hover:bg-gm-paper' }}">
                    {{ $st->label() }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Table --}}
    <div class="border border-gm-gray-line bg-white">
        <table class="w-full">
            <thead class="border-b border-gm-gray-line bg-gm-paper">
                <tr class="gm-meta text-left">
                    <th class="px-6 py-3">Référence</th>
                    <th class="px-6 py-3">Client</th>
                    <th class="px-6 py-3">Formule</th>
                    <th class="px-6 py-3">Date</th>
                    <th class="px-6 py-3 text-right">Montant</th>
                    <th class="px-6 py-3 text-center">Statut</th>
                    <th class="px-6 py-3">Paiement</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gm-gray-line">
                @forelse ($orders as $order)
                    <tr class="hover:bg-gm-paper">
                        <td class="px-6 py-4 font-mono text-xs text-gm-charcoal">{{ $order->reference }}</td>
                        <td class="px-6 py-4">
                            <div class="font-slab text-base font-bold italic text-gm-ink">
                                {{ $order->user->fullName() }}
                            </div>
                            <div class="gm-meta normal-case tracking-normal">{{ $order->user->email }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gm-charcoal">{{ $order->plan?->name ?? '—' }}</td>
                        <td class="gm-meta px-6 py-4">
                            {{ $order->created_at->locale('fr')->translatedFormat('j M Y H:i') }}
                        </td>
                        <td class="px-6 py-4 text-right font-slab text-base font-bold text-gm-ink">
                            {{ number_format($order->total_cents / 100, 0, ',', ' ') }} {{ $order->currency }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            @switch($order->status->value)
                                @case('paid')
                                    <span class="gm-meta inline-block border border-gm-ink bg-gm-ink px-2 py-0.5 text-white">Payée</span>
                                    @break
                                @case('pending')
                                    <span class="gm-meta inline-block border border-gm-charcoal-2 px-2 py-0.5 text-gm-charcoal-2">En attente</span>
                                    @break
                                @case('failed')
                                    <span class="gm-meta inline-block border border-gm-red bg-gm-red-soft px-2 py-0.5 text-gm-red-deep">Échec</span>
                                    @break
                                @default
                                    <span class="gm-meta inline-block border border-gm-gray-line px-2 py-0.5">{{ $order->status->label() }}</span>
                            @endswitch
                        </td>
                        <td class="px-6 py-4">
                            @if ($payment = $order->latestPayment)
                                <span class="gm-meta uppercase">{{ $payment->provider }}</span>
                                <span class="gm-meta mx-1">·</span>
                                <span class="gm-meta">{{ $payment->channel }}</span>
                            @else
                                <span class="gm-meta text-gm-gray">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center">
                            <p class="gm-meta">Aucune commande ne correspond aux filtres.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($orders->hasPages())
        <div class="mt-6">{{ $orders->links() }}</div>
    @endif
</div>
