<x-layouts.public title="Politique de confidentialité — GÉNIE MARKETING Mag">
    <article class="mx-auto max-w-container-narrow px-8 py-16">
        <div class="gm-section-heading flex items-baseline gap-6">
            <span class="font-mono text-xs font-bold tracking-[0.15em] text-gm-red">Informations légales</span>
            <h1 class="font-slab text-3xl font-bold italic leading-none text-gm-ink md:text-5xl">Politique de confidentialité</h1>
        </div>

        <div class="prose prose-lg prose-slate mt-10 max-w-none font-sans text-gm-ink
            [&_h2]:font-slab [&_h2]:italic [&_h2]:text-gm-ink [&_h2]:mt-10
            [&_h3]:font-slab [&_h3]:italic [&_h3]:text-gm-ink [&_h3]:mt-6
            [&_a]:text-gm-red [&_a]:underline
            [&_table]:w-full [&_table]:border-collapse [&_td]:border [&_td]:border-gm-gray-line [&_td]:p-2 [&_th]:border [&_th]:border-gm-gray-line [&_th]:p-2 [&_th]:bg-gm-paper [&_th]:text-left">

            <p class="lead">
                La présente politique décrit comment <strong>{{ $publisher['editor'] }}</strong> (ci-après
                « <strong>l'Éditeur</strong> » ou « <strong>nous</strong> ») collecte, utilise, conserve et protège vos
                données à caractère personnel dans le cadre de l'exploitation du site
                <a href="/">geniemag.ci</a>. Elle s'applique à tout visiteur, abonné, contributeur
                ou prospect.
            </p>
            <p>
                Nous nous engageons à respecter :
            </p>
            <ul>
                <li>la <strong>loi ivoirienne n° 2013-450 du 19 juin 2013</strong> relative à la protection des données à caractère personnel, et ses textes d'application, sous le contrôle de l'<strong>Autorité de Régulation des Télécommunications de Côte d'Ivoire (ARTCI)</strong> ;</li>
                <li>le <strong>Règlement (UE) 2016/679 du 27 avril 2016 (RGPD)</strong> pour les personnes concernées résidant dans l'Union européenne ;</li>
                <li>les principes de <strong>privacy by design</strong> et de <strong>minimisation</strong> des données.</li>
            </ul>

            <h2>1. Responsable du traitement</h2>
            <p>
                Le responsable du traitement est :<br>
                <strong>{{ $publisher['editor'] }}</strong>
                @if ($publisher['editor_form']) ({{ $publisher['editor_form'] }}) @endif<br>
                @if ($publisher['address']) {{ $publisher['address'] }}<br> @endif
                @if ($publisher['editor_rccm']) RCCM : {{ $publisher['editor_rccm'] }}<br> @endif
                @if ($publisher['email']) Contact général : <a href="mailto:{{ $publisher['email'] }}">{{ $publisher['email'] }}</a> @endif
            </p>
            <p>
                <strong>Délégué à la Protection des Données (DPO)</strong> :
                @if ($publisher['dpo_email'])
                    <a href="mailto:{{ $publisher['dpo_email'] }}">{{ $publisher['dpo_email'] }}</a>.
                @else
                    coordonnées fournies sur demande à l'adresse générale ci-dessus.
                @endif
                Le DPO répond sous 30 jours maximum aux demandes relatives à vos droits.
            </p>

            <h2>2. Données que nous collectons</h2>
            <p>
                Nous ne collectons que les données nécessaires aux finalités décrites à l'article 3.
                Les champs marqués d'un astérisque dans les formulaires sont les seuls obligatoires.
            </p>
            <table>
                <thead>
                    <tr>
                        <th>Catégorie</th>
                        <th>Données concernées</th>
                        <th>Source</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Identification</td>
                        <td>Prénom, nom, email, téléphone, mot de passe (haché, jamais lisible)</td>
                        <td>Formulaire d'inscription / checkout</td>
                    </tr>
                    <tr>
                        <td>Adresse</td>
                        <td>Adresse postale, ville, pays (formule Combo papier uniquement)</td>
                        <td>Formulaire d'inscription</td>
                    </tr>
                    <tr>
                        <td>Commandes &amp; paiement</td>
                        <td>Référence, montant, devise, statut, canal de paiement (mobile money / carte). <strong>Aucune coordonnée bancaire n'est stockée</strong> — le PAN (numéro de carte), code Wave ou PIN transitent uniquement chez le prestataire de paiement.</td>
                        <td>Prestataire de paiement (Wave / Paystack)</td>
                    </tr>
                    <tr>
                        <td>Préférences éditoriales</td>
                        <td>Choix de newsletters, opt-in marketing, centres d'intérêt</td>
                        <td>Vous-même, via l'espace « Mon profil »</td>
                    </tr>
                    <tr>
                        <td>Usage du service</td>
                        <td>Articles lus, durée de session, dates de connexion, appareil utilisé</td>
                        <td>Cookies techniques &amp; logs serveurs</td>
                    </tr>
                    <tr>
                        <td>Techniques</td>
                        <td>Adresse IP, user-agent, référents, date/heure — conservés en journaux d'audit</td>
                        <td>Requêtes HTTP</td>
                    </tr>
                    <tr>
                        <td>Authentification forte (2FA)</td>
                        <td>Secret TOTP chiffré, codes de récupération chiffrés, dates de confirmation/désactivation</td>
                        <td>Vous-même, lors de l'activation 2FA</td>
                    </tr>
                </tbody>
            </table>
            <p>
                Nous ne collectons <strong>aucune donnée sensible</strong> au sens de l'article 9 du RGPD
                (origine raciale ou ethnique, opinions politiques, convictions religieuses, santé,
                orientation sexuelle, biométrie), sauf divulgation volontaire par vous dans un commentaire
                — que nous vous déconseillons.
            </p>

            <h2>3. Finalités et bases légales</h2>
            <table>
                <thead>
                    <tr>
                        <th>Finalité</th>
                        <th>Base légale</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Création et gestion du compte, fourniture du service (articles, numéros, newsletters abonnés)</td>
                        <td>Exécution du contrat (art. 6.1.b RGPD)</td>
                    </tr>
                    <tr>
                        <td>Traitement des paiements, facturation, recouvrement</td>
                        <td>Exécution du contrat + obligation légale (comptabilité)</td>
                    </tr>
                    <tr>
                        <td>Sécurité, prévention des abus, journal d'audit, lutte contre la fraude</td>
                        <td>Intérêt légitime de l'Éditeur (art. 6.1.f RGPD)</td>
                    </tr>
                    <tr>
                        <td>Newsletter gratuite / prospection</td>
                        <td>Consentement explicite (double opt-in)</td>
                    </tr>
                    <tr>
                        <td>Mesure d'audience et statistiques anonymisées</td>
                        <td>Consentement (cookies non essentiels)</td>
                    </tr>
                    <tr>
                        <td>Gestion des demandes de droits, réclamations, médiation</td>
                        <td>Obligation légale</td>
                    </tr>
                    <tr>
                        <td>Respect des obligations légales (réquisitions judiciaires, droit de réponse…)</td>
                        <td>Obligation légale</td>
                    </tr>
                </tbody>
            </table>

            <h2>4. Destinataires et sous-traitants</h2>
            <p>
                Vos données sont accessibles uniquement aux personnes habilitées au sein de l'Éditeur
                (équipes éditoriale, commerciale, technique, conformité), strictement dans la limite
                nécessaire à l'exercice de leurs missions, sous obligation de confidentialité et
                dans le cadre d'un cloisonnement par rôle (RBAC) avec journal d'audit.
            </p>
            <p>
                Nous recourons aux sous-traitants suivants, liés par des clauses contractuelles garantissant
                un niveau de protection équivalent à la présente politique (clauses types contractuelles
                européennes ou engagements équivalents) :
            </p>
            <table>
                <thead>
                    <tr><th>Sous-traitant</th><th>Finalité</th><th>Localisation</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Wave Business</td>
                        <td>Traitement des paiements Mobile Money</td>
                        <td>UEMOA (Sénégal, Côte d'Ivoire)</td>
                    </tr>
                    <tr>
                        <td>Paystack (fallback optionnel)</td>
                        <td>Traitement de paiements cartes et mobile money</td>
                        <td>Afrique / Irlande (Stripe Group)</td>
                    </tr>
                    <tr>
                        <td>{{ $publisher['host_name'] ?: 'Hostinger' }}</td>
                        <td>Hébergement du site, BDD, emails transactionnels SMTP</td>
                        <td>Union européenne (Chypre / Lituanie)</td>
                    </tr>
                    <tr>
                        <td>SendGrid / Mailjet (si activé)</td>
                        <td>Envoi d'emails marketing, confirmations</td>
                        <td>UE / États-Unis (clauses types UE)</td>
                    </tr>
                    <tr>
                        <td>Sentry (si activé)</td>
                        <td>Supervision des erreurs applicatives</td>
                        <td>UE</td>
                    </tr>
                    <tr>
                        <td>Cloudflare (si activé)</td>
                        <td>CDN, protection DDoS, WAF</td>
                        <td>Réseau mondial</td>
                    </tr>
                </tbody>
            </table>
            <p>
                Nous ne <strong>vendons jamais</strong> vos données. Elles ne sont communiquées à des tiers qu'en
                cas d'obligation légale (réquisition judiciaire, demande administrative fondée) ou de
                cession d'activité, auquel cas vous en serez informé 30 jours à l'avance avec faculté
                de suppression préalable.
            </p>

            <h2>5. Transferts de données hors Côte d'Ivoire</h2>
            <p>
                Certains sous-traitants sont établis hors de Côte d'Ivoire (Union européenne et
                exceptionnellement États-Unis). Ces transferts sont encadrés soit par une décision
                d'adéquation (UE), soit par des <strong>clauses contractuelles types</strong> offrant des garanties
                équivalentes à la loi ivoirienne 2013-450 et au RGPD.
            </p>

            <h2>6. Durées de conservation</h2>
            <table>
                <thead>
                    <tr><th>Donnée</th><th>Durée active</th><th>Archivage légal</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Compte actif, préférences</td>
                        <td>Durée de vie du compte</td>
                        <td>3 ans après dernière activité (preuve)</td>
                    </tr>
                    <tr>
                        <td>Abonnement, commandes</td>
                        <td>Durée du contrat</td>
                        <td>3 ans (preuve) + prescription civile</td>
                    </tr>
                    <tr>
                        <td>Factures</td>
                        <td>—</td>
                        <td>10 ans (obligation comptable CI)</td>
                    </tr>
                    <tr>
                        <td>Journaux d'audit et sécurité</td>
                        <td>12 mois</td>
                        <td>Purge automatique</td>
                    </tr>
                    <tr>
                        <td>Newsletter (inscrit)</td>
                        <td>Tant qu'inscrit</td>
                        <td>3 ans après désinscription (preuve du consentement)</td>
                    </tr>
                    <tr>
                        <td>Cookies de mesure d'audience</td>
                        <td>13 mois maximum</td>
                        <td>—</td>
                    </tr>
                    <tr>
                        <td>Données de prospection</td>
                        <td>3 ans à compter du dernier contact</td>
                        <td>—</td>
                    </tr>
                </tbody>
            </table>
            <p>
                Au terme de ces durées, les données sont supprimées ou anonymisées de manière irréversible.
            </p>

            <h2>7. Vos droits</h2>
            <p>
                Conformément à la loi 2013-450 et au RGPD, vous disposez des droits suivants, que vous
                pouvez exercer à tout moment auprès de notre DPO
                @if ($publisher['dpo_email']) (<a href="mailto:{{ $publisher['dpo_email'] }}">{{ $publisher['dpo_email'] }}</a>) @endif :
            </p>
            <ul>
                <li><strong>Droit d'accès</strong> : obtenir une copie des données que nous détenons sur vous ;</li>
                <li><strong>Droit de rectification</strong> : corriger une donnée inexacte ou incomplète ;</li>
                <li><strong>Droit à l'effacement (« droit à l'oubli »)</strong> : supprimer vos données, sous réserve des obligations de conservation légale ;</li>
                <li><strong>Droit à la limitation</strong> : suspendre temporairement le traitement le temps d'une vérification ;</li>
                <li><strong>Droit d'opposition</strong> : refuser un traitement fondé sur l'intérêt légitime ou la prospection ;</li>
                <li><strong>Droit à la portabilité</strong> : recevoir vos données dans un format structuré, courant et lisible ;</li>
                <li><strong>Droit de retirer le consentement</strong> : à tout moment, sans effet rétroactif ;</li>
                <li><strong>Droit de donner des directives post-mortem</strong> (art. 38 RGPD) sur le sort de vos données après votre décès ;</li>
                <li><strong>Droit de ne pas faire l'objet d'une décision automatisée</strong> : nous ne prenons aucune décision produisant des effets juridiques fondée exclusivement sur un traitement automatisé.</li>
            </ul>
            <p>
                Pour des raisons de sécurité, nous pourrons demander un justificatif d'identité avant
                de répondre. Nous répondons sous un mois maximum (pouvant être porté à trois mois pour
                les demandes complexes, avec information motivée).
            </p>

            <h2>8. Réclamation auprès d'une autorité de contrôle</h2>
            <p>
                En cas de réponse insatisfaisante, vous pouvez introduire une réclamation auprès de :
            </p>
            <ul>
                <li>l'<strong>Autorité de Régulation des Télécommunications de Côte d'Ivoire (ARTCI)</strong> — <a href="https://www.artci.ci" target="_blank" rel="noopener noreferrer">www.artci.ci</a> ;</li>
                <li>pour les résidents UE : votre autorité nationale (en France, la <strong>CNIL</strong> — <a href="https://www.cnil.fr" target="_blank" rel="noopener noreferrer">www.cnil.fr</a>).</li>
            </ul>

            <h2>9. Sécurité des données</h2>
            <p>
                Nous mettons en œuvre des mesures techniques et organisationnelles appropriées, notamment :
            </p>
            <ul>
                <li>chiffrement TLS 1.3 pour tous les échanges ;</li>
                <li>hachage des mots de passe avec bcrypt (coût ≥ 12) ;</li>
                <li>chiffrement au repos des secrets 2FA et codes de récupération ;</li>
                <li>cloisonnement par rôle (RBAC) avec journal d'audit immuable ;</li>
                <li>protection contre les attaques applicatives (CSRF, XSS, injection SQL, SSRF, IDOR) ;</li>
                <li>double authentification obligatoire pour les comptes administrateurs ;</li>
                <li>sauvegardes chiffrées avec vérification régulière de restauration ;</li>
                <li>revues de sécurité périodiques et traitement des vulnérabilités signalées.</li>
            </ul>
            <p>
                En cas de violation de données susceptible d'engendrer un risque pour vos droits et libertés,
                nous notifierons l'ARTCI dans les 72 heures et vous informerons individuellement sans délai
                lorsque le risque est élevé.
            </p>

            <h2>10. Mineurs</h2>
            <p>
                Le Site n'est pas destiné aux enfants de moins de 16 ans. Nous ne collectons pas
                sciemment les données des mineurs sans consentement vérifiable d'un représentant légal.
                Si vous estimez qu'un mineur s'est inscrit sans accord parental, contactez notre DPO
                pour suppression immédiate.
            </p>

            <h2>11. Cookies et traceurs</h2>
            <p>
                L'usage de cookies et traceurs est détaillé dans notre
                <a href="{{ route('legal.cookies') }}">Politique cookies</a>. Vous pouvez à tout moment
                modifier vos choix via le bandeau dédié.
            </p>

            <h2>12. Modifications de la politique</h2>
            <p>
                Nous pouvons modifier la présente politique pour refléter les évolutions légales,
                techniques ou de nos services. Toute modification substantielle vous sera notifiée par
                email au moins 30 jours avant son entrée en vigueur. La version opposable est celle
                en ligne à la date de la consultation.
            </p>

            <h2>13. Contact</h2>
            <p>
                Pour toute question, exercice de droit ou réclamation :
                @if ($publisher['dpo_email'])
                    <a href="mailto:{{ $publisher['dpo_email'] }}">{{ $publisher['dpo_email'] }}</a>
                @else
                    <em>DPO — adresse fournie sur demande.</em>
                @endif
                @if ($publisher['address']) <br>Ou par courrier postal : {{ $publisher['editor'] }}, {{ $publisher['address'] }}. @endif
            </p>
        </div>

        <footer class="gm-meta mt-12 border-t border-gm-gray-line pt-4">
            Dernière mise à jour : {{ $updatedAt }}.
        </footer>
    </article>
</x-layouts.public>
