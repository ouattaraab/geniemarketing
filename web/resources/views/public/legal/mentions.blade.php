<x-layouts.public title="Mentions légales — GÉNIE MARKETING Mag">
    <article class="mx-auto max-w-container-narrow px-8 py-16">
        <div class="gm-section-heading flex items-baseline gap-6">
            <span class="font-mono text-xs font-bold tracking-[0.15em] text-gm-red">Informations légales</span>
            <h1 class="font-slab text-3xl font-bold italic leading-none text-gm-ink md:text-5xl">Mentions légales</h1>
        </div>

        <div class="prose prose-lg prose-slate mt-10 max-w-none font-sans text-gm-ink
            [&_h2]:font-slab [&_h2]:italic [&_h2]:text-gm-ink [&_h2]:mt-10
            [&_a]:text-gm-red [&_a]:underline">

            <h2>Éditeur</h2>
            <p>
                <strong>{{ $publisher['editor'] }}</strong><br>
                @if ($publisher['address']) {{ $publisher['address'] }}<br> @endif
                Directeur de la publication : {{ $publisher['director'] }}<br>
                @if ($publisher['email']) Email : <a href="mailto:{{ $publisher['email'] }}">{{ $publisher['email'] }}</a><br> @endif
                @if ($publisher['phone']) Téléphone : {{ $publisher['phone'] }} @endif
            </p>

            <h2>Hébergeur</h2>
            <p>
                L'hébergement du site est assuré par un prestataire professionnel.
                Les coordonnées complètes sont disponibles sur demande auprès de l'éditeur.
            </p>

            <h2>Propriété intellectuelle</h2>
            <p>
                L'ensemble des contenus publiés sur <a href="/">geniemag.ci</a>
                (articles, photographies, illustrations, logo, marques) est protégé par le droit d'auteur.
                Toute reproduction, représentation ou diffusion, intégrale ou partielle, sans autorisation écrite
                préalable de l'éditeur est interdite et constitue une contrefaçon sanctionnée par la loi.
            </p>

            <h2>Responsabilité éditoriale</h2>
            <p>
                GÉNIE MARKETING Mag s'efforce de fournir des informations exactes et à jour.
                Les analyses publiées reflètent les opinions de leurs auteurs et n'engagent pas l'éditeur.
                L'éditeur ne peut être tenu responsable des erreurs, omissions ou des conséquences de l'utilisation des informations.
            </p>

            <h2>Contact</h2>
            <p>
                Pour toute question relative aux présentes mentions légales, vous pouvez nous contacter à :
                @if ($publisher['email']) <a href="mailto:{{ $publisher['email'] }}">{{ $publisher['email'] }}</a>. @endif
            </p>
        </div>

        <footer class="gm-meta mt-12 border-t border-gm-gray-line pt-4">
            Dernière mise à jour : {{ now()->locale('fr')->translatedFormat('j F Y') }}
        </footer>
    </article>
</x-layouts.public>
