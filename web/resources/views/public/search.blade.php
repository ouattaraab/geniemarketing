<x-layouts.public :title="$query !== '' ? 'Recherche : '.$query : 'Recherche'">
    <section class="mx-auto max-w-container px-8 py-16">
        <x-gm.section-heading subtitle="{{ $query !== '' ? $articles->count().' résultat(s)' : 'Qu\'aimeriez-vous trouver ?' }}">
            Recherche
        </x-gm.section-heading>

        <form method="GET" action="{{ route('search') }}" class="mb-10 flex gap-3">
            <input
                type="search"
                name="q"
                value="{{ $query }}"
                minlength="2"
                placeholder="Mots-clés (ex: mobile money, CinetPay, IA…)"
                class="flex-1 border border-gm-gray-line bg-white px-4 py-3 font-sans text-base focus:border-gm-red focus:outline-none"
                autofocus
            />
            <button type="submit" class="gm-btn-primary">Rechercher</button>
        </form>

        @if ($query === '')
            <p class="gm-meta">Tapez au moins 2 caractères pour lancer la recherche.</p>
        @elseif ($articles->isEmpty())
            <div class="border border-dashed border-gm-gray-line bg-gm-cream p-10 text-center">
                <p class="gm-meta">Aucun article ne correspond à votre recherche « {{ $query }} ».</p>
                <p class="mt-3 font-slab text-xl italic text-gm-charcoal">
                    Essayez avec des mots-clés plus généraux, ou explorez nos <a href="{{ route('category.show', 'analyses') }}" class="text-gm-red hover:underline">rubriques</a>.
                </p>
            </div>
        @else
            <div class="grid gap-10 md:grid-cols-2">
                @foreach ($articles as $article)
                    <x-gm.article-card :article="$article" />
                @endforeach
            </div>
        @endif
    </section>
</x-layouts.public>
