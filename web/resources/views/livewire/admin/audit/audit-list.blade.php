<div>
    <div class="mb-6 flex flex-wrap items-center gap-3">
        <div class="relative flex-1 min-w-[260px]">
            <input
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="Rechercher (action, objet, IP)…"
                class="w-full border border-gm-gray-line bg-white px-4 py-2.5 font-sans text-sm focus:border-gm-red focus:outline-none"
            />
        </div>

        <div class="flex items-center gap-1 border border-gm-gray-line bg-white p-1 font-mono text-[11px] uppercase tracking-[0.1em]">
            @foreach ([
                'all' => 'Tous',
                'article' => 'Articles',
                'order' => 'Commandes',
                'subscription' => 'Abonnements',
                'user' => 'Utilisateurs',
                'payment' => 'Paiements',
                'auth' => 'Connexions',
            ] as $key => $label)
                <button wire:click="$set('action', '{{ $key }}')" class="px-3 py-1.5 transition-colors {{ $action === $key ? 'bg-gm-ink text-white' : 'text-gm-charcoal hover:bg-gm-paper' }}">{{ $label }}</button>
            @endforeach
        </div>

        <div class="ml-auto">
            <button wire:click="exportCsv" class="gm-meta border border-gm-gray-line bg-white px-3 py-2 hover:border-gm-red hover:text-gm-red">Exporter CSV ↓</button>
        </div>
    </div>

    <div class="border border-gm-gray-line bg-white">
        <table class="w-full">
            <thead class="border-b border-gm-gray-line bg-gm-paper">
                <tr class="gm-meta text-left">
                    <th class="px-6 py-3">Date</th>
                    <th class="px-6 py-3">Utilisateur</th>
                    <th class="px-6 py-3">Action</th>
                    <th class="px-6 py-3">Objet</th>
                    <th class="px-6 py-3">IP</th>
                    <th class="px-6 py-3">Détails</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gm-gray-line">
                @forelse ($logs as $log)
                    <tr class="hover:bg-gm-paper align-top">
                        <td class="gm-meta px-6 py-3 whitespace-nowrap">
                            {{ $log->created_at->format('d/m H:i:s') }}
                        </td>
                        <td class="px-6 py-3 text-sm">
                            @if ($log->user)
                                <div class="font-bold text-gm-ink">{{ $log->user->email }}</div>
                                <div class="gm-meta normal-case tracking-normal">{{ $log->user->getRoleNames()->join(', ') ?: '—' }}</div>
                            @else
                                <span class="gm-meta">système</span>
                            @endif
                        </td>
                        <td class="px-6 py-3">
                            <span class="gm-meta border border-gm-gray-line bg-gm-cream px-2 py-0.5">{{ $log->action }}</span>
                        </td>
                        <td class="gm-meta px-6 py-3">
                            @if ($log->object_type)
                                {{ class_basename($log->object_type) }}#{{ $log->object_id }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="gm-meta px-6 py-3 font-mono">{{ $log->ip ?? '—' }}</td>
                        <td class="px-6 py-3 text-xs max-w-md">
                            @if ($log->changes)
                                @foreach ($log->changes as $field => $value)
                                    <div><span class="gm-meta">{{ $field }}:</span> <code class="bg-gm-cream px-1">{{ is_scalar($value) ? Str::limit((string) $value, 40) : '…' }}</code></div>
                                @endforeach
                            @elseif ($log->context)
                                <div class="gm-meta normal-case tracking-normal">{{ Str::limit(json_encode($log->context), 80) }}</div>
                            @else
                                <span class="gm-meta">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center">
                            <p class="gm-meta">Aucune entrée d'audit.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($logs->hasPages())
        <div class="mt-6">{{ $logs->links() }}</div>
    @endif
</div>
