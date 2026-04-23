<x-layouts.public title="Conditions générales d'utilisation et de vente — GÉNIE MARKETING Mag">
    <article class="mx-auto max-w-container-narrow px-8 py-16">
        <div class="gm-section-heading flex items-baseline gap-6">
            <span class="font-mono text-xs font-bold tracking-[0.15em] text-gm-red">Informations légales</span>
            <h1 class="font-slab text-3xl font-bold italic leading-none text-gm-ink md:text-5xl">CGU & CGV</h1>
        </div>

        <div class="prose prose-lg prose-slate mt-10 max-w-none font-sans text-gm-ink
            [&_h2]:font-slab [&_h2]:italic [&_h2]:text-gm-ink [&_h2]:mt-10
            [&_h3]:font-slab [&_h3]:italic [&_h3]:text-gm-ink [&_h3]:mt-6
            [&_a]:text-gm-red [&_a]:underline">

            <p class="lead">
                Les présentes Conditions Générales d'Utilisation et de Vente (ci-après « <strong>CGU/CGV</strong> »)
                régissent les relations entre <strong>{{ $publisher['editor'] }}</strong>, société
                {{ $publisher['editor_form'] ?: 'commerciale' }} éditrice du site
                <a href="/">geniemag.ci</a> (ci-après « <strong>l'Éditeur</strong> » ou « <strong>GÉNIE MARKETING Mag</strong> »),
                et toute personne physique ou morale accédant au site ou souscrivant à un abonnement
                (ci-après « <strong>l'Utilisateur</strong> » ou « <strong>l'Abonné</strong> »).
            </p>

            <h2>Article 1 — Objet et champ d'application</h2>
            <p>
                Les présentes CGU/CGV définissent les conditions d'accès, d'utilisation et de souscription
                aux services proposés sur geniemag.ci, à savoir :
            </p>
            <ul>
                <li>la consultation d'articles et contenus éditoriaux (accès libre ou premium) ;</li>
                <li>la souscription à des formules d'abonnement numérique et/ou papier ;</li>
                <li>l'inscription à la newsletter ;</li>
                <li>la publication de commentaires sur les articles ouverts à la discussion.</li>
            </ul>
            <p>
                La création d'un compte ou la souscription à un abonnement emporte acceptation pleine
                et entière et sans réserve des présentes CGU/CGV ainsi que de la
                <a href="{{ route('legal.privacy') }}">Politique de confidentialité</a>
                et de la <a href="{{ route('legal.cookies') }}">Politique cookies</a>.
            </p>

            <h2>Article 2 — Définitions</h2>
            <ul>
                <li><strong>Site</strong> : le site web accessible à l'adresse <a href="/">https://geniemag.ci</a>.</li>
                <li><strong>Contenu premium</strong> : articles, études, numéros PDF du magazine et tous autres contenus réservés aux Abonnés à jour de leur abonnement.</li>
                <li><strong>Compte</strong> : espace personnel créé par l'Utilisateur, accessible via un identifiant (email) et un mot de passe.</li>
                <li><strong>Abonnement</strong> : contrat à durée déterminée ouvrant l'accès aux Contenus premium pour une durée définie par la formule choisie.</li>
                <li><strong>Formule</strong> : offre commerciale (Digital, Combo, Entreprise, ou autre formule proposée par l'Éditeur).</li>
            </ul>

            <h2>Article 3 — Acceptation et modification</h2>
            <p>
                L'accès au Site vaut acceptation des CGU/CGV en vigueur à la date de connexion.
                L'Éditeur se réserve la faculté de modifier les CGU/CGV à tout moment. Toute modification
                substantielle sera notifiée à l'Abonné par email 30 jours avant son entrée en vigueur ;
                l'Abonné pourra résilier sans frais avant cette date s'il refuse les nouvelles conditions.
                À défaut, il est réputé les avoir acceptées.
            </p>

            <h2>Article 4 — Accès au service et création de compte</h2>
            <h3>4.1 Éligibilité</h3>
            <p>
                La souscription d'un abonnement est réservée aux personnes majeures capables juridiquement.
                Les mineurs ne peuvent souscrire qu'avec l'accord préalable, écrit et vérifiable de leur
                représentant légal.
            </p>
            <h3>4.2 Sécurité des identifiants</h3>
            <p>
                L'Utilisateur est seul responsable de la confidentialité de ses identifiants. Toute connexion
                effectuée avec ses identifiants est présumée émaner de lui. Il s'engage à notifier
                l'Éditeur sans délai de toute utilisation non autorisée et à activer la double
                authentification (2FA) lorsqu'elle est proposée.
            </p>
            <h3>4.3 Exactitude des informations</h3>
            <p>
                L'Utilisateur s'engage à fournir des informations exactes, à jour, et à les maintenir à jour
                dans son espace « Mon profil ». Toute fraude pourra entraîner la suspension immédiate du
                compte sans remboursement.
            </p>

            <h2>Article 5 — Formules d'abonnement et tarifs</h2>
            <p>
                Les formules, leurs prix et leurs contenus sont décrits sur la page
                <a href="{{ route('subscribe') }}">/abonnement</a>. Les prix sont indiqués en francs CFA
                (XOF) toutes taxes comprises (TTC). L'Éditeur se réserve le droit de modifier ses tarifs
                à tout moment ; le tarif applicable est celui en vigueur au jour de la souscription.
                Les tarifs en cours ne s'appliquent pas rétroactivement aux abonnements déjà conclus.
            </p>
            <h3>5.1 Durée et reconduction</h3>
            <p>
                Sauf mention contraire, les abonnements sont conclus pour une durée d'un an à compter de
                la date de paiement. Ils se renouvellent par <strong>tacite reconduction</strong> pour des périodes
                successives d'un an, sauf résiliation notifiée par l'Abonné au moins 30 jours avant la
                date d'échéance, via l'espace « Mon compte » ou par email à
                @if ($publisher['email']) <a href="mailto:{{ $publisher['email'] }}">{{ $publisher['email'] }}</a>@endif.
            </p>
            <h3>5.2 Période d'essai</h3>
            <p>
                Lorsqu'une période d'essai gratuite est proposée, l'Abonné peut résilier librement pendant
                cette période. À l'issue, l'abonnement bascule automatiquement en payant sauf résiliation
                antérieure.
            </p>

            <h2>Article 6 — Paiement</h2>
            <p>
                Les paiements sont traités par <strong>Wave Business</strong> (ou tout autre prestataire de paiement
                agréé que l'Éditeur pourrait activer via son administration), via Mobile Money, carte
                bancaire ou tout autre moyen proposé au tunnel de paiement. L'Éditeur ne collecte et
                ne stocke aucune donnée bancaire ; les flux sont sécurisés par TLS 1.3.
            </p>
            <p>
                En cas de défaut de paiement au renouvellement (solde insuffisant, carte expirée), l'accès
                aux Contenus premium peut être suspendu après relance. L'Abonné dispose de 15 jours pour
                régulariser avant résiliation définitive de l'abonnement.
            </p>
            <p>
                Une facture est émise pour chaque règlement et mise à disposition dans l'espace
                « Mon compte » en PDF téléchargeable.
            </p>

            <h2>Article 7 — Droit de rétractation</h2>
            <p>
                Conformément à l'<strong>article 35 de la loi ivoirienne n° 2016-412 du 15 juin 2016 relative à
                la consommation</strong>, l'Abonné consommateur dispose d'un délai de <strong>quatorze (14) jours
                francs</strong> à compter de la souscription pour exercer son droit de rétractation,
                sans justification et sans pénalité.
            </p>
            <p>
                <strong>Exception — contenu numérique exécuté :</strong> si l'Abonné demande expressément l'accès
                immédiat au Contenu premium et reconnaît perdre son droit de rétractation dès le début
                de l'exécution, ce droit ne pourra plus être exercé pour la part de contenu déjà consommée.
                Cette renonciation est validée lors du paiement.
            </p>
            <p>
                Pour exercer le droit de rétractation, l'Abonné adresse une déclaration claire à
                @if ($publisher['email']) <a href="mailto:{{ $publisher['email'] }}">{{ $publisher['email'] }}</a>@endif
                en précisant son identifiant ou la référence de la commande. Le remboursement intervient
                sous 14 jours par le même moyen de paiement, sauf accord exprès de l'Abonné pour un autre moyen.
            </p>

            <h2>Article 8 — Résiliation</h2>
            <h3>8.1 Résiliation par l'Abonné</h3>
            <p>
                L'Abonné peut résilier à tout moment via son espace « Mon compte » ou par email. La
                résiliation prend effet à l'échéance de la période en cours ; les sommes déjà versées
                restent acquises à l'Éditeur, sauf rétractation valable (Article 7).
            </p>
            <h3>8.2 Résiliation par l'Éditeur</h3>
            <p>
                L'Éditeur peut résilier de plein droit, avec ou sans préavis selon la gravité, en cas de :
            </p>
            <ul>
                <li>manquement grave de l'Abonné à ses obligations (fraude, partage d'identifiants, extraction massive de contenu, propos haineux ou illicites, tentative d'intrusion) ;</li>
                <li>défaut de paiement persistant (Article 6) ;</li>
                <li>obligation légale, judiciaire ou réglementaire.</li>
            </ul>
            <p>
                La résiliation pour faute grave n'ouvre droit à aucun remboursement.
            </p>

            <h2>Article 9 — Obligations et engagements de l'Utilisateur</h2>
            <p>L'Utilisateur s'engage notamment à :</p>
            <ul>
                <li>ne pas communiquer ses identifiants, ni les céder, prêter ou partager avec des tiers ;</li>
                <li>ne pas reproduire, représenter, diffuser ou commercialiser tout ou partie des Contenus premium hors du cadre strictement privé ;</li>
                <li>ne pas extraire de manière automatisée ou massive (scraping, crawling) le contenu du Site sans autorisation écrite préalable ;</li>
                <li>ne pas chercher à contourner les mesures techniques de protection (paywall, 2FA, limitations techniques) ;</li>
                <li>respecter la charte de modération lors de la publication de commentaires (pas d'injures, diffamation, incitation à la haine, spam, contenu publicitaire déguisé) ;</li>
                <li>ne pas utiliser le Site pour une activité illicite ou contraire aux bonnes mœurs.</li>
            </ul>

            <h2>Article 10 — Contenus publiés par les Utilisateurs (UGC)</h2>
            <p>
                L'Utilisateur qui publie des commentaires, messages ou tout autre contenu sur le Site
                en garantit l'originalité et la licéité. Il accorde à l'Éditeur une licence non exclusive,
                gratuite, mondiale, pour la durée légale de protection des droits, en vue d'héberger,
                d'afficher et d'éventuellement citer ce contenu dans le cadre éditorial.
            </p>
            <p>
                L'Éditeur se réserve le droit de modérer, masquer ou supprimer à tout moment et sans
                préavis tout contenu manifestement illicite, diffamatoire, hors sujet ou contraire
                aux présentes CGU.
            </p>

            <h2>Article 11 — Propriété intellectuelle</h2>
            <p>
                L'ensemble des éléments du Site (textes, articles, photographies, illustrations, graphismes,
                logos, marques, bases de données, code source) est la propriété exclusive de l'Éditeur ou
                de ses partenaires, et est protégé par les législations ivoirienne et internationale
                sur la propriété intellectuelle (notamment l'Accord de Bangui révisé et la loi ivoirienne
                n° 2016-555 du 26 juillet 2016 relative au droit d'auteur et aux droits voisins).
            </p>
            <p>
                L'abonnement confère à l'Abonné une <strong>licence d'usage strictement personnelle et non cessible</strong>
                des Contenus premium, à des fins de consultation privée uniquement. Toute autre utilisation
                (reproduction, diffusion publique, revente, citation hors norme) requiert l'autorisation
                écrite préalable de l'Éditeur. Toute violation expose son auteur aux sanctions prévues par
                la loi, notamment au titre de la contrefaçon.
            </p>

            <h2>Article 12 — Responsabilité de l'Éditeur</h2>
            <p>
                L'Éditeur fournit le service avec diligence professionnelle, sur la base d'une obligation
                de moyens. Il ne garantit pas que le Site sera exempt d'erreurs, d'interruptions ou d'anomalies,
                ni son adéquation parfaite à un besoin particulier de l'Abonné.
            </p>
            <h3>12.1 Limitations</h3>
            <p>
                Dans les limites autorisées par la loi, l'Éditeur ne saurait être tenu responsable :
            </p>
            <ul>
                <li>des dommages indirects ou immatériels (perte de données, perte d'exploitation, préjudice commercial, manque à gagner) ;</li>
                <li>des interruptions de service dues à la maintenance (préavis de 48 h sauf urgence sécurité), à un cas de force majeure, ou au fait d'un tiers (hébergeur, opérateur télécom, prestataire de paiement) ;</li>
                <li>de la perte d'accès liée à un matériel, une connexion ou un navigateur défectueux de l'Utilisateur ;</li>
                <li>des contenus ou liens externes vers des sites tiers ;</li>
                <li>des analyses, opinions ou tribunes publiées par des contributeurs, qui n'engagent que leurs auteurs.</li>
            </ul>
            <p>
                La responsabilité totale cumulée de l'Éditeur, toutes causes confondues, est plafonnée
                au montant effectivement payé par l'Abonné sur les douze (12) mois précédant la survenance
                du fait générateur du litige.
            </p>

            <h2>Article 13 — Force majeure</h2>
            <p>
                Aucune partie ne saurait être tenue responsable d'un manquement imputable à un cas de force
                majeure au sens de la jurisprudence ivoirienne : catastrophe naturelle, guerre, troubles
                civils, incendie, panne générale d'électricité ou de télécommunications, décision des
                autorités publiques, attaque informatique majeure, pandémie. L'exécution des obligations
                est suspendue pendant la durée de l'événement, et sera reprise dès que possible.
            </p>

            <h2>Article 14 — Protection des données personnelles</h2>
            <p>
                Le traitement des données personnelles est régi par notre
                <a href="{{ route('legal.privacy') }}">Politique de confidentialité</a>, établie
                conformément à la <strong>loi ivoirienne n° 2013-450 du 19 juin 2013</strong> relative à la
                protection des données à caractère personnel et, pour les Utilisateurs résidant dans
                l'Union européenne, au <strong>Règlement (UE) 2016/679 (RGPD)</strong>.
            </p>

            <h2>Article 15 — Cookies</h2>
            <p>
                L'utilisation de cookies est détaillée dans notre
                <a href="{{ route('legal.cookies') }}">Politique cookies</a>. Les cookies non essentiels
                ne sont déposés qu'avec votre consentement explicite, révocable à tout moment.
            </p>

            <h2>Article 16 — Liens hypertextes et services tiers</h2>
            <p>
                Le Site peut contenir des liens vers des sites tiers. L'Éditeur n'exerce aucun contrôle
                sur ces sites et décline toute responsabilité quant à leur contenu, leur disponibilité
                et leurs pratiques en matière de données personnelles. L'Utilisateur reste libre de
                consulter la politique des tiers avant d'interagir avec eux.
            </p>

            <h2>Article 17 — Cession</h2>
            <p>
                L'Éditeur peut céder le contrat à un tiers dans le cadre d'une restructuration, fusion,
                acquisition ou cession d'activité, à condition que le niveau de protection offert à l'Abonné
                soit au moins équivalent. L'Abonné ne peut céder son contrat à un tiers sans l'accord
                écrit préalable de l'Éditeur.
            </p>

            <h2>Article 18 — Divisibilité et non-renonciation</h2>
            <p>
                Si l'une des clauses des présentes CGU/CGV est déclarée nulle, invalide ou inapplicable
                par une juridiction compétente, les autres clauses demeurent pleinement applicables.
                Le fait pour l'Éditeur de ne pas exercer un droit ou de tolérer un manquement ne vaut pas
                renonciation à s'en prévaloir ultérieurement.
            </p>

            <h2>Article 19 — Réclamations et médiation</h2>
            <p>
                Toute réclamation doit être adressée en priorité au service client :
                @if ($publisher['email']) <a href="mailto:{{ $publisher['email'] }}">{{ $publisher['email'] }}</a>. @endif
                Un accusé de réception est envoyé sous 48 heures ouvrées et une réponse au fond sous
                15 jours ouvrables. À défaut de solution amiable, l'Abonné consommateur peut saisir le
                Conseil de la Concurrence ou toute instance de médiation habituellement compétente en
                Côte d'Ivoire.
            </p>

            <h2>Article 20 — Droit applicable et juridictions</h2>
            <p>
                Les présentes CGU/CGV sont régies par le <strong>droit ivoirien</strong>. Tout différend relatif à leur
                formation, exécution ou interprétation sera, à défaut de règlement amiable, soumis à la
                compétence exclusive des tribunaux du ressort d'Abidjan, sauf disposition impérative
                contraire protectrice du consommateur.
            </p>

            <h2>Article 21 — Contact</h2>
            <p>
                Pour toute question relative aux présentes CGU/CGV :<br>
                <strong>{{ $publisher['editor'] }}</strong><br>
                @if ($publisher['address']) {{ $publisher['address'] }}<br> @endif
                @if ($publisher['email']) Email : <a href="mailto:{{ $publisher['email'] }}">{{ $publisher['email'] }}</a><br> @endif
                @if ($publisher['phone']) Téléphone : {{ $publisher['phone'] }} @endif
            </p>
        </div>

        <footer class="gm-meta mt-12 border-t border-gm-gray-line pt-4">
            Dernière mise à jour : {{ $updatedAt }} · Version opposable.
        </footer>
    </article>
</x-layouts.public>
