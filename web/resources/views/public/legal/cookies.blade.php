<x-layouts.public title="Politique cookies — GÉNIE MARKETING Mag">
    <article class="mx-auto max-w-container-narrow px-8 py-16">
        <div class="gm-section-heading flex items-baseline gap-6">
            <span class="font-mono text-xs font-bold tracking-[0.15em] text-gm-red">Informations légales</span>
            <h1 class="font-slab text-3xl font-bold italic leading-none text-gm-ink md:text-5xl">Politique cookies</h1>
        </div>

        <div class="prose prose-lg prose-slate mt-10 max-w-none font-sans text-gm-ink
            [&_h2]:font-slab [&_h2]:italic [&_h2]:text-gm-ink [&_h2]:mt-10
            [&_h3]:font-slab [&_h3]:italic [&_h3]:text-gm-ink [&_h3]:mt-6
            [&_a]:text-gm-red [&_a]:underline
            [&_table]:w-full [&_table]:border-collapse [&_td]:border [&_td]:border-gm-gray-line [&_td]:p-2 [&_th]:border [&_th]:border-gm-gray-line [&_th]:p-2 [&_th]:bg-gm-paper [&_th]:text-left">

            <p class="lead">
                Un <strong>cookie</strong> (ou traceur) est un petit fichier déposé sur votre terminal lors de votre
                visite. Il permet de mémoriser une préférence, une session d'authentification, ou de mesurer
                la fréquentation du site. La présente politique précise les traceurs utilisés sur
                <a href="/">geniemag.ci</a> et la manière dont vous en contrôlez l'usage.
            </p>
            <p>
                Nous appliquons les recommandations de la <strong>CNIL</strong> et les lignes directrices de
                l'<strong>ARTCI</strong> : les cookies non essentiels ne sont déposés qu'après obtention de
                votre <strong>consentement explicite</strong>, manifesté par un acte positif (clic sur « Accepter »
                ou paramétrage fin). Refuser est aussi simple qu'accepter.
            </p>

            <h2>1. Catégories de cookies utilisés</h2>

            <h3>1.1 Cookies strictement nécessaires (exemption de consentement)</h3>
            <p>
                Ces cookies sont indispensables au fonctionnement du site et ne nécessitent pas de
                consentement au sens de l'article 82 de la loi Informatique &amp; Libertés et des lignes
                directrices CNIL de 2020.
            </p>
            <table>
                <thead>
                    <tr><th>Nom</th><th>Finalité</th><th>Durée</th><th>Émetteur</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>gm-api-session</code></td>
                        <td>Session utilisateur authentifiée, gestion du panier</td>
                        <td>30 jours glissants</td>
                        <td>Site (première partie)</td>
                    </tr>
                    <tr>
                        <td><code>XSRF-TOKEN</code></td>
                        <td>Protection contre les attaques CSRF</td>
                        <td>Session</td>
                        <td>Site</td>
                    </tr>
                    <tr>
                        <td><code>gm.consent</code> (localStorage)</td>
                        <td>Mémoriser votre choix de consentement cookies</td>
                        <td>13 mois</td>
                        <td>Site</td>
                    </tr>
                    <tr>
                        <td><code>2fa_verified_at</code> (session)</td>
                        <td>Mémoriser la validation de double authentification en cours de session</td>
                        <td>Session</td>
                        <td>Site</td>
                    </tr>
                </tbody>
            </table>

            <h3>1.2 Cookies de mesure d'audience (soumis à consentement)</h3>
            <p>
                Si vous l'acceptez, nous utilisons une solution d'analytique hébergée par nos soins
                (<strong>Matomo</strong> ou équivalent) pour comprendre comment le site est utilisé — pages vues,
                parcours de lecture, durée des sessions. L'IP est <strong>anonymisée</strong> (dernier octet
                tronqué), aucun transfert hors UE/Afrique n'a lieu, et aucun recoupement avec les bases
                marketing n'est effectué.
            </p>
            <table>
                <thead>
                    <tr><th>Nom</th><th>Finalité</th><th>Durée</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>_pk_id*</code></td>
                        <td>Identifiant visiteur anonyme pour la mesure d'audience</td>
                        <td>13 mois</td>
                    </tr>
                    <tr>
                        <td><code>_pk_ses*</code></td>
                        <td>Visite en cours</td>
                        <td>30 minutes</td>
                    </tr>
                </tbody>
            </table>

            <h3>1.3 Cookies de personnalisation et marketing (soumis à consentement)</h3>
            <p>
                Uniquement si vous l'acceptez : cookies internes permettant de proposer une bannière
                promotionnelle ciblée, un rappel de panier d'abonnement abandonné, ou l'affichage de
                newsletters thématiques pertinentes. <strong>Aucun cookie tiers</strong> de régie publicitaire,
                de reciblage (retargeting) ou de réseau social n'est déposé par défaut.
            </p>

            <h3>1.4 Réseaux sociaux embarqués</h3>
            <p>
                Nous n'intégrons pas nativement les boutons de partage « social plugins » (Like, Tweet,
                etc.) qui pisteraient vos visites. Lorsque vous cliquez sur nos liens de partage,
                l'ouverture d'une fenêtre du réseau concerné relève de la politique dudit réseau — nous
                vous invitons à la consulter.
            </p>

            <h2>2. Vos choix</h2>
            <p>
                Lors de votre première visite, un bandeau vous permet :
            </p>
            <ul>
                <li>d'<strong>accepter</strong> tous les cookies ;</li>
                <li>de <strong>refuser</strong> tous les cookies non essentiels (choix conservé 13 mois) ;</li>
                <li>de <strong>personnaliser</strong> votre consentement par catégorie.</li>
            </ul>
            <p>
                Votre choix est mémorisé 13 mois. Vous pouvez le modifier à tout moment en cliquant sur
                le bouton ci-dessous ou en réinitialisant les cookies via votre navigateur :
            </p>
            <p>
                <button
                    x-data
                    @click="localStorage.removeItem('gm.consent'); location.reload()"
                    class="gm-btn-primary"
                >Réafficher le bandeau cookies</button>
            </p>

            <h2>3. Contrôle via votre navigateur</h2>
            <p>
                Vous pouvez configurer votre navigateur pour accepter, rejeter ou supprimer les cookies :
            </p>
            <ul>
                <li><a href="https://support.google.com/chrome/answer/95647" target="_blank" rel="noopener noreferrer">Chrome</a> ;</li>
                <li><a href="https://support.mozilla.org/fr/kb/empecher-sites-web-enregistrer-cookies-preferences" target="_blank" rel="noopener noreferrer">Firefox</a> ;</li>
                <li><a href="https://support.apple.com/fr-fr/guide/safari/sfri11471/mac" target="_blank" rel="noopener noreferrer">Safari</a> ;</li>
                <li><a href="https://support.microsoft.com/fr-fr/microsoft-edge" target="_blank" rel="noopener noreferrer">Edge</a>.</li>
            </ul>
            <p>
                <strong>Attention :</strong> le blocage de tous les cookies, y compris les cookies strictement
                nécessaires, pourrait empêcher certaines fonctionnalités du site (authentification,
                panier, paiement) de fonctionner correctement.
            </p>

            <h2>4. Signal « Do Not Track » (DNT) et Global Privacy Control</h2>
            <p>
                Nous respectons le signal <strong>GPC (Global Privacy Control)</strong> : lorsqu'il est émis par
                votre navigateur, nous le traitons comme un refus des cookies de mesure d'audience et
                marketing, même en absence d'action explicite sur le bandeau.
            </p>

            <h2>5. Contact</h2>
            <p>
                Toute question relative à notre politique cookies peut être adressée à notre DPO :
                @if ($publisher['dpo_email'])
                    <a href="mailto:{{ $publisher['dpo_email'] }}">{{ $publisher['dpo_email'] }}</a>
                @else
                    <em>coordonnées fournies en page confidentialité.</em>
                @endif
                Un accusé de réception est envoyé sous 48 heures ouvrées.
            </p>
        </div>

        <footer class="gm-meta mt-12 border-t border-gm-gray-line pt-4">
            Dernière mise à jour : {{ $updatedAt }}.
        </footer>
    </article>
</x-layouts.public>
