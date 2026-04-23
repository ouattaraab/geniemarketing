<div>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-3">
            <select wire:model.live="placement" class="border border-gm-gray-line bg-white px-3 py-2.5 font-sans text-sm">
                @foreach ($placements as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            <div class="flex items-center gap-1 border border-gm-gray-line bg-white p-1 font-mono text-[11px] uppercase tracking-[0.1em]">
                @foreach (['all' => 'Toutes', 'active' => 'Actives', 'scheduled' => 'Programmées', 'inactive' => 'Inactives', 'expired' => 'Expirées'] as $key => $label)
                    <button wire:click="$set('status', '{{ $key }}')"
                            class="px-3 py-1.5 transition-colors {{ $status === $key ? 'bg-gm-ink text-white' : 'text-gm-charcoal hover:bg-gm-paper' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>
        <a href="{{ route('admin.ads.create') }}" class="gm-btn-primary" wire:navigate>
            + Nouvelle bannière
        </a>
    </div>

    <div class="border border-gm-gray-line bg-white">
        <table class="w-full">
            <thead class="border-b border-gm-gray-line bg-gm-paper">
                <tr class="gm-meta text-left">
                    <th class="px-6 py-3">Visuel</th>
                    <th class="px-6 py-3">Titre</th>
                    <th class="px-6 py-3">Placement</th>
                    <th class="px-6 py-3 text-center">Priorité</th>
                    <th class="px-6 py-3 text-center">Fenêtre</th>
                    <th class="px-6 py-3 text-center">Impressions / clicks</th>
                    <th class="px-6 py-3 text-center">Statut</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gm-gray-line">
                @forelse ($ads as $ad)
                    <tr class="hover:bg-gm-paper">
                        <td class="px-6 py-4">
                            @if ($ad->imageUrl())
                                <img src="{{ $ad->imageUrl() }}" alt="" class="h-12 w-20 object-cover border border-gm-gray-line" />
                            @else
                                <span class="gm-meta">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-slab text-base font-bold italic text-gm-ink">{{ $ad->title }}</div>
                            @if ($ad->sponsor_name)
                                <div class="gm-meta normal-case tracking-normal">Sponsor : {{ $ad->sponsor_name }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 font-mono text-xs text-gm-charcoal">{{ $ad->placement }}</td>
                        <td class="gm-meta px-6 py-4 text-center">{{ $ad->priority }}</td>
                        <td class="gm-meta px-6 py-4 text-center normal-case tracking-normal text-gm-gray">
                            @if ($ad->starts_at || $ad->ends_at)
                                {{ $ad->starts_at?->format('d/m/Y') ?? '—' }}
                                <br>→<br>
                                {{ $ad->ends_at?->format('d/m/Y') ?? 'illimité' }}
                            @else
                                <span>toujours</span>
                            @endif
                        </td>
                        <td class="gm-meta px-6 py-4 text-center normal-case tracking-normal">
                            {{ number_format($ad->impressions, 0, ',', ' ') }} / {{ number_format($ad->clicks, 0, ',', ' ') }}
                            <br>
                            <span class="text-gm-red">{{ $ad->ctrPercent() }} %</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if ($ad->is_active)
                                <span class="gm-meta inline-block border border-gm-ink bg-gm-ink px-2 py-0.5 text-white">Active</span>
                            @else
                                <span class="gm-meta inline-block border border-gm-gray-line px-2 py-0.5 text-gm-gray">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="inline-flex items-center gap-2">
                                <a href="{{ route('admin.ads.edit', $ad) }}"
                                   class="gm-meta normal-case tracking-normal hover:text-gm-red"
                                   wire:navigate>Éditer</a>
                                <button wire:click="toggleActive({{ $ad->id }})"
                                        class="gm-meta normal-case tracking-normal hover:text-gm-red">
                                    {{ $ad->is_active ? 'Désactiver' : 'Activer' }}
                                </button>
                                @hasanyrole('adm|sup')
                                    <button wire:click="delete({{ $ad->id }})"
                                            wire:confirm="Supprimer définitivement la bannière « {{ $ad->title }} » ?"
                                            class="gm-meta normal-case tracking-normal text-gm-red hover:text-gm-red-deep">
                                        Supprimer
                                    </button>
                                @endhasanyrole
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-10 text-center">
                            <p class="gm-meta">Aucune bannière ne correspond aux filtres.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($ads->hasPages())
        <div class="mt-6">{{ $ads->links() }}</div>
    @endif
</div>
