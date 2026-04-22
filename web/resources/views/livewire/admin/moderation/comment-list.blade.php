<div>
    <div class="mb-6 flex flex-wrap items-center gap-3">
        <div class="relative flex-1 min-w-[260px]">
            <input
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="Rechercher (contenu, email)…"
                class="w-full border border-gm-gray-line bg-white px-4 py-2.5 font-sans text-sm focus:border-gm-red focus:outline-none"
            />
        </div>

        <div class="flex items-center gap-1 border border-gm-gray-line bg-white p-1 font-mono text-[11px] uppercase tracking-[0.1em]">
            <button wire:click="$set('status', 'pending')" class="px-3 py-1.5 transition-colors {{ $status === 'pending' ? 'bg-gm-red text-white' : 'text-gm-charcoal hover:bg-gm-paper' }}">
                À modérer ({{ $this->counts['pending'] }})
            </button>
            <button wire:click="$set('status', 'approved')" class="px-3 py-1.5 transition-colors {{ $status === 'approved' ? 'bg-gm-ink text-white' : 'text-gm-charcoal hover:bg-gm-paper' }}">
                Approuvés ({{ $this->counts['approved'] }})
            </button>
            <button wire:click="$set('status', 'rejected')" class="px-3 py-1.5 transition-colors {{ $status === 'rejected' ? 'bg-gm-gray text-white' : 'text-gm-charcoal hover:bg-gm-paper' }}">
                Rejetés ({{ $this->counts['rejected'] }})
            </button>
            <button wire:click="$set('status', 'all')" class="px-3 py-1.5 transition-colors {{ $status === 'all' ? 'bg-gm-ink text-white' : 'text-gm-charcoal hover:bg-gm-paper' }}">
                Tous
            </button>
        </div>
    </div>

    <div class="space-y-4">
        @forelse ($comments as $comment)
            <article class="border border-gm-gray-line bg-white p-5">
                <header class="mb-3 flex flex-wrap items-center gap-3 text-sm">
                    <span class="font-slab font-bold italic text-gm-ink">{{ $comment->user->fullName() }}</span>
                    <span class="gm-meta">{{ $comment->user->email }}</span>
                    <span class="gm-meta">·</span>
                    <span class="gm-meta">{{ $comment->created_at->locale('fr')->diffForHumans() }}</span>
                    <span class="gm-meta">·</span>
                    <a href="{{ route('article.show', $comment->article) }}" target="_blank" class="gm-meta hover:text-gm-red">
                        Sur « {{ Str::limit($comment->article->title, 50) }} » ↗
                    </a>

                    <span class="ml-auto gm-meta border border-gm-gray-line px-2 py-0.5">{{ ucfirst($comment->status) }}</span>
                </header>

                <p class="text-gm-charcoal leading-relaxed whitespace-pre-line">{{ $comment->content }}</p>

                <footer class="mt-4 flex items-center gap-3">
                    @if ($comment->status === 'pending')
                        <button wire:click="approve({{ $comment->id }})" class="gm-btn-primary">Approuver</button>
                        <button wire:click="reject({{ $comment->id }})" class="gm-meta border border-gm-gray-line bg-white px-3 py-2 hover:border-gm-red hover:text-gm-red">Rejeter</button>
                    @elseif ($comment->status === 'approved')
                        <button wire:click="reject({{ $comment->id }})" class="gm-meta border border-gm-gray-line bg-white px-3 py-2 hover:border-gm-red hover:text-gm-red">Dépublier</button>
                    @elseif ($comment->status === 'rejected')
                        <button wire:click="approve({{ $comment->id }})" class="gm-meta border border-gm-gray-line bg-white px-3 py-2 hover:border-gm-red hover:text-gm-red">Réhabiliter</button>
                    @endif
                    @if ($comment->moderated_at)
                        <span class="ml-auto gm-meta">
                            Modéré par {{ $comment->moderatedBy?->email ?? '—' }} · {{ $comment->moderated_at->locale('fr')->diffForHumans() }}
                        </span>
                    @endif
                </footer>
            </article>
        @empty
            <div class="border border-dashed border-gm-gray-line bg-gm-cream p-10 text-center">
                <p class="gm-meta">Aucun commentaire dans cette file.</p>
            </div>
        @endforelse
    </div>

    @if ($comments->hasPages())
        <div class="mt-6">{{ $comments->links() }}</div>
    @endif
</div>
