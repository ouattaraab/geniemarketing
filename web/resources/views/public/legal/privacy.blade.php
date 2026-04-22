<x-layouts.public title="Politique de confidentialité — GÉNIE MARKETING Mag">
    <article class="mx-auto max-w-container-narrow px-8 py-16">
        <div class="gm-section-heading flex items-baseline gap-6">
            <span class="font-mono text-xs font-bold tracking-[0.15em] text-gm-red">Informations légales</span>
            <h1 class="font-slab text-3xl font-bold italic leading-none text-gm-ink md:text-5xl">Politique de confidentialité</h1>
        </div>

        <div class="prose prose-lg prose-slate mt-10 max-w-none font-sans text-gm-ink
            [&_h2]:font-slab [&_h2]:italic [&_h2]:text-gm-ink [&_h2]:mt-10
            [&_a]:text-gm-red [&_a]:underline">

            <p>
                La présente politique décrit comment GÉNIE MARKETING Mag collecte, utilise et protège vos données à caractère personnel,
                conformément au <strong>Règlement Général sur la Protection des Données (RGPD)</strong> et à la
                <strong>Loi n° 2013-450 du 19 juin 2013 de Côte d'Ivoire</strong> relative à la protection des données à caractère personnel.
            </p>

            <h2>Données collectées</h2>
            <ul>
                <li><strong>Compte</strong> : prénom, nom, email, téléphone, préférences éditoriales</li>
                <li><strong>Paiement</strong> : détails traités par Paystack — nous ne stockons jamais les numéros de carte</li>
                <li><strong>Navigation</strong> : IP, user-agent, articles lus, durée de session</li>
                <li><strong>Newsletter</strong> : email uniquement, avec double opt-in</li>
            </ul>

            <h2>Finalités</h2>
            <ul>
                <li>Fourniture du service éditorial et de l'abonnement</li>
                <li>Facturation et obligations comptables (rétention 10 ans)</li>
                <li>Sécurité : détection d'abus, audit, traçabilité</li>
                <li>Information éditoriale et communications commerciales (avec votre consentement)</li>
            </ul>

            <h2>Base légale</h2>
            <p>
                L'exécution du contrat d'abonnement justifie la majorité des traitements.
                La newsletter et les cookies de mesure d'audience sont soumis à votre consentement explicite,
                révocable à tout moment.
            </p>

            <h2>Durée de conservation</h2>
            <ul>
                <li>Compte et abonnement : pendant toute la durée du contrat + 3 ans (preuve)</li>
                <li>Factures : 10 ans (obligation comptable)</li>
                <li>Journaux d'audit : 12 mois</li>
                <li>Newsletter : tant que vous êtes inscrit, + 3 ans pour prouver le consentement</li>
            </ul>

            <h2>Vos droits</h2>
            <p>
                Vous disposez des droits d'accès, de rectification, d'effacement, de limitation, de portabilité et d'opposition.
                Exercez-les auprès de notre DPO :
                @if ($publisher['dpo_email'])
                    <a href="mailto:{{ $publisher['dpo_email'] }}">{{ $publisher['dpo_email'] }}</a>.
                @else
                    <em>coordonnées sur demande.</em>
                @endif
                En cas de réponse insatisfaisante, vous pouvez saisir l'ARTCI (Autorité de Régulation des Télécommunications de Côte d'Ivoire).
            </p>

            <h2>Sécurité</h2>
            <p>
                Toutes les communications sont chiffrées en TLS 1.3.
                Les mots de passe sont hachés avec bcrypt/argon2.
                L'accès aux données est cloisonné par rôle et tracé dans un journal d'audit.
            </p>

            <h2>Sous-traitants</h2>
            <ul>
                <li>Paystack (traitement des paiements)</li>
                <li>SendGrid / Mailjet (envoi d'emails transactionnels et marketing)</li>
                <li>AWS S3 ou Scaleway (stockage des médias et sauvegardes)</li>
                <li>Cloudflare (CDN et protection DDoS)</li>
            </ul>
        </div>

        <footer class="gm-meta mt-12 border-t border-gm-gray-line pt-4">
            Dernière mise à jour : {{ now()->locale('fr')->translatedFormat('j F Y') }}
        </footer>
    </article>
</x-layouts.public>
