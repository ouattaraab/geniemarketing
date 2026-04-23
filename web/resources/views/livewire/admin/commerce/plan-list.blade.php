<div>
    <div class="mb-6 flex items-center justify-between gap-4">
        <p class="gm-meta normal-case tracking-normal">
            Liste des formules proposées aux abonnés. Les plans inactifs sont masqués de la page publique /abonnement.
        </p>
        @if (auth()->user()->hasAnyRole(['com', 'adm', 'sup']))
            <a href="{{ route('admin.plans.create') }}" class="gm-btn-primary" wire:navigate>
                + Nouvelle formule
            </a>
        @endif
    </div>

    <div class="border border-gm-gray-line bg-white">
        <table class="w-full">
            <thead class="border-b border-gm-gray-line bg-gm-paper">
                <tr class="gm-meta text-left">
                    <th class="px-6 py-3">Code</th>
                    <th class="px-6 py-3">Nom</th>
                    <th class="px-6 py-3 text-right">Prix</th>
                    <th class="px-6 py-3 text-center">Durée</th>
                    <th class="px-6 py-3 text-center">Licences</th>
                    <th class="px-6 py-3 text-center">Abonnés</th>
                    <th class="px-6 py-3 text-center">Statut</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gm-gray-line">
                @forelse ($plans as $plan)
                    <tr class="hover:bg-gm-paper">
                        <td class="px-6 py-4 font-mono text-xs text-gm-charcoal">{{ $plan->code }}</td>
                        <td class="px-6 py-4">
                            <div class="font-slab text-base font-bold italic text-gm-ink">
                                {{ $plan->name }}
                                @if ($plan->is_featured)
                                    <span class="gm-meta ml-2 border border-gm-red px-1.5 py-0.5 text-gm-red">★ mis en avant</span>
                                @endif
                            </div>
                            @if ($plan->description)
                                <div class="gm-meta normal-case tracking-normal mt-1">{{ \Illuminate\Support\Str::limit($plan->description, 80) }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right font-slab text-base font-bold text-gm-ink">
                            {{ number_format($plan->price_cents / 100, 0, ',', ' ') }}
                            <span class="gm-meta">{{ $plan->currency }}</span>
                        </td>
                        <td class="gm-meta px-6 py-4 text-center">{{ $plan->duration_months }} mois</td>
                        <td class="gm-meta px-6 py-4 text-center">{{ $plan->licenses_included }}</td>
                        <td class="gm-meta px-6 py-4 text-center">{{ $plan->subscriptions_count }}</td>
                        <td class="px-6 py-4 text-center">
                            @if ($plan->is_active)
                                <span class="gm-meta inline-block border border-gm-ink bg-gm-ink px-2 py-0.5 text-white">Actif</span>
                            @else
                                <span class="gm-meta inline-block border border-gm-gray-line px-2 py-0.5 text-gm-gray">Inactif</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="inline-flex items-center gap-2">
                                <a href="{{ route('admin.plans.edit', $plan) }}"
                                   class="gm-meta normal-case tracking-normal hover:text-gm-red"
                                   wire:navigate>Éditer</a>
                                <button wire:click="toggleActive({{ $plan->id }})"
                                        wire:confirm="Basculer le statut de la formule « {{ $plan->name }} » ?"
                                        class="gm-meta normal-case tracking-normal hover:text-gm-red">
                                    {{ $plan->is_active ? 'Désactiver' : 'Activer' }}
                                </button>
                                @hasrole('sup')
                                    <button wire:click="delete({{ $plan->id }})"
                                            wire:confirm="Supprimer définitivement la formule « {{ $plan->name }} » ? (refusé si des abonnements y sont liés)"
                                            class="gm-meta normal-case tracking-normal text-gm-red hover:text-gm-red-deep">
                                        Supprimer
                                    </button>
                                @endhasrole
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-10 text-center">
                            <p class="gm-meta">Aucune formule. Créez la première pour ouvrir les abonnements.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
