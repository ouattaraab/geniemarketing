<div>
    <p class="gm-meta mb-6 normal-case tracking-normal">
        Activez les moyens de paiement proposés au checkout. La clé technique correspondante
        doit être configurée dans <code class="bg-gm-cream px-1 py-0.5 font-mono text-xs">.env</code>
        (colonne « Variable .env »). Un moyen désactivé est masqué des utilisateurs, même si sa clé est présente.
    </p>

    <div class="border border-gm-gray-line bg-white">
        <table class="w-full">
            <thead class="border-b border-gm-gray-line bg-gm-paper">
                <tr class="gm-meta text-left">
                    <th class="px-6 py-3">Moyen de paiement</th>
                    <th class="px-6 py-3">Description</th>
                    <th class="px-6 py-3">Variable .env</th>
                    <th class="px-6 py-3 text-center">État</th>
                    <th class="px-6 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gm-gray-line">
                @forelse ($methods as $m)
                    <tr class="hover:bg-gm-paper">
                        <td class="px-6 py-4">
                            <div class="font-slab text-base font-bold italic text-gm-ink">{{ $m->name }}</div>
                            <div class="gm-meta normal-case tracking-normal">code : <span class="font-mono">{{ $m->code }}</span></div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gm-charcoal">{{ $m->description ?? '—' }}</td>
                        <td class="px-6 py-4 font-mono text-xs text-gm-charcoal">{{ $m->env_var ?? '—' }}</td>
                        <td class="px-6 py-4 text-center">
                            @if ($m->is_enabled)
                                <span class="gm-meta inline-block border border-gm-ink bg-gm-ink px-2 py-0.5 text-white">Activé</span>
                            @else
                                <span class="gm-meta inline-block border border-gm-gray-line px-2 py-0.5 text-gm-gray">Désactivé</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button wire:click="toggle({{ $m->id }})"
                                    wire:confirm="{{ $m->is_enabled ? 'Désactiver' : 'Activer' }} « {{ $m->name }} » pour les utilisateurs ?"
                                    class="border px-4 py-2 font-mono text-[11px] font-bold uppercase tracking-[0.15em] transition-colors
                                    {{ $m->is_enabled
                                        ? 'border-gm-red text-gm-red hover:bg-gm-red hover:text-white'
                                        : 'border-gm-ink text-gm-ink hover:bg-gm-ink hover:text-white' }}">
                                {{ $m->is_enabled ? 'Désactiver' : 'Activer' }}
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center">
                            <p class="gm-meta">Aucun moyen de paiement configuré.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <p class="gm-meta mt-6 normal-case tracking-normal text-gm-gray">
        ⓘ Au moins un moyen de paiement doit être activé pour que le tunnel d'abonnement soit disponible.
        Si tous sont désactivés, la page /abonnement affiche un message d'indisponibilité.
    </p>
</div>
