@props(['title'])

<x-layouts.public :title="$title.' — GÉNIE MARKETING Mag'" :description="'Page légale : '.$title">
    <article class="mx-auto max-w-container-narrow px-8 py-16">
        <div class="gm-section-heading flex items-baseline gap-6">
            <span class="font-mono text-xs font-bold tracking-[0.15em] text-gm-red">Informations légales</span>
            <h1 class="font-slab text-3xl font-bold italic leading-none text-gm-ink md:text-5xl">{{ $title }}</h1>
        </div>

        <div class="prose prose-lg prose-slate mt-10 max-w-none font-sans text-gm-ink
            [&_h2]:font-slab [&_h2]:italic [&_h2]:text-gm-ink [&_h2]:mt-10
            [&_a]:text-gm-red [&_a]:underline">
            {{ $slot }}
        </div>

        <footer class="gm-meta mt-12 border-t border-gm-gray-line pt-4">
            Dernière mise à jour : {{ now()->locale('fr')->translatedFormat('j F Y') }}
        </footer>
    </article>
</x-layouts.public>
