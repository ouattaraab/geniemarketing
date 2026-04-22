<x-layouts.public title="Magazine — GÉNIE MARKETING Mag">
    <section class="mx-auto max-w-container px-8 py-16">
        <x-gm.section-heading number="06" subtitle="Tous les numéros · papier & PDF">
            Magazine
        </x-gm.section-heading>

        @if ($issues->isEmpty())
            <div class="border border-dashed border-gm-gray-line bg-gm-cream p-10 text-center">
                <p class="gm-meta">Aucun numéro publié pour l'instant.</p>
                <p class="mt-3 font-slab text-xl italic text-gm-charcoal">
                    Le premier numéro arrive bientôt — restez connectés.
                </p>
            </div>
        @else
            <div class="grid gap-8 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                @foreach ($issues as $issue)
                    <article class="flex flex-col border border-gm-gray-line bg-white">
                        <a href="#" class="block aspect-[3/4] overflow-hidden bg-gm-cream">
                            @if ($issue->cover)
                                <img src="{{ $issue->cover->url() }}" alt="Couverture du numéro {{ $issue->number }}" class="h-full w-full object-cover transition-transform duration-500 hover:scale-105" loading="lazy" />
                            @else
                                <div class="flex h-full items-center justify-center">
                                    <span class="font-slab text-6xl italic text-gm-red">#{{ $issue->number }}</span>
                                </div>
                            @endif
                        </a>
                        <div class="flex flex-1 flex-col p-4">
                            <span class="gm-meta">N° {{ $issue->number }} · {{ $issue->publication_date->locale('fr')->translatedFormat('M Y') }}</span>
                            <h3 class="mt-2 font-slab text-xl font-bold italic text-gm-ink">
                                {{ $issue->title }}
                            </h3>
                            @if ($issue->theme)
                                <p class="mt-2 text-sm text-gm-charcoal line-clamp-3">{{ $issue->theme }}</p>
                            @endif
                            <div class="mt-4 flex items-center gap-2 text-sm">
                                @if ($issue->price_paper_cents)
                                    <span class="gm-meta">Papier <strong class="text-gm-ink">{{ number_format($issue->price_paper_cents / 100, 0, ',', ' ') }} FCFA</strong></span>
                                @endif
                                @if ($issue->price_pdf_cents)
                                    <span class="gm-meta">· PDF <strong class="text-gm-ink">{{ number_format($issue->price_pdf_cents / 100, 0, ',', ' ') }} FCFA</strong></span>
                                @endif
                            </div>
                            @if ($issue->hasPdf())
                                <a href="{{ route('magazine.reader', $issue) }}" class="gm-btn-primary mt-4 text-center">Lire en ligne</a>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            @if ($issues->hasPages())
                <div class="mt-10">{{ $issues->links() }}</div>
            @endif
        @endif
    </section>
</x-layouts.public>
