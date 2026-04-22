<div>
    {{-- Filtres --}}
    <div class="mb-6 flex flex-wrap items-center gap-3">
        <div class="relative flex-1 min-w-[260px]">
            <input
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="Rechercher un article (titre, chapô)…"
                class="w-full border border-gm-gray-line bg-white px-4 py-2.5 font-sans text-sm focus:border-gm-red focus:outline-none"
            />
        </div>

        <select wire:model.live="categoryId" class="border border-gm-gray-line bg-white px-3 py-2.5 font-sans text-sm">
            <option value="">Toutes rubriques</option>
            @foreach ($this->categories as $cat)
                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
            @endforeach
        </select>

        <div class="flex flex-wrap items-center gap-1 border border-gm-gray-line bg-white p-1 font-mono text-[11px] uppercase tracking-[0.1em]">
            <button
                wire:click="$set('status', 'all')"
                class="px-3 py-1.5 transition-colors {{ $status === 'all' ? 'bg-gm-ink text-white' : 'text-gm-charcoal hover:bg-gm-paper' }}"
            >
                Tous ({{ $this->statusCounts['all'] ?? 0 }})
            </button>
            @foreach ($statuses as $st)
                <button
                    wire:click="$set('status', '{{ $st->value }}')"
                    class="px-3 py-1.5 transition-colors {{ $status === $st->value ? 'bg-gm-ink text-white' : 'text-gm-charcoal hover:bg-gm-paper' }}"
                >
                    {{ $st->label() }} ({{ $this->statusCounts[$st->value] ?? 0 }})
                </button>
            @endforeach
        </div>

        <div class="ml-auto">
            <a href="{{ route('admin.articles.create') }}" class="gm-btn-primary">Nouvel article</a>
        </div>
    </div>

    {{-- Table --}}
    <div class="border border-gm-gray-line bg-white">
        <table class="w-full">
            <thead class="border-b border-gm-gray-line bg-gm-paper">
                <tr class="gm-meta text-left">
                    <th class="px-6 py-3">Titre</th>
                    <th class="px-6 py-3">Rubrique</th>
                    <th class="px-6 py-3">Auteurs</th>
                    <th class="px-6 py-3">Statut</th>
                    <th class="px-6 py-3 text-right">Vues</th>
                    <th class="px-6 py-3">Mise à jour</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gm-gray-line">
                @forelse ($articles as $article)
                    <tr class="hover:bg-gm-paper">
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.articles.edit', $article) }}" class="font-slab text-base font-bold italic text-gm-ink hover:text-gm-red">
                                {{ $article->title }}
                            </a>
                            @if ($article->lede)
                                <div class="gm-meta mt-1 normal-case tracking-normal">
                                    {{ Str::limit($article->lede, 80) }}
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gm-charcoal">
                            {{ $article->category->name }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gm-charcoal">
                            {{ $article->authors->pluck('name')->join(', ') ?: '—' }}
                        </td>
                        <td class="px-6 py-4">
                            @php $status = $article->status; @endphp
                            <span class="gm-meta inline-block border px-2 py-0.5
                                @switch($status)
                                    @case(\App\Enums\ArticleStatus::Draft) border-gm-gray-line text-gm-gray @break
                                    @case(\App\Enums\ArticleStatus::Review) border-gm-red bg-gm-red-soft text-gm-red-deep @break
                                    @case(\App\Enums\ArticleStatus::Scheduled) border-gm-charcoal-2 text-gm-charcoal-2 @break
                                    @case(\App\Enums\ArticleStatus::Published) border-gm-ink bg-gm-ink text-white @break
                                    @case(\App\Enums\ArticleStatus::Archived) border-gm-gray-line bg-gm-cream text-gm-gray @break
                                @endswitch
                            ">
                                {{ $status->label() }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right font-mono text-sm text-gm-charcoal">
                            {{ number_format($article->views_count ?? 0, 0, ',', ' ') }}
                        </td>
                        <td class="px-6 py-4 gm-meta">
                            {{ $article->updated_at->locale('fr')->diffForHumans() }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('admin.articles.edit', $article) }}" class="gm-meta hover:text-gm-red">Éditer</a>
                            @if ($article->status === \App\Enums\ArticleStatus::Published)
                                <span class="gm-meta mx-2">·</span>
                                <a href="{{ route('article.show', $article) }}" target="_blank" class="gm-meta hover:text-gm-red">Voir</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center">
                            <p class="gm-meta">Aucun article ne correspond à vos filtres.</p>
                            <a href="{{ route('admin.articles.create') }}" class="gm-btn-primary mt-4 inline-block">
                                Créer le premier article
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($articles->hasPages())
        <div class="mt-6">
            {{ $articles->links() }}
        </div>
    @endif
</div>
