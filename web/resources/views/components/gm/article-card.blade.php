@props([
    'article',
    'variant' => 'default', // default | large | compact
])

@php
    // Normalise : accepte soit un array, soit un modèle Article.
    $data = $article instanceof \App\Models\Article
        ? [
            'title' => $article->title,
            'excerpt' => $article->lede,
            'category' => $article->editorialCategory?->name ?? $article->category?->name ?? 'Article',
            'author' => $article->authors->pluck('name')->join(', '),
            'reading_time' => $article->reading_time_minutes ?? 5,
            'cover_url' => $article->cover?->url(),
            'url' => route('article.show', $article),
            'is_premium' => $article->access_level !== \App\Enums\ArticleAccessLevel::Free,
        ]
        : $article;

    $sizes = match ($variant) {
        'large' => ['grid' => 'md:grid-cols-2', 'title' => 'text-3xl md:text-4xl', 'aspect' => 'aspect-[4/3]'],
        'compact' => ['grid' => 'md:grid-cols-[1fr_2fr]', 'title' => 'text-lg md:text-xl', 'aspect' => 'aspect-[4/3]'],
        default => ['grid' => '', 'title' => 'text-xl md:text-2xl', 'aspect' => 'aspect-[16/9]'],
    };
@endphp

<article class="group grid gap-6 {{ $sizes['grid'] }}">
    <a href="{{ $data['url'] ?? '#' }}" class="block overflow-hidden">
        <div class="{{ $sizes['aspect'] }} overflow-hidden border border-gm-gray-line bg-gm-cream">
            @if (! empty($data['cover_url']))
                <img
                    src="{{ $data['cover_url'] }}"
                    alt=""
                    class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                    loading="lazy"
                />
            @endif
        </div>
    </a>

    <div class="flex flex-col justify-center">
        <div class="mb-3 flex items-center gap-3">
            <span class="font-mono text-[11px] font-bold uppercase tracking-[0.15em] text-gm-red">
                {{ $data['category'] ?? 'Article' }}
            </span>
            @if (! empty($data['is_premium']))
                <span class="gm-meta border border-gm-ink bg-gm-ink px-1.5 py-0.5 text-white">Premium</span>
            @endif
        </div>

        <h3 class="font-slab font-bold leading-tight text-gm-ink {{ $sizes['title'] }}">
            <a href="{{ $data['url'] ?? '#' }}" class="hover:text-gm-red transition-colors">
                {{ $data['title'] }}
            </a>
        </h3>

        @if (! empty($data['excerpt']))
            <p class="mt-3 text-base leading-relaxed text-gm-charcoal">
                {{ $data['excerpt'] }}
            </p>
        @endif

        <div class="gm-meta mt-4 flex items-center gap-3">
            @if (! empty($data['author']))
                <span>Par {{ $data['author'] }}</span>
                <span>·</span>
            @endif
            <span>{{ $data['reading_time'] ?? 5 }} min de lecture</span>
        </div>
    </div>
</article>
