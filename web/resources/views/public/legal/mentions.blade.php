<x-layouts.public title="Mentions légales — GÉNIE MARKETING Mag">
    <article class="mx-auto max-w-container-narrow px-8 py-16">
        <div class="gm-section-heading flex items-baseline gap-6">
            <span class="font-mono text-xs font-bold tracking-[0.15em] text-gm-red">Informations légales</span>
            <h1 class="font-slab text-3xl font-bold italic leading-none text-gm-ink md:text-5xl">Mentions légales</h1>
        </div>

        <div class="prose prose-lg prose-slate mt-10 max-w-none font-sans text-gm-ink
            [&_h2]:font-slab [&_h2]:italic [&_h2]:text-gm-ink [&_h2]:mt-10
            [&_h3]:font-slab [&_h3]:italic [&_h3]:text-gm-ink [&_h3]:mt-6
            [&_a]:text-gm-red [&_a]:underline">

            <p class="lead">
                Conformément aux dispositions de la <strong>loi ivoirienne n° 2013-451 du 19 juin 2013 relative
                à la lutte contre la cybercriminalité</strong>, de la <strong>loi n° 2017-867 du 27 décembre 2017
                portant régime juridique de la presse</strong> et des textes en vigueur en Côte d'Ivoire,
                nous fournissons aux visiteurs du site <a href="/">geniemag.ci</a> les informations légales suivantes.
            </p>

            <h2>1. Éditeur du site</h2>
            <p>
                Le site <a href="/">geniemag.ci</a> est édité par :
            </p>
            <ul>
                <li><strong>Raison sociale :</strong> {{ $publisher['editor'] }}</li>
                @if ($publisher['editor_form'])
                    <li><strong>Forme juridique :</strong> {{ $publisher['editor_form'] }}</li>
                @endif
                @if ($publisher['editor_capital'])
                    <li><strong>Capital social :</strong> {{ $publisher['editor_capital'] }}</li>
                @endif
                @if ($publisher['address'])
                    <li><strong>Siège social :</strong> {{ $publisher['address'] }}</li>
                @endif
                @if ($publisher['editor_rccm'])
                    <li><strong>RCCM :</strong> {{ $publisher['editor_rccm'] }}</li>
                @endif
                @if ($publisher['editor_nif'])
                    <li><strong>NIF (Numéro d'Identification Fiscale) :</strong> {{ $publisher['editor_nif'] }}</li>
                @endif
                @if ($publisher['editor_cc'])
                    <li><strong>Compte Contribuable :</strong> {{ $publisher['editor_cc'] }}</li>
                @endif
                @if ($publisher['phone'])
                    <li><strong>Téléphone :</strong> {{ $publisher['phone'] }}</li>
                @endif
                @if ($publisher['email'])
                    <li><strong>Email :</strong> <a href="mailto:{{ $publisher['email'] }}">{{ $publisher['email'] }}</a></li>
                @endif
            </ul>

            <h2>2. Directeur de la publication</h2>
            <p>
                Conformément à l'article 27 de la loi n° 2017-867 du 27 décembre 2017, le directeur
                de la publication, responsable au regard de la loi sur la presse, est :
                <strong>{{ $publisher['director'] ?: '— (à renseigner)' }}</strong>.
            </p>
            @if ($publisher['cppap'] && $publisher['cppap'] !== '—')
                <p>
                    <strong>Numéro d'enregistrement CNPCI :</strong> {{ $publisher['cppap'] }}
                    (Conseil National de la Presse de Côte d'Ivoire).
                </p>
            @endif

            <h2>3. Hébergeur du site</h2>
            <p>
                L'hébergement du site est assuré par :
            </p>
            <ul>
                <li><strong>{{ $publisher['host_name'] ?: '— (à renseigner dans l\'administration)' }}</strong></li>
                @if ($publisher['host_address'])
                    <li>Adresse : {{ $publisher['host_address'] }}</li>
                @endif
                @if ($publisher['host_url'])
                    <li>Site : <a href="{{ $publisher['host_url'] }}" target="_blank" rel="noopener noreferrer">{{ $publisher['host_url'] }}</a></li>
                @endif
                @if ($publisher['host_phone'])
                    <li>Téléphone : {{ $publisher['host_phone'] }}</li>
                @endif
            </ul>

            <h2>4. Objet du site</h2>
            <p>
                <strong>GÉNIE MARKETING Mag</strong> est un magazine numérique et papier spécialisé dans
                l'analyse du marketing, de la communication et des transformations économiques en
                Côte d'Ivoire et dans la zone UEMOA. Le site propose des articles en libre accès,
                des contenus premium réservés aux abonnés, des éditions PDF du magazine, une newsletter
                gratuite et des formules d'abonnement pour particuliers et entreprises.
            </p>

            <h2>5. Propriété intellectuelle</h2>
            <p>
                L'ensemble des éléments du site <a href="/">geniemag.ci</a> (articles, photographies,
                illustrations, vidéos, graphismes, logos, marques, bases de données, code source, design)
                est la propriété exclusive de {{ $publisher['editor'] }} ou de ses partenaires,
                et est protégé par :
            </p>
            <ul>
                <li>la <strong>loi ivoirienne n° 2016-555 du 26 juillet 2016 relative au droit d'auteur et aux droits voisins</strong> ;</li>
                <li>l'<strong>Accord de Bangui du 2 mars 1977 révisé</strong> portant création de l'OAPI ;</li>
                <li>les conventions internationales applicables en matière de propriété intellectuelle.</li>
            </ul>
            <p>
                Toute reproduction, représentation, diffusion ou exploitation, intégrale ou partielle,
                sans autorisation écrite préalable de l'éditeur est interdite et constitue une
                <strong>contrefaçon</strong> sanctionnée par les articles du Code pénal ivoirien et de la loi
                précitée. Les abonnés bénéficient d'une licence d'usage strictement personnelle et non
                transmissible des contenus premium (voir <a href="{{ route('legal.terms') }}">CGU/CGV</a>).
            </p>

            <h2>6. Responsabilité éditoriale</h2>
            <p>
                {{ $publisher['editor'] }} met en œuvre des moyens raisonnables pour fournir sur son site
                des informations fiables et à jour. Toutefois, l'éditeur ne peut garantir l'exactitude,
                la précision ou l'exhaustivité des informations mises à disposition. En conséquence,
                l'éditeur décline toute responsabilité :
            </p>
            <ul>
                <li>pour les imprécisions, inexactitudes ou omissions portant sur des informations disponibles sur le site ;</li>
                <li>pour les dommages résultant d'une intrusion frauduleuse d'un tiers ayant entraîné une modification des informations mises à disposition sur le site ;</li>
                <li>et plus généralement, pour tout dommage direct ou indirect, quelles qu'en soient les causes, origines, nature ou conséquences.</li>
            </ul>
            <p>
                Les tribunes et analyses publiées par des contributeurs tiers n'engagent que leurs auteurs.
                Le droit de réponse est garanti conformément à l'article 68 de la loi 2017-867 — les
                demandes doivent être adressées au Directeur de la publication par tout moyen
                assurant la preuve de leur réception.
            </p>

            <h2>7. Liens hypertextes</h2>
            <p>
                Le site peut contenir des liens hypertextes vers d'autres sites. {{ $publisher['editor'] }}
                n'exerce aucun contrôle sur ces sites et décline toute responsabilité quant à leur contenu,
                leur disponibilité, leurs pratiques commerciales ou leur politique de protection des données.
                La création de liens vers geniemag.ci est libre, à condition qu'elle ne porte pas atteinte
                à la réputation de l'éditeur et qu'elle soit supprimée sur simple demande.
            </p>

            <h2>8. Signalement de contenu illicite</h2>
            <p>
                En application de la <strong>loi n° 2013-451 sur la lutte contre la cybercriminalité</strong>,
                tout contenu manifestement illicite (incitation à la haine, apologie du terrorisme, atteinte
                aux mineurs, diffamation, contrefaçon) peut être signalé à l'adresse
                @if ($publisher['email']) <a href="mailto:{{ $publisher['email'] }}">{{ $publisher['email'] }}</a> @endif
                ou via la <a href="https://www.cybercrime.interieur.gouv.ci" target="_blank" rel="noopener noreferrer">Plateforme de Lutte contre la Cybercriminalité (PLCC)</a>
                du Ministère de l'Intérieur. Tout signalement abusif est passible de sanctions.
            </p>

            <h2>9. Protection des données personnelles</h2>
            <p>
                Le traitement des données personnelles recueillies par le site est décrit dans notre
                <a href="{{ route('legal.privacy') }}">Politique de confidentialité</a>, établie
                conformément à la <strong>loi ivoirienne n° 2013-450</strong> et au <strong>RGPD</strong>.
                Pour toute question relative à la protection des données, contactez notre DPO :
                @if ($publisher['dpo_email'])
                    <a href="mailto:{{ $publisher['dpo_email'] }}">{{ $publisher['dpo_email'] }}</a>.
                @endif
            </p>

            <h2>10. Cookies</h2>
            <p>
                Consultez notre <a href="{{ route('legal.cookies') }}">Politique cookies</a> pour connaître
                les traceurs utilisés et les options de consentement.
            </p>

            <h2>11. Crédits</h2>
            <p>
                Conception, développement et direction artistique : équipes internes de
                {{ $publisher['editor'] }}. Photographies : contributeurs, banques d'images sous licence,
                crédits indiqués sous chaque cliché ou dans l'ours du magazine.
            </p>

            <h2>12. Droit applicable</h2>
            <p>
                Les présentes mentions légales sont soumises au droit ivoirien. Tout litige y relatif
                relèvera de la compétence exclusive des tribunaux d'Abidjan.
            </p>
        </div>

        <footer class="gm-meta mt-12 border-t border-gm-gray-line pt-4">
            Dernière mise à jour : {{ $updatedAt }}.
        </footer>
    </article>
</x-layouts.public>
