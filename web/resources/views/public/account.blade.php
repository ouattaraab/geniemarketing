<x-layouts.public title="Mon compte — GÉNIE MARKETING Mag">
    <section class="mx-auto max-w-container-narrow px-8 py-16">
        <x-gm.section-heading subtitle="{{ $user->email }}">
            Mon compte
        </x-gm.section-heading>

        @if (session('status'))
            <div class="mb-8 border-l-4 border-gm-ink bg-white px-4 py-3 text-sm text-gm-ink">
                {{ session('status') }}
            </div>
        @endif

        {{-- Abonnement actif --}}
        <div class="mb-12">
            <h3 class="font-slab text-xl font-bold italic text-gm-ink mb-4">Mon abonnement</h3>

            @if ($activeSubscription)
                <div class="border border-gm-red bg-white p-6">
                    <div class="flex items-start justify-between gap-6">
                        <div>
                            <span class="font-mono text-[11px] font-bold uppercase tracking-[0.15em] text-gm-red">
                                {{ $activeSubscription->status->value === 'trialing' ? 'Période d\'essai' : 'Actif' }}
                            </span>
                            <h4 class="mt-2 font-slab text-2xl font-bold italic text-gm-ink">
                                {{ $activeSubscription->plan->name }}
                            </h4>
                            <div class="gm-meta mt-2 space-y-1 normal-case tracking-normal">
                                <div>Commencé le {{ $activeSubscription->start_date->locale('fr')->translatedFormat('j F Y') }}</div>
                                <div>Expire le <strong class="text-gm-ink">{{ $activeSubscription->end_date->locale('fr')->translatedFormat('j F Y') }}</strong></div>
                                @if ($activeSubscription->trial_ends_at && $activeSubscription->trial_ends_at->isFuture())
                                    <div class="text-gm-red">Essai jusqu'au {{ $activeSubscription->trial_ends_at->locale('fr')->translatedFormat('j F Y') }}</div>
                                @endif
                                <div>Renouvellement automatique : {{ $activeSubscription->auto_renewal ? 'activé' : 'désactivé' }}</div>
                            </div>
                        </div>
                        <div class="font-slab text-3xl font-bold text-gm-ink">
                            {{ number_format($activeSubscription->plan->price_cents / 100, 0, ',', ' ') }}
                            <span class="gm-meta">FCFA/an</span>
                        </div>
                    </div>
                </div>
            @else
                <div class="border border-dashed border-gm-gray-line bg-gm-cream p-8 text-center">
                    <p class="gm-meta">Aucun abonnement actif</p>
                    <p class="mt-3 font-slab text-xl italic text-gm-charcoal">
                        Souscrivez pour accéder à tous les articles premium et numéros PDF.
                    </p>
                    <a href="{{ route('subscribe') }}" class="gm-btn-primary mt-6 inline-block">
                        Voir les formules
                    </a>
                </div>
            @endif
        </div>

        {{-- Articles achetés à l'unité --}}
        @if ($purchasedArticles->isNotEmpty())
            <div class="mb-12">
                <h3 class="font-slab text-xl font-bold italic text-gm-ink mb-4">Mes articles achetés</h3>
                <div class="grid gap-4 md:grid-cols-2">
                    @foreach ($purchasedArticles as $ar)
                        @php $art = $ar->article; @endphp
                        @if ($art)
                            <a href="{{ route('article.show', $art) }}"
                               class="group block border border-gm-gray-line bg-white p-5 transition-colors hover:border-gm-red">
                                <span class="gm-meta text-gm-red">{{ $art->category?->name ?? '—' }}</span>
                                <h4 class="mt-2 font-slab text-lg font-bold italic text-gm-ink group-hover:text-gm-red">
                                    {{ $art->title }}
                                </h4>
                                <p class="gm-meta mt-2 normal-case tracking-normal text-gm-gray">
                                    Acheté le {{ $ar->granted_at->locale('fr')->translatedFormat('j F Y') }}
                                    @if ($ar->expires_at)
                                        · valide jusqu'au {{ $ar->expires_at->locale('fr')->translatedFormat('j F Y') }}
                                    @else
                                        · accès permanent
                                    @endif
                                </p>
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Historique des commandes --}}
        <div class="mb-12">
            <h3 class="font-slab text-xl font-bold italic text-gm-ink mb-4">Historique des commandes</h3>

            @if ($orders->isEmpty())
                <p class="gm-meta">Aucune commande pour l'instant.</p>
            @else
                <div class="border border-gm-gray-line bg-white">
                    <table class="w-full">
                        <thead class="border-b border-gm-gray-line bg-gm-paper">
                            <tr class="gm-meta text-left">
                                <th class="px-6 py-3">Référence</th>
                                <th class="px-6 py-3">Formule</th>
                                <th class="px-6 py-3">Date</th>
                                <th class="px-6 py-3 text-right">Montant</th>
                                <th class="px-6 py-3 text-center">Statut</th>
                                <th class="px-6 py-3 text-right">Facture</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gm-gray-line">
                            @foreach ($orders as $order)
                                <tr class="hover:bg-gm-paper">
                                    <td class="px-6 py-4 font-mono text-xs text-gm-charcoal">{{ $order->reference }}</td>
                                    <td class="px-6 py-4 text-sm text-gm-ink">{{ $order->plan?->name ?? '—' }}</td>
                                    <td class="gm-meta px-6 py-4">
                                        {{ $order->created_at->locale('fr')->translatedFormat('j F Y') }}
                                    </td>
                                    <td class="px-6 py-4 text-right font-slab text-base font-bold">
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
                                    <td class="px-6 py-4 text-right">
                                        @if ($order->invoice)
                                            <a href="{{ route('account.invoice.download', $order->invoice) }}" class="gm-meta hover:text-gm-red" target="_blank">
                                                {{ $order->invoice->number }} ↓
                                            </a>
                                        @else
                                            <span class="gm-meta text-gm-gray">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Préférences --}}
        <div>
            <h3 class="font-slab text-xl font-bold italic text-gm-ink mb-4">Préférences</h3>
            <div class="border border-gm-gray-line bg-white p-6">
                <a href="{{ route('profile.edit') }}" class="gm-meta hover:text-gm-red">
                    Modifier mes informations personnelles →
                </a>
            </div>
        </div>

        {{-- Session --}}
        <div class="mt-12 border-t border-gm-gray-line pt-8">
            <form method="POST" action="{{ route('logout') }}" class="flex flex-col items-start gap-3 sm:flex-row sm:items-center sm:justify-between">
                @csrf
                <p class="gm-meta normal-case tracking-normal">
                    Connecté en tant que <strong class="text-gm-ink">{{ $user->email }}</strong>
                </p>
                <button type="submit"
                        class="border border-gm-red px-5 py-3 font-mono text-[11px] font-bold uppercase tracking-[0.15em] text-gm-red hover:bg-gm-red hover:text-white transition-colors">
                    Se déconnecter
                </button>
            </form>
        </div>
    </section>
</x-layouts.public>
