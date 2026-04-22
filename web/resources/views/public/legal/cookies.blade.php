<x-layouts.public title="Politique cookies — GÉNIE MARKETING Mag">
    <article class="mx-auto max-w-container-narrow px-8 py-16">
        <div class="gm-section-heading flex items-baseline gap-6">
            <span class="font-mono text-xs font-bold tracking-[0.15em] text-gm-red">Informations légales</span>
            <h1 class="font-slab text-3xl font-bold italic leading-none text-gm-ink md:text-5xl">Politique cookies</h1>
        </div>

        <div class="prose prose-lg prose-slate mt-10 max-w-none font-sans text-gm-ink
            [&_h2]:font-slab [&_h2]:italic [&_h2]:text-gm-ink [&_h2]:mt-10
            [&_a]:text-gm-red [&_a]:underline">

            <p>
                Un cookie est un petit fichier déposé sur votre navigateur pour mémoriser votre préférence,
                une session d'authentification, ou mesurer la fréquentation du site.
                Nous appliquons les recommandations de la CNIL et de l'ARTCI :
                les cookies non-essentiels ne sont jamais déposés sans votre accord explicite.
            </p>

            <h2>Types de cookies utilisés</h2>

            <h3>Essentiels (obligatoires — exemption consentement)</h3>
            <ul>
                <li><code>laravel_session</code> — session utilisateur (30 jours glissants)</li>
                <li><code>XSRF-TOKEN</code> — protection contre les attaques CSRF</li>
                <li><code>gm.consent</code> (localStorage) — mémorise vos choix de cookies</li>
            </ul>

            <h3>Mesure d'audience (soumis à consentement)</h3>
            <p>
                Si vous l'acceptez, nous utilisons <strong>Matomo</strong> (analytique self-hosted)
                pour compter les pages vues et comprendre les parcours,
                avec IP anonymisée et sans transfert hors Afrique/UE.
            </p>

            <h3>Marketing (soumis à consentement)</h3>
            <p>
                Uniquement si vous l'acceptez : cookies de personnalisation des offres
                (bannière promo ciblée, rappel panier d'abonnement abandonné).
                Pas de cookies tiers de régie publicitaire.
            </p>

            <h2>Modifier vos choix</h2>
            <p>
                Vous pouvez à tout moment modifier vos choix en cliquant sur le bouton ci-dessous :
            </p>
            <p>
                <button
                    x-data
                    @click="localStorage.removeItem('gm.consent'); location.reload()"
                    class="gm-btn-primary"
                >Réafficher le bandeau cookies</button>
            </p>

            <h2>Contact</h2>
            <p>
                Questions sur les cookies ? Notre DPO répond sous 48h ouvrées à
                @if ($publisher['dpo_email'])
                    <a href="mailto:{{ $publisher['dpo_email'] }}">{{ $publisher['dpo_email'] }}</a>.
                @else
                    <em>l'adresse DPO indiquée en page confidentialité.</em>
                @endif
            </p>
        </div>

        <footer class="gm-meta mt-12 border-t border-gm-gray-line pt-4">
            Dernière mise à jour : {{ now()->locale('fr')->translatedFormat('j F Y') }}
        </footer>
    </article>
</x-layouts.public>
