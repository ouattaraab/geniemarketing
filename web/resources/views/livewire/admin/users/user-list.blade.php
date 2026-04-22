<div>
    <div class="mb-6 flex flex-wrap items-center gap-3">
        <div class="relative flex-1 min-w-[260px]">
            <input
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="Rechercher un collaborateur (nom, email)…"
                class="w-full border border-gm-gray-line bg-white px-4 py-2.5 font-sans text-sm focus:border-gm-red focus:outline-none"
            />
        </div>

        <select wire:model.live="role" class="border border-gm-gray-line bg-white px-3 py-2.5 font-sans text-sm">
            <option value="all">Tous rôles</option>
            <option value="red">Rédacteur</option>
            <option value="chef">Rédacteur en chef</option>
            <option value="edit">Admin éditorial</option>
            <option value="com">Commercial</option>
            <option value="adm">Admin système</option>
            <option value="sup">Super admin</option>
        </select>

        <div class="flex items-center gap-1 border border-gm-gray-line bg-white p-1 font-mono text-[11px] uppercase tracking-[0.1em]">
            @foreach (['all' => 'Tous', 'active' => 'Actifs', 'inactive' => 'Inactifs', 'pending' => 'En attente'] as $key => $label)
                <button
                    wire:click="$set('status', '{{ $key }}')"
                    class="px-3 py-1.5 transition-colors {{ $status === $key ? 'bg-gm-ink text-white' : 'text-gm-charcoal hover:bg-gm-paper' }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <div class="ml-auto">
            <a href="{{ route('admin.users.create') }}" class="gm-btn-primary">Nouveau collaborateur</a>
        </div>
    </div>

    <div class="border border-gm-gray-line bg-white">
        <table class="w-full">
            <thead class="border-b border-gm-gray-line bg-gm-paper">
                <tr class="gm-meta text-left">
                    <th class="px-6 py-3">Nom</th>
                    <th class="px-6 py-3">Email</th>
                    <th class="px-6 py-3">Rôle</th>
                    <th class="px-6 py-3 text-center">Statut</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gm-gray-line">
                @forelse ($users as $u)
                    <tr class="hover:bg-gm-paper">
                        <td class="px-6 py-4 font-slab text-base font-bold italic text-gm-ink">
                            {{ $u->first_name }} {{ $u->last_name }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gm-charcoal">
                            {{ $u->email }}
                        </td>
                        <td class="px-6 py-4">
                            @foreach ($u->roles as $r)
                                <span class="gm-meta inline-block border border-gm-gray-line bg-gm-cream px-2 py-0.5">{{ $r->name }}</span>
                            @endforeach
                        </td>
                        <td class="px-6 py-4 text-center">
                            @switch($u->status)
                                @case('active')
                                    <span class="gm-meta inline-block border border-gm-red bg-gm-red-soft px-2 py-0.5 text-gm-red-deep">Actif</span>
                                    @break
                                @case('pending')
                                    <span class="gm-meta inline-block border border-gm-charcoal-2 px-2 py-0.5 text-gm-charcoal-2">En attente</span>
                                    @break
                                @default
                                    <span class="gm-meta inline-block border border-gm-gray-line px-2 py-0.5 text-gm-gray">Inactif</span>
                            @endswitch
                        </td>
                        <td class="px-6 py-4 text-right">
                            @can('update', $u)
                                <a href="{{ route('admin.users.edit', $u) }}" class="gm-meta hover:text-gm-red">Éditer</a>
                            @endcan
                            @can('delete', $u)
                                <span class="gm-meta mx-2">·</span>
                                <button wire:click="toggleStatus({{ $u->id }})" wire:confirm="Confirmer le changement de statut ?" class="gm-meta hover:text-gm-red">
                                    {{ $u->status === 'active' ? 'Désactiver' : 'Réactiver' }}
                                </button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center">
                            <p class="gm-meta">Aucun utilisateur ne correspond aux filtres.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($users->hasPages())
        <div class="mt-6">{{ $users->links() }}</div>
    @endif
</div>
