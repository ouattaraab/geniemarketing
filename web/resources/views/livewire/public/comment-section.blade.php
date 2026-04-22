<section class="mx-auto max-w-container-narrow px-8 pb-16">
    <x-gm.section-heading>Discussion</x-gm.section-heading>

    {{-- Formulaire --}}
    @auth
        <form wire:submit="submit" class="mb-10 border border-gm-gray-line bg-white p-6">
            <label class="gm-meta mb-2 block">Votre commentaire</label>
            <textarea
                wire:model="newComment"
                rows="4"
                maxlength="2000"
                class="w-full border border-gm-gray-line px-3 py-2 font-sans text-base focus:border-gm-red focus:ring-0"
                placeholder="Partagez votre point de vue…"
            ></textarea>
            @error('newComment')<p class="gm-meta mt-2 text-gm-red">{{ $message }}</p>@enderror

            <div class="mt-4 flex items-center justify-between">
                <p class="gm-meta">
                    Les commentaires sont modérés avant publication. Restons courtois et factuels.
                </p>
                <button type="submit" class="gm-btn-primary" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="submit">Envoyer</span>
                    <span wire:loading wire:target="submit">Envoi…</span>
                </button>
            </div>

            @if (session('comment-status'))
                <p class="mt-4 border-l-2 border-gm-red bg-gm-red-soft px-3 py-2 text-sm text-gm-red-deep">
                    {{ session('comment-status') }}
                </p>
            @endif
        </form>
    @else
        <div class="mb-10 border border-gm-gray-line bg-white p-6 text-center">
            <p class="gm-meta">Vous devez être abonné pour commenter.</p>
            <div class="mt-3 flex items-center justify-center gap-3">
                <a href="{{ route('login') }}" class="gm-meta hover:text-gm-red">Se connecter</a>
                <span class="gm-meta">·</span>
                <a href="{{ route('subscribe') }}" class="gm-btn-primary">S'abonner</a>
            </div>
        </div>
    @endauth

    {{-- Liste --}}
    @if ($this->comments->isEmpty())
        <p class="gm-meta text-center">Aucun commentaire publié. Soyez le premier !</p>
    @else
        <div class="space-y-6">
            @foreach ($this->comments as $comment)
                <article class="border-l-2 border-gm-gray-line bg-white p-5">
                    <header class="flex items-center justify-between gap-3 mb-3">
                        <div>
                            <span class="font-slab font-bold italic text-gm-ink">{{ $comment->user->fullName() }}</span>
                            <time class="gm-meta ml-3">{{ $comment->created_at->locale('fr')->diffForHumans() }}</time>
                        </div>
                    </header>
                    <p class="text-gm-charcoal leading-relaxed whitespace-pre-line">{{ $comment->content }}</p>

                    @if ($comment->replies->isNotEmpty())
                        <div class="mt-4 ml-6 space-y-3 border-l-2 border-gm-gray-line pl-4">
                            @foreach ($comment->replies as $reply)
                                <div>
                                    <div class="gm-meta">
                                        <strong class="text-gm-ink">{{ $reply->user->fullName() }}</strong>
                                        · {{ $reply->created_at->locale('fr')->diffForHumans() }}
                                    </div>
                                    <p class="mt-1 text-sm text-gm-charcoal">{{ $reply->content }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </article>
            @endforeach
        </div>
    @endif
</section>
