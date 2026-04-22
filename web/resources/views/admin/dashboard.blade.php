<x-layouts.admin title="Tableau de bord" breadcrumb="Vue d'ensemble · 30 derniers jours">
    {{-- KPIs --}}
    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
        @foreach ($metrics as $metric)
            <div class="border border-gm-gray-line bg-white p-6">
                <div class="gm-meta">{{ $metric['label'] }}</div>
                <div class="mt-3 font-slab text-3xl font-bold italic text-gm-ink">
                    {{ $metric['value'] }}
                </div>
                @if (! empty($metric['hint']))
                    <div class="mt-2 text-xs text-gm-gray">{{ $metric['hint'] }}</div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Courbe revenus --}}
    <div class="mt-10 border border-gm-gray-line bg-white p-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="font-slab text-xl font-bold italic text-gm-ink">Revenus quotidiens</h3>
                <p class="gm-meta mt-1 normal-case tracking-normal">30 derniers jours · commandes payées (FCFA)</p>
            </div>
            <a href="{{ route('admin.orders.index') }}" class="gm-meta hover:text-gm-red">Voir toutes les commandes →</a>
        </div>

        @php
            $max = max(1, max(array_column($revenueSeries, 'value')));
            $w = 800;
            $h = 120;
            $step = $w / max(1, count($revenueSeries) - 1);
            $points = [];
            foreach ($revenueSeries as $i => $pt) {
                $x = round($i * $step, 1);
                $y = round($h - ($pt['value'] / $max) * ($h - 10), 1);
                $points[] = "$x,$y";
            }
            $polyline = implode(' ', $points);
            $areaPath = "M 0,{$h} L ".implode(' L ', $points)." L {$w},{$h} Z";
            $totalRevenue = array_sum(array_column($revenueSeries, 'value'));
        @endphp

        <div class="mt-6 overflow-x-auto">
            <svg viewBox="0 0 {{ $w }} {{ $h + 10 }}" class="w-full" preserveAspectRatio="none" style="min-height: 140px;">
                <path d="{{ $areaPath }}" fill="#FDECEE" />
                <polyline points="{{ $polyline }}" fill="none" stroke="#B40F1E" stroke-width="2" />
                @foreach ($revenueSeries as $i => $pt)
                    @if ($pt['value'] > 0)
                        <circle cx="{{ round($i * $step, 1) }}" cy="{{ round($h - ($pt['value'] / $max) * ($h - 10), 1) }}" r="2.5" fill="#B40F1E" />
                    @endif
                @endforeach
            </svg>
        </div>

        <div class="gm-meta mt-3 flex justify-between">
            <span>{{ $revenueSeries[0]['date'] ?? '—' }}</span>
            <span>Total 30j : <strong class="text-gm-ink">{{ number_format($totalRevenue, 0, ',', ' ') }} FCFA</strong></span>
            <span>{{ end($revenueSeries)['date'] ?? '—' }}</span>
        </div>
    </div>

    <div class="mt-10 grid gap-6 lg:grid-cols-2">
        {{-- Top articles --}}
        <div class="border border-gm-gray-line bg-white p-6">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="font-slab text-xl font-bold italic text-gm-ink">Top articles 30j</h3>
                <a href="{{ route('admin.articles.index') }}" class="gm-meta hover:text-gm-red">Tous →</a>
            </div>
            @if ($topArticles->isEmpty())
                <p class="gm-meta">Aucun article publié dans les 30 derniers jours.</p>
            @else
                <ol class="space-y-3">
                    @foreach ($topArticles as $idx => $a)
                        <li class="flex items-start gap-3">
                            <span class="font-slab text-xl font-bold italic text-gm-red-bright w-8 text-center">{{ $idx + 1 }}</span>
                            <div class="flex-1 min-w-0">
                                <a href="{{ route('admin.articles.edit', $a) }}" class="block font-slab text-base font-bold italic text-gm-ink hover:text-gm-red truncate">
                                    {{ $a->title }}
                                </a>
                                <div class="gm-meta normal-case tracking-normal">{{ $a->category?->name ?? '—' }} · publié {{ $a->published_at->locale('fr')->diffForHumans() }}</div>
                            </div>
                            <span class="gm-meta font-mono shrink-0">{{ number_format($a->views_count, 0, ',', ' ') }} vues</span>
                        </li>
                    @endforeach
                </ol>
            @endif
        </div>

        {{-- Dernières commandes --}}
        <div class="border border-gm-gray-line bg-white p-6">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="font-slab text-xl font-bold italic text-gm-ink">Dernières commandes</h3>
                <a href="{{ route('admin.orders.index') }}" class="gm-meta hover:text-gm-red">Toutes →</a>
            </div>
            @if ($recentOrders->isEmpty())
                <p class="gm-meta">Aucune commande enregistrée.</p>
            @else
                <ul class="space-y-3">
                    @foreach ($recentOrders as $o)
                        <li class="flex items-center gap-3 border-b border-gm-gray-line pb-3 last:border-0 last:pb-0">
                            <div class="flex-1 min-w-0">
                                <div class="font-slab font-bold italic text-gm-ink truncate">{{ $o->user?->fullName() ?? '—' }}</div>
                                <div class="gm-meta normal-case tracking-normal truncate">
                                    {{ $o->plan?->name ?? '—' }} · {{ $o->reference }}
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-mono text-sm text-gm-ink">{{ number_format($o->total_cents / 100, 0, ',', ' ') }} FCFA</div>
                                @switch($o->status->value)
                                    @case('paid')
                                        <span class="gm-meta text-gm-ink">✓ Payée</span> @break
                                    @case('pending')
                                        <span class="gm-meta text-gm-charcoal-2">⋯ En attente</span> @break
                                    @case('failed')
                                        <span class="gm-meta text-gm-red">✗ Échec</span> @break
                                    @default
                                        <span class="gm-meta">{{ $o->status->label() }}</span>
                                @endswitch
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    {{-- Actions rapides --}}
    <div class="mt-10 grid gap-4 md:grid-cols-3">
        <a href="{{ route('admin.articles.create') }}" class="border border-gm-gray-line bg-white p-5 transition hover:border-gm-red hover:shadow-gm-red">
            <div class="font-slab text-lg font-bold italic text-gm-ink">+ Nouvel article</div>
            <div class="gm-meta mt-1 normal-case tracking-normal">Rédiger un article et le soumettre à relecture.</div>
        </a>
        <a href="{{ route('admin.comments.index') }}" class="border border-gm-gray-line bg-white p-5 transition hover:border-gm-red hover:shadow-gm-red">
            <div class="flex items-center gap-2">
                <span class="font-slab text-lg font-bold italic text-gm-ink">Modérer les commentaires</span>
                @if ($pendingComments > 0)
                    <span class="gm-meta border border-gm-red bg-gm-red px-2 py-0.5 text-white">{{ $pendingComments }}</span>
                @endif
            </div>
            <div class="gm-meta mt-1 normal-case tracking-normal">Valider ou rejeter la file d'attente.</div>
        </a>
        <a href="{{ route('admin.newsletters.create') }}" class="border border-gm-gray-line bg-white p-5 transition hover:border-gm-red hover:shadow-gm-red">
            <div class="font-slab text-lg font-bold italic text-gm-ink">+ Nouvelle campagne</div>
            <div class="gm-meta mt-1 normal-case tracking-normal">Préparer une newsletter et l'envoyer aux abonnés.</div>
        </a>
    </div>
</x-layouts.admin>
