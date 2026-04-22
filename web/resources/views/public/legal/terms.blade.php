<x-layouts.public title="Conditions générales d'utilisation — GÉNIE MARKETING Mag">
    <article class="mx-auto max-w-container-narrow px-8 py-16">
        <div class="gm-section-heading flex items-baseline gap-6">
            <span class="font-mono text-xs font-bold tracking-[0.15em] text-gm-red">Informations légales</span>
            <h1 class="font-slab text-3xl font-bold italic leading-none text-gm-ink md:text-5xl">CGU & CGV</h1>
        </div>

        <div class="prose prose-lg prose-slate mt-10 max-w-none font-sans text-gm-ink
            [&_h2]:font-slab [&_h2]:italic [&_h2]:text-gm-ink [&_h2]:mt-10
            [&_a]:text-gm-red [&_a]:underline">

            <h2>1. Objet</h2>
            <p>
                Les présentes Conditions Générales d'Utilisation et de Vente régissent les relations
                entre <strong>{{ $publisher['editor'] }}</strong> (l'éditeur) et tout utilisateur du site
                <a href="/">geniemag.ci</a>.
            </p>

            <h2>2. Accès au service</h2>
            <p>
                Le site propose :
            </p>
            <ul>
                <li>des articles en accès libre ;</li>
                <li>des contenus premium réservés aux abonnés payants (3 formules : Digital, Combo, Entreprise) ;</li>
                <li>un magazine papier et numérique disponible à la vente.</li>
            </ul>

            <h2>3. Formules d'abonnement</h2>
            <p>
                Les tarifs sont indiqués en FCFA TTC sur la page <a href="/abonnement">abonnement</a>.
                L'engagement est d'un an, renouvelable par tacite reconduction (sauf opposition notifiée 30 jours avant échéance).
            </p>

            <h2>4. Paiement</h2>
            <p>
                Les paiements sont traités par <strong>Paystack</strong>,
                prestataire agréé pour le traitement sécurisé des transactions bancaires et Mobile Money.
                Aucune donnée bancaire n'est stockée sur nos serveurs.
            </p>

            <h2>5. Droit de rétractation</h2>
            <p>
                Conformément à la loi ivoirienne, vous disposez de <strong>14 jours</strong> après la souscription
                pour vous rétracter, sauf si vous avez déjà consommé le contenu numérique avec votre accord préalable.
                Contactez-nous à @if ($publisher['email']) <a href="mailto:{{ $publisher['email'] }}">{{ $publisher['email'] }}</a> @else notre service client @endif
                pour exercer ce droit.
            </p>

            <h2>6. Obligations de l'utilisateur</h2>
            <ul>
                <li>ne pas partager ses identifiants de connexion ;</li>
                <li>ne pas reproduire le contenu premium hors cadre privé ;</li>
                <li>respecter la charte de modération lors des commentaires.</li>
            </ul>

            <h2>7. Responsabilité</h2>
            <p>
                L'éditeur s'engage à fournir le service avec diligence.
                Une interruption temporaire pour maintenance est prévue avec préavis de 48h
                et n'ouvre pas droit à indemnisation sauf dépassement du SLA contractuel Entreprise.
            </p>

            <h2>8. Litiges</h2>
            <p>
                Les présentes CGU/CGV sont soumises au droit ivoirien.
                Tout litige relèvera de la compétence exclusive des tribunaux d'Abidjan,
                sauf disposition d'ordre public contraire.
            </p>
        </div>

        <footer class="gm-meta mt-12 border-t border-gm-gray-line pt-4">
            Dernière mise à jour : {{ now()->locale('fr')->translatedFormat('j F Y') }}
        </footer>
    </article>
</x-layouts.public>
