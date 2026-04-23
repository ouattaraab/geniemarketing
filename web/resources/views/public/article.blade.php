<x-layouts.public
    :title="$article->meta_title ?? $article->title"
    :description="$article->meta_description ?? $article->lede"
    :og-image="$article->cover?->url() ?? url('/og-default.png')"
    og-type="article"
    :canonical="$article->canonical_url ?: route('article.show', $article)"
>
    <article class="mx-auto max-w-container-narrow px-8 py-16">

        {{-- Bannière publicitaire — haut de l'article --}}
        <x-gm.ad placement="article_top" class="mb-8" />

        {{-- Métadonnées --}}
        <div class="mb-6 flex items-center gap-3">
            <a href="{{ route('category.show', $article->category) }}"
               class="font-mono text-[11px] font-bold uppercase tracking-[0.15em] text-gm-red hover:text-gm-red-deep">
                {{ $article->category->name }}
            </a>
            @if ($article->editorialCategory)
                <span class="gm-meta">·</span>
                <span class="font-mono text-[11px] font-bold uppercase tracking-[0.15em] text-gm-charcoal-2">
                    {{ $article->editorialCategory->name }}
                </span>
            @endif
            @if ($article->access_level !== \App\Enums\ArticleAccessLevel::Free)
                <span class="gm-meta border border-gm-ink bg-gm-ink px-1.5 py-0.5 text-white">Premium</span>
            @endif
        </div>

        {{-- Titre + chapô --}}
        <h1 class="font-slab text-4xl font-bold leading-tight text-gm-ink md:text-5xl">
            {{ $article->title }}
        </h1>

        @if ($article->lede)
            <p class="mt-6 font-slab text-xl leading-relaxed text-gm-charcoal md:text-2xl">
                {{ $article->lede }}
            </p>
        @endif

        {{-- Signature --}}
        <div class="gm-meta mt-8 flex items-center gap-4 border-y border-gm-gray-line py-4">
            @if ($article->authors->isNotEmpty())
                <span>Par <strong class="text-gm-ink">{{ $article->authors->pluck('name')->join(', ') }}</strong></span>
                <span>·</span>
            @endif
            <time datetime="{{ $article->published_at->toIso8601String() }}">
                {{ $article->published_at->locale('fr')->translatedFormat('j F Y') }}
            </time>
            @if ($article->reading_time_minutes)
                <span>·</span>
                <span>{{ $article->reading_time_minutes }} min de lecture</span>
            @endif
        </div>

        {{-- Cover --}}
        @if ($article->cover)
            <figure class="mt-10">
                <img src="{{ $article->cover->url() }}" alt="{{ $article->cover->alt ?? '' }}" class="w-full border border-gm-gray-line" />
                @if ($article->cover->caption || $article->cover->credit)
                    <figcaption class="gm-meta mt-2 flex justify-between">
                        <span>{{ $article->cover->caption }}</span>
                        @if ($article->cover->credit)
                            <span>{{ $article->cover->credit }}</span>
                        @endif
                    </figcaption>
                @endif
            </figure>
        @endif

        {{-- Contenu --}}
        <div class="prose prose-lg prose-slate mt-10 max-w-none font-sans text-gm-ink">
            @php
                $renderer = app(\App\Services\TiptapRenderer::class);
                $body = is_array($article->body) ? $article->body : null;
                $fullHtml = $body ? $renderer->toHtml($body) : '';
            @endphp

            @if (! $hasAccess)
                {{-- Paywall : 30% premiers blocs puis CTA --}}
                @php
                    $previewDoc = $body;
                    if (is_array($body) && isset($body['content'])) {
                        $n = max(1, (int) floor(count($body['content']) * 0.3));
                        $previewDoc = ['type' => 'doc', 'content' => array_slice($body['content'], 0, $n)];
                    }
                    $previewHtml = $renderer->toHtml($previewDoc);
                    $isPurchasable = $article->isPurchasable();
                    $priceLabel = $isPurchasable
                        ? number_format($article->price_cents / 100, 0, ',', ' ').' '.$article->price_currency
                        : null;
                @endphp
                {!! $previewHtml !!}

                @if ($isPurchasable)
                    {{-- Paywall spécifique : article payant à l'unité --}}
                    <div class="mt-10 border-y-2 border-gm-red bg-white p-8 text-center not-prose">
                        <span class="font-mono text-[11px] font-bold uppercase tracking-[0.15em] text-gm-red">Article premium — accès à la demande</span>
                        <h3 class="mt-3 font-slab text-2xl font-bold italic text-gm-ink md:text-3xl">
                            Débloquer cet article pour <span class="text-gm-red">{{ $priceLabel }}</span>
                        </h3>
                        <p class="mt-3 text-gm-charcoal">
                            Accès permanent à cet article, relisable depuis votre espace « Mon compte ».
                        </p>
                        <div class="mt-6 flex flex-col items-center justify-center gap-3 sm:flex-row">
                            <form method="POST" action="{{ route('article.buy', $article) }}">
                                @csrf
                                <button type="submit" class="gm-btn-primary">
                                    @auth
                                        Payer {{ $priceLabel }} avec Wave
                                    @else
                                        Créer un compte et acheter ({{ $priceLabel }})
                                    @endauth
                                </button>
                            </form>
                            <span class="gm-meta">ou</span>
                            <a href="{{ route('subscribe') }}" class="gm-meta normal-case tracking-normal text-gm-ink underline hover:text-gm-red">
                                S'abonner à l'illimité à partir de 24 000 FCFA/an
                            </a>
                        </div>
                        @guest
                            <p class="gm-meta mt-4 normal-case tracking-normal text-gm-gray">
                                Déjà un compte ? <a href="{{ route('login') }}" class="underline hover:text-gm-red">Se connecter</a>.
                            </p>
                        @endguest
                    </div>
                @else
                    {{-- Paywall abonnement standard --}}
                    <div class="mt-10 border-y-2 border-gm-ink bg-white p-8 text-center not-prose">
                        <span class="font-mono text-[11px] font-bold uppercase tracking-[0.15em] text-gm-red">Article réservé aux abonnés</span>
                        <h3 class="mt-3 font-slab text-2xl font-bold italic text-gm-ink md:text-3xl">
                            Lisez la suite avec l'abonnement GM
                        </h3>
                        <p class="mt-3 text-gm-charcoal">
                            Accédez à tous les articles premium, aux numéros PDF et à la newsletter réservée aux abonnés.
                        </p>
                        <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                            <a href="{{ route('subscribe') }}" class="gm-btn-primary">S'abonner à partir de 24 000 FCFA/an</a>
                            @guest
                                <a href="{{ route('login') }}" class="gm-meta hover:text-gm-red">Déjà abonné ? Se connecter</a>
                            @endguest
                        </div>
                    </div>
                @endif
            @else
                @if (! empty($freemiumBonus))
                    <div class="mb-6 border border-gm-red bg-gm-red-soft px-4 py-3 not-prose">
                        <span class="font-mono text-[11px] font-bold uppercase tracking-[0.15em] text-gm-red-deep">
                            Article offert
                        </span>
                        <p class="mt-1 text-sm text-gm-charcoal">
                            Vous lisez cet article grâce à votre quota gratuit mensuel.
                            Il vous reste <strong class="text-gm-red-deep">{{ $freemiumRemaining }} article{{ $freemiumRemaining > 1 ? 's' : '' }}</strong> ce mois-ci avant déclenchement du paywall.
                            <a href="{{ route('subscribe') }}" class="underline hover:text-gm-red">Abonnez-vous</a> pour un accès illimité.
                        </p>
                    </div>
                @endif

                {!! $fullHtml !!}
            @endif
        </div>

        {{-- Tags + partage --}}
        <div class="mt-12 flex flex-wrap items-center justify-between gap-6 border-t border-gm-gray-line pt-6">
            @if ($article->tags->isNotEmpty())
                <div class="flex flex-wrap gap-2">
                    @foreach ($article->tags as $tag)
                        <span class="gm-meta border border-gm-gray-line bg-white px-2 py-1">#{{ $tag->name }}</span>
                    @endforeach
                </div>
            @else
                <div></div>
            @endif

            <x-gm.share-buttons :article="$article" />
        </div>

        {{-- Bannière publicitaire — bas de l'article --}}
        <x-gm.ad placement="article_bottom" class="mt-10" />
    </article>

    {{-- Commentaires --}}
    <livewire:public.comment-section :article="$article" />

    {{-- Articles liés --}}
    @if ($related->isNotEmpty())
        <section class="mx-auto max-w-container px-8 py-16">
            <x-gm.section-heading>Sur le même sujet</x-gm.section-heading>
            <div class="grid gap-10 md:grid-cols-3">
                @foreach ($related as $rel)
                    <x-gm.article-card :article="$rel" />
                @endforeach
            </div>
        </section>
    @endif
</x-layouts.public>
