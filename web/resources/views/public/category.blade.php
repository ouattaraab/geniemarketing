<x-layouts.public :title="'Rubrique ' . $categoryName . ' — GÉNIE MARKETING Mag'">
    <section class="mx-auto max-w-container px-8 py-16">
        <x-gm.section-heading :subtitle="$tagline">
            {{ $categoryName }}
        </x-gm.section-heading>

        @if ($articles->isEmpty())
            <div class="border border-dashed border-gm-gray-line bg-gm-cream p-10 text-center">
                <p class="gm-meta">Aucun article publié dans cette rubrique pour l'instant.</p>
                <p class="mt-3 font-slab text-xl italic text-gm-charcoal">Revenez bientôt — la rédaction y travaille.</p>
            </div>
        @else
            <div class="grid gap-10 md:grid-cols-2">
                @foreach ($articles as $article)
                    <x-gm.article-card :article="$article" />
                @endforeach
            </div>

            @if ($articles->hasPages())
                <div class="mt-10">
                    {{ $articles->withQueryString()->links() }}
                </div>
            @endif
        @endif
    </section>
</x-layouts.public>
