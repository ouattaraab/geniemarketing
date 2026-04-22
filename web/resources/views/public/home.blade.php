<x-layouts.public title="GÉNIE MARKETING Mag — La Une">
    {{-- Hero La Une --}}
    <section class="mx-auto max-w-container px-8 py-16">
        <x-gm.section-heading number="01" subtitle="Mise à jour {{ \Carbon\Carbon::now()->locale('fr')->translatedFormat('l j F') }}">
            La Une
        </x-gm.section-heading>

        @if ($hero)
            <x-gm.article-card :article="$hero" variant="large" />
        @else
            <div class="border border-dashed border-gm-gray-line bg-gm-cream p-10 text-center">
                <p class="gm-meta">Aucun article publié pour l'instant.</p>
                <p class="mt-3 font-slab text-xl italic text-gm-charcoal">
                    La rédaction prépare les premiers articles — ils arrivent très bientôt.
                </p>
            </div>
        @endif
    </section>

    {{-- Derniers articles --}}
    @if ($latest->isNotEmpty())
        <section class="mx-auto max-w-container px-8 py-10">
            <x-gm.section-heading number="02">
                Derniers articles
            </x-gm.section-heading>

            <div class="grid gap-10 md:grid-cols-2">
                @foreach ($latest as $article)
                    <x-gm.article-card :article="$article" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- CTA Newsletter --}}
    <section class="mx-auto max-w-container px-8 py-16">
        <div class="border-y-2 border-gm-ink bg-white px-8 py-12 md:px-16">
            <div class="grid items-center gap-8 md:grid-cols-[2fr_1fr]">
                <div>
                    <span class="font-mono text-[11px] font-bold uppercase tracking-[0.15em] text-gm-red">
                        Newsletter — Chaque mardi
                    </span>
                    <h3 class="mt-3 font-slab text-3xl font-bold italic leading-tight text-gm-ink md:text-4xl">
                        L'essentiel du marketing ivoirien, dans votre boîte mail.
                    </h3>
                    <p class="mt-3 text-gm-charcoal">
                        Abonnez-vous gratuitement pour recevoir notre sélection hebdomadaire d'analyses et d'études de cas.
                    </p>
                </div>
                <form class="flex flex-col gap-3" method="POST" action="{{ route('newsletter.subscribe') }}">
                    @csrf
                    <input type="hidden" name="newsletter" value="hebdo-public" />
                    <input type="hidden" name="source" value="home" />
                    <input
                        type="email"
                        name="email"
                        placeholder="votre@email.ci"
                        required
                        value="{{ old('email') }}"
                        class="border border-gm-gray-line bg-gm-paper px-4 py-3 font-sans text-sm focus:border-gm-red focus:outline-none"
                    />
                    @error('email')<span class="gm-meta text-gm-red">{{ $message }}</span>@enderror
                    <button type="submit" class="gm-btn-primary">
                        Je m'inscris
                    </button>
                </form>
            </div>

            @if (session('newsletter-status'))
                <div class="mt-6 border-t border-gm-gray-line pt-4 text-center gm-meta normal-case tracking-normal text-gm-ink">
                    {{ session('newsletter-status') }}
                </div>
            @endif
        </div>
    </section>
</x-layouts.public>
