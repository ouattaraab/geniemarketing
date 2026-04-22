# GÉNIE MARKETING Mag
## Backlog produit & document d'architecture

> *Spécifications pour l'implémentation du backoffice éditorial et du frontend web (grand public & abonnés)*

---

| Attribut | Valeur |
|---|---|
| **Version** | 1.0 |
| **Date** | 20 avril 2026 |
| **Auteur** | Direction Produit — Génie Marketing Mag |
| **Statut** | Draft — à valider par le COPIL |
| **Périmètre** | Backoffice éditorial + Frontend web (grand public & abonnés) |
| **Nombre d'épiques** | 22 |
| **Nombre d'user stories** | 166 |
| **Durée totale** | 12 à 15 mois sur 4 itérations |
| **Budget indicatif** | 72 à 95 millions de FCFA |

---

## Table des matières

1. [Introduction](#1-introduction)
2. [Glossaire](#2-glossaire)
3. [Vision produit](#3-vision-produit)
4. [Acteurs et rôles](#4-acteurs-et-rôles)
5. [Architecture fonctionnelle](#5-architecture-fonctionnelle)
6. [Architecture technique cible](#6-architecture-technique-cible)
7. [Modèle de données](#7-modèle-de-données)
8. [Backlog détaillé — 166 user stories](#8-backlog-détaillé--166-user-stories)
9. [Roadmap et phasage](#9-roadmap-et-phasage)
10. [Exigences non-fonctionnelles](#10-exigences-non-fonctionnelles)
11. [Intégrations externes](#11-intégrations-externes)
12. [Risques et mitigations](#12-risques-et-mitigations)
13. [Gouvernance projet](#13-gouvernance-projet)
14. [Annexes](#14-annexes)

---

## 1. Introduction

### 1.1. Contexte

GÉNIE MARKETING Mag se positionne comme le magazine de référence du marketing ivoirien et panafricain. Sa promesse éditoriale — *décrypter, inspirer, transformer* — repose sur la production de contenus à forte valeur ajoutée : analyses approfondies, études de cas, interviews exclusives, dossiers sectoriels et portraits des acteurs qui font bouger l'écosystème.

Pour soutenir cette ambition, le magazine se dote d'une plateforme numérique associant deux environnements complémentaires :

- un **backoffice éditorial** permettant à la rédaction de produire, valider et publier les contenus, gérer le magazine papier/PDF, piloter les abonnements et la relation client ;
- un **frontend web public** donnant accès aux articles selon le profil du lecteur (visiteur, abonné Digital, Combo ou Entreprise), avec un parcours d'abonnement fluide et un espace abonné complet.

Le présent document est un livrable structurant : il cadre la vision produit, formalise les acteurs, décrit l'architecture cible et pose les bases de la mise en œuvre, tout en détaillant intégralement le backlog fonctionnel (166 user stories avec critères d'acceptation, priorisation et estimation).

### 1.2. Objectifs du document

Ce document poursuit quatre objectifs :

- **Aligner** l'équipe produit, la rédaction, la direction et les équipes techniques sur le périmètre exact à livrer.
- **Fournir une base contractuelle** utilisable pour cadrer le projet avec des prestataires internes ou externes.
- **Poser les fondations** techniques et fonctionnelles indispensables à une plateforme éditoriale pérenne (performance, sécurité, conformité).
- **Structurer le phasage** en 4 itérations successives, chaque itération livrant de la valeur mesurable.

### 1.3. Périmètre

Le périmètre couvert par ce document comprend :

- **En entrée :** l'application web de production éditoriale (backoffice) — gestion des articles, des médias, du magazine, des abonnements, de la relation client, de la newsletter, des commandes, des paramètres.
- **En sortie :** l'application web grand public et abonnés (frontend) — accueil, rubriques, consultation des articles avec paywall, tunnel d'abonnement, espace abonné, boutique magazine.
- **Transverse :** SEO, performance, sécurité, accessibilité, conformité RGPD et loi 2013-450 CI sur la protection des données personnelles.

**Ne sont pas couverts** par ce document : les applications mobiles natives (iOS / Android), les systèmes de billetterie événementielle et d'espace vidéo (différés en V3+), l'intégration d'un logiciel de gestion comptable à proprement parler (simple export CSV prévu).

---

## 2. Glossaire

| Terme | Définition |
|---|---|
| **Backoffice** | Application web privée, utilisée par l'équipe éditoriale, commerciale et administrative pour piloter la plateforme. |
| **Frontend** | Application web publique, accessible aux visiteurs non identifiés et aux abonnés authentifiés. |
| **Paywall** | Dispositif qui conditionne l'accès à tout ou partie d'un contenu à une authentification ou à un abonnement payant. |
| **Freemium** | Modèle permettant à un visiteur non abonné de consulter un nombre limité d'articles gratuits par mois avant déclenchement du paywall. |
| **Épique** | Grande fonctionnalité produit, regroupant un ensemble cohérent d'user stories. |
| **User story** | Expression d'un besoin utilisateur sous la forme "En tant que [rôle], je veux [action] afin de [bénéfice]". |
| **MVP** | Minimum Viable Product. Périmètre réduit mais autonome permettant un lancement commercial. |
| **MRR / ARR** | Monthly / Annual Recurring Revenue. Revenu mensuel / annuel récurrent. |
| **LTV** | Life Time Value. Valeur client moyenne sur toute sa durée d'abonnement. |
| **CAC** | Customer Acquisition Cost. Coût moyen d'acquisition d'un nouvel abonné. |
| **Churn rate** | Taux d'attrition. Pourcentage d'abonnés qui ne renouvellent pas leur abonnement sur une période donnée. |
| **NPS** | Net Promoter Score. Indicateur de recommandation client. |
| **WCAG 2.1 AA** | Web Content Accessibility Guidelines. Référentiel international d'accessibilité web, niveau intermédiaire. |
| **RGPD** | Règlement général sur la protection des données (UE, 2018). |
| **Loi 2013-450 CI** | Loi ivoirienne du 19 juin 2013 relative à la protection des données à caractère personnel. |
| **ARTCI** | Autorité de régulation des télécommunications de Côte d'Ivoire, également compétente pour les données personnelles. |
| **CinetPay** | Agrégateur ivoirien de paiements (Mobile Money, cartes bancaires). |
| **FCFA** | Franc CFA (XOF), devise officielle de la Côte d'Ivoire. |
| **SSR** | Server-Side Rendering. Rendu côté serveur, essentiel pour le SEO d'un site de contenu. |
| **CDN** | Content Delivery Network. Réseau de diffusion de contenus géographiquement distribué. |

---

## 3. Vision produit

### 3.1. Promesse utilisateur

**Pour les lecteurs** — décideurs, marketeurs, étudiants, curieux — la plateforme offre l'accès le plus riche et le plus exigeant à l'écosystème marketing ivoirien : analyses de fond, études de cas documentées, portraits et interviews exclusives, dossiers sectoriels. L'expérience de lecture se veut soignée, à la hauteur des magazines de référence internationaux, tout en respectant les codes et la culture ivoiriens.

**Pour l'équipe éditoriale**, la plateforme est un outil de production industriel : publier un article doit être aussi simple qu'écrire un document Word, le workflow de validation doit être transparent, et toute l'orchestration (médias, SEO, diffusion sociale, newsletter) doit être automatisée autant que possible.

**Pour la direction**, la plateforme est un outil de pilotage commercial et éditorial : KPIs de consultation et d'engagement, suivi des abonnements, pilotage financier, analyses de performance — tout doit être disponible en quelques clics.

### 3.2. Objectifs business mesurables

Les objectifs mesurables sur les 12 mois suivant le lancement du MVP sont :

| Indicateur | Cible année 1 |
|---|---|
| Abonnés payants (mix Digital, Combo, Entreprise) | **5 000** |
| Visiteurs uniques mensuels sur le site | **150 000** |
| Churn annuel | **< 25 %** |
| Taux d'ouverture newsletter | **> 35 %** |
| Durée moyenne de lecture d'un article | **> 4 min** |
| Taux de transformation tunnel d'abonnement | **> 3 %** |
| Production éditoriale | **8 à 12 articles/semaine** |

### 3.3. Modèle économique

Le modèle repose sur trois sources de revenus complémentaires :

- **Abonnements récurrents** (cœur) : 3 formules — Digital 24 000 FCFA/an, Combo Papier + Digital 48 000 FCFA/an, Entreprise à partir de 320 000 FCFA/an pour 10 licences.
- **Ventes à l'unité** : numéros papier et PDF accessibles aux non-abonnés, ainsi qu'articles premium achetables à l'unité (revenu d'appoint).
- **Partenariats & contenus sponsorisés** : dossiers sponsorisés clairement identifiés, bannières, études sectorielles commanditées (respect strict des obligations de transparence).

---

## 4. Acteurs et rôles

Le système distingue **10 rôles**, répartis entre les utilisateurs frontend (visiteurs et abonnés selon 3 formules) et les utilisateurs backoffice (rédaction, commerce, administration).

### 4.1. Utilisateurs du frontend

| Code | Rôle | Périmètre |
|---|---|---|
| **VIS** | Visiteur | Navigation libre, lecture des articles en accès ouvert, consultation des extraits payants, inscription à la newsletter, souscription à un abonnement. |
| **AB-D** | Abonné Digital | Accès illimité aux articles premium, lecture et téléchargement des numéros PDF, newsletter premium, espace abonné complet. |
| **AB-C** | Abonné Combo | Tous les droits AB-D + livraison du magazine papier à domicile + 2 invitations par an aux événements GM Days. |
| **AB-E** | Abonné Entreprise | 10 licences nominatives + reporting d'usage + account manager dédié + études sectorielles complètes. |

### 4.2. Utilisateurs du backoffice

| Code | Rôle | Périmètre |
|---|---|---|
| **RED** | Rédacteur | Rédiger, illustrer et soumettre ses propres articles à relecture. |
| **CHEF** | Rédacteur en chef | Relire, valider, publier, planifier les contenus de l'ensemble des rédacteurs. |
| **EDIT** | Administrateur éditorial | Superviser la rédaction : gérer auteurs, taxonomies, médias, numéros magazine, modération. |
| **COM** | Gestionnaire commercial | Piloter les abonnements, la relation client (CRM), les commandes, la facturation et les campagnes newsletter. |
| **ADM** | Administrateur système | Gérer les comptes backoffice, les rôles, les paramètres techniques, les intégrations. |
| **SUP** | Super administrateur | Accès total à la plateforme, gouvernance globale, configuration maître. |

### 4.3. Matrice des droits

Légende : **L** = Lire · **C** = Créer · **M** = Modifier · **S** = Supprimer · **V** = Valider/Publier · **L-perso** = Lire ses propres éléments uniquement · **Lim.** = Accès limité aux licences de l'entreprise · **-** = Aucun droit · **Tout** = Tous droits

| Objet / Action | VIS | AB-D | AB-C | AB-E | RED | CHEF | EDIT | COM | ADM | SUP |
|---|:-:|:-:|:-:|:-:|:-:|:-:|:-:|:-:|:-:|:-:|
| Consulter articles libres | L | L | L | L | L | L | L | L | L | L |
| Consulter articles premium | - | L | L | L | L | L | L | L | L | L |
| Commenter (après modération) | - | L+C | L+C | L+C | L | L | L | L | L | L |
| Rédiger ses propres articles | - | - | - | - | L+C+M | L+C+M | L+C+M | - | - | L+C+M |
| Valider / publier | - | - | - | - | - | V | V | - | - | V |
| Gérer tous les articles | - | - | - | - | - | L+M | L+C+M+S | - | - | Tout |
| Gérer les taxonomies | - | - | - | - | - | L | L+C+M | - | L | Tout |
| Gérer les numéros magazine | - | - | - | - | - | L+C+M | L+C+M+S | - | - | Tout |
| Gérer les médias | - | - | - | - | L+C | L+C+M | L+C+M+S | - | L | Tout |
| Gérer les abonnements | - | - | - | - | - | L | L | L+C+M | L | Tout |
| Gérer les abonnés | - | - | - | Lim. | - | L | L | L+C+M+S | L | Tout |
| Consulter les statistiques | - | - | - | L-perso | L-perso | L | L | L | L | Tout |
| Modérer les commentaires | - | - | - | - | - | L+M | L+M+S | - | - | Tout |
| Envoyer newsletters | - | - | - | - | - | L+C | L+C+M+S | L+C+M | - | Tout |
| Gérer facturation | - | L-perso | L-perso | L-perso | - | - | - | L+C+M | L | Tout |
| Gérer utilisateurs backoffice | - | - | - | - | - | - | - | - | L+C+M+S | Tout |
| Paramètres techniques | - | - | - | - | - | - | - | - | L+M | Tout |
| Journal d'audit | - | - | - | - | - | L-perso | L | L | L+M | Tout |

---

## 5. Architecture fonctionnelle

L'architecture fonctionnelle organise la plateforme en **22 épiques** regroupés en trois blocs complémentaires : le backoffice (15 épiques), le frontend (5 épiques) et le socle transverse (2 épiques). Chaque épique encapsule une responsabilité claire et peut évoluer indépendamment.

### 5.1. Vue d'ensemble des blocs

| Bloc | Nb épiques | Responsabilité principale |
|---|:-:|---|
| **Backoffice** | 15 | Production éditoriale, gestion du magazine, gestion commerciale, administration système. |
| **Frontend** | 5 | Consultation des contenus par les visiteurs et abonnés, tunnel d'abonnement et de paiement, espace abonné. |
| **Transverse** | 2 | SEO, performance, cache, sécurité, conformité, accessibilité — socle qui irrigue tous les autres blocs. |

### 5.2. Liste complète des épiques

| ID | Bloc | Épique | Description | Nb US |
|---|---|---|---|:-:|
| **EP-01** | Backoffice | **Gestion des utilisateurs & rôles** | Création et gestion des comptes backoffice (rédacteurs, chefs, admins, gestionnaires commerciaux). Rôles, permissions, sessions, authentification forte. | 9 |
| **EP-02** | Backoffice | **Gestion éditoriale — Articles** | CRUD complet des articles avec éditeur riche WYSIWYG, insertion médias, métadonnées SEO, gestion des auteurs, versions et historique. | 14 |
| **EP-03** | Backoffice | **Workflow éditorial** | Cycle de vie : brouillon → relecture → validation → planification → publication → archivage. Notifications, kanban rédactionnel. | 8 |
| **EP-04** | Backoffice | **Gestion des taxonomies** | Rubriques (La Une, Analyses, Succès, Boutique), sous-rubriques, tags, catégories Succès/Échec, slugs et URLs. | 6 |
| **EP-05** | Backoffice | **Gestion des médias** | Bibliothèque centralisée, upload, transformation automatique (WebP, thumbnails), métadonnées, droits et crédits photo. | 8 |
| **EP-06** | Backoffice | **Gestion des numéros magazine** | Création de numéros (papier/PDF), sommaire, couverture, association d'articles, stock papier, archives. | 9 |
| **EP-07** | Backoffice | **Abonnements & offres commerciales** | Formules (Digital, Combo, Entreprise), tarification, durées, codes promo, périodes d'essai, avantages. | 7 |
| **EP-08** | Backoffice | **Gestion des abonnés (CRM)** | Base abonnés, segmentation, historique, modifications manuelles, relances, licences multi-utilisateurs. | 9 |
| **EP-09** | Backoffice | **Paywall & règles d'accès** | Définition des niveaux d'accès par article, freemium avec compteur, preview partiel, achat à l'unité. | 6 |
| **EP-10** | Backoffice | **Newsletter & campagnes** | Listes de diffusion, templates, campagnes, A/B testing, statistiques, double opt-in, désabonnements. | 9 |
| **EP-11** | Backoffice | **Commandes & facturation** | Suivi commandes, factures PDF, relances, remboursements, rapports de revenus, paramétrage moyens de paiement. | 8 |
| **EP-12** | Backoffice | **Modération & interactions** | Modération des commentaires, filtrage automatique, bannissements, gestion des signalements. | 5 |
| **EP-13** | Backoffice | **Dashboards & analytics** | Tableaux de bord personnalisés rédacteur / chef / admin, analytics d'articles et d'abonnés, exports. | 7 |
| **EP-14** | Backoffice | **Audit & journalisation** | Journal horodaté de toutes les actions sensibles, export, recherche, conservation réglementaire. | 3 |
| **EP-15** | Backoffice | **Paramètres de la plateforme** | Paramètres globaux, pages légales, bannières, SMTP, SEO global, branding. | 6 |
| **EP-16** | Frontend | **Page d'accueil (La Une) & rubriques** | Slider, fil d'actu, choix de la rédaction, CTA newsletter, navigation par rubriques avec filtres et pagination. | 7 |
| **EP-17** | Frontend | **Consultation d'articles** | Lecteur article avec sommaire auto, bios auteurs, suggestions, temps de lecture, galeries, partage. | 7 |
| **EP-18** | Frontend | **Inscription, connexion & compte abonné** | Inscription email / social, connexion, 2FA, espace abonné complet, gestion préférences et données. | 12 |
| **EP-19** | Frontend | **Tunnel d'abonnement & paiement** | Page comparatif, checkout, paiement Mobile Money/carte, confirmation, renouvellement automatique. | 8 |
| **EP-20** | Frontend | **Boutique magazine & lecture PDF** | Dernier numéro, archives filtrables, commande papier/PDF, lecteur PDF protégé, watermark. | 6 |
| **EP-21** | Transverse | **SEO, performance & partage social** | Sitemap, Open Graph, Schema.org, cache CDN, images optimisées, lazy-loading, SSR. | 6 |
| **EP-22** | Transverse | **Sécurité, conformité & accessibilité** | OWASP Top 10, RGPD / Loi CI 2013-450, WCAG 2.1 AA, cookies, registre des traitements. | 5 |
| | | | **TOTAL** | **165** |

---

## 6. Architecture technique cible

L'architecture cible est pensée pour supporter la croissance (de 5 000 à 50 000 abonnés sur 3 ans), respecter les standards de sécurité et de performance, et permettre des évolutions indépendantes des modules.

### 6.1. Principes structurants

- **API-first** : toutes les fonctionnalités du backoffice et du frontend reposent sur la même couche API, ce qui permet une future application mobile native sans refonte.
- **Séparation stricte backoffice / frontend** : deux applications distinctes, deux domaines, des authentifications séparées.
- **SSR pour le frontend public** : impératif pour le SEO d'une plateforme éditoriale. Rendu côté serveur avec ISR (Incremental Static Regeneration) pour les articles publiés.
- **Stateless** : aucune donnée de session sur les serveurs applicatifs — scalabilité horizontale facilitée.
- **Infrastructure as code** : tout l'environnement décrit en code (Terraform ou équivalent), reproductible.

### 6.2. Stack technique recommandée

| Couche | Recommandation | Alternatives crédibles |
|---|---|---|
| **Backend API** | NestJS (Node.js/TypeScript) | Laravel (PHP), Django (Python), Spring Boot (Java) |
| **Frontend public** | Next.js (React) en SSR/ISR | Nuxt.js (Vue), Astro, SvelteKit |
| **Frontend backoffice** | React + Vite (SPA protégée) | Vue 3 + Vite, Angular |
| **Base de données** | PostgreSQL 16 | MySQL 8, Oracle (si infra existante) |
| **Cache / Queue** | Redis 7 | Memcached |
| **Recherche** | MeiliSearch self-hosted | Elasticsearch, Algolia (SaaS) |
| **Stockage fichiers** | S3-compatible (AWS S3, Scaleway, MinIO) | Stockage local avec rsync |
| **CDN / Edge** | Cloudflare | AWS CloudFront, Bunny.net |
| **Email transactionnel** | SendGrid ou Mailjet | AWS SES, Brevo |
| **Email marketing** | Mailjet | Brevo, Mailchimp |
| **SMS** | API Orange CI ou InfoBip | TextMe, Twilio |
| **Paiement CI** | CinetPay (Mobile Money + Carte) | PayDunya, Flutterwave |
| **Authentification** | Keycloak ou auth custom JWT | Auth0, Firebase Auth |
| **Monitoring** | Sentry (erreurs) + Prometheus/Grafana | Datadog, New Relic |
| **Logs** | Grafana Loki | ELK, Datadog Logs |
| **CI/CD** | GitHub Actions ou GitLab CI | Jenkins, CircleCI |
| **Hébergement** | OVH ou Scaleway (FR), AWS eu-west-3 | Hébergeur local CI (si SLA suffisant) |

### 6.3. Schéma logique de haut niveau

```
┌─────────────────────────────────────────────────────────────────┐
│                          UTILISATEURS                             │
│   Visiteurs · Abonnés (D/C/E) · Équipe BO · Admins système        │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                      CDN (Cloudflare)                             │
│        Cache · WAF · Anti-DDoS · SSL/TLS · Rate limiting          │
└─────────────────────────────────────────────────────────────────┘
              │                                    │
              ▼                                    ▼
    ┌──────────────────┐              ┌──────────────────┐
    │  FRONTEND PUBLIC │              │   BACKOFFICE      │
    │  Next.js SSR     │              │   React SPA       │
    │  (geniemag.ci)   │              │   (admin.*.ci)    │
    └──────────────────┘              └──────────────────┘
              │                                    │
              └────────────────┬───────────────────┘
                               ▼
            ┌────────────────────────────────────┐
            │          API REST/GraphQL           │
            │         NestJS · JWT · Roles        │
            │  ┌─────────┬─────────┬────────────┐ │
            │  │Articles │Subs     │Paiement    │ │
            │  │Medias   │Users    │Newsletter  │ │
            │  │Workflow │Orders   │Analytics   │ │
            │  └─────────┴─────────┴────────────┘ │
            └────────────────────────────────────┘
                               │
        ┌──────────────┬───────┼───────┬──────────────┐
        ▼              ▼       ▼       ▼              ▼
   ┌────────┐    ┌─────────┐ ┌────┐ ┌──────────┐ ┌──────────┐
   │Postgres│    │MeiliSearch│ │Redis│ │Stockage S3│ │Workers   │
   │  16    │    │ Index    │ │Cache│ │ (médias, │ │ async    │
   │Primary │    │  articles│ │Queue│ │ PDFs)    │ │ (email,  │
   │+Replica│    │          │ │     │ │          │ │ PDF gen) │
   └────────┘    └──────────┘ └────┘ └──────────┘ └──────────┘
                               │
                               ▼
     ┌────────────────────────────────────────────────┐
     │           INTÉGRATIONS EXTERNES                 │
     │  CinetPay · SendGrid · Orange SMS · Sentry ·    │
     │  Matomo · Buffer · Apple News · Google News     │
     └────────────────────────────────────────────────┘
```

### 6.4. Environnements

Quatre environnements doivent être mis en place dès la phase MVP :

| Env. | Public | Usage |
|---|---|---|
| **LOCAL** | Développeurs | Environnement de développement sur poste, avec BD locale. |
| **INT** | Équipe technique | Intégration continue, validation des branches avant merge. |
| **REC** | Équipe produit + rédaction | Recette fonctionnelle, données de test, validation avant mise en prod. |
| **PROD** | Utilisateurs finaux | Production, données réelles, monitoring 24/7, sauvegardes quotidiennes. |

---

## 7. Modèle de données

Le modèle de données conceptuel s'organise autour de **20 entités principales** regroupées en cinq grands domaines.

### 7.1. Domaine Identité & accès

| Entité | Description |
|---|---|
| **User** | Utilisateur (visiteur, abonné ou membre du backoffice). Attributs : `id`, `email`, `password_hash`, `first_name`, `last_name`, `phone`, `type` (abonné/backoffice), `status`, `created_at`, `2fa_enabled`. |
| **Role** | Rôle fonctionnel (VIS, AB-D, AB-C, AB-E, RED, CHEF, EDIT, COM, ADM, SUP). Un utilisateur peut avoir plusieurs rôles. |
| **Permission** | Permission atomique sur un objet (`article.read`, `article.write`, `subscription.manage`...). Permissions attribuées aux rôles. |
| **Session** | Session d'un utilisateur authentifié : `token`, `expiration`, `device_info`, `ip`, `last_activity`. |

### 7.2. Domaine Contenu éditorial

| Entité | Description |
|---|---|
| **Article** | Article éditorial. Attributs : `id`, `title`, `slug`, `lede`, `body` (JSON riche), `cover_media_id`, `status` (draft/review/scheduled/published/archived), `access_level`, `published_at`, `reading_time_minutes`. |
| **ArticleVersion** | Version d'un article pour l'historique. Une version créée à chaque sauvegarde. |
| **Author** | Fiche publique d'auteur (distincte du User backoffice). Permet de signer des articles par des auteurs externes/invités. |
| **Category** | Rubrique principale (La Une, Analyses, Succès, Boutique) et sous-rubriques. |
| **Tag** | Étiquette libre pour navigation transversale. |
| **EditorialCategory** | Catégorie éditoriale (Succès, Échec constructif, Interview, Portrait, Tribune, Analyse). |
| **Media** | Fichier média (image, PDF, vidéo). Attributs : `id`, `url`, `mime_type`, `width`, `height`, `alt_text`, `caption`, `credit`, `license`. |
| **MediaCollection** | Regroupement de médias (ex: photos d'un événement). |

### 7.3. Domaine Magazine

| Entité | Description |
|---|---|
| **Magazine** | Configuration globale du magazine (nom, baseline, ISSN, ...). |
| **MagazineIssue** | Numéro du magazine. Attributs : `id`, `number`, `title`, `theme`, `cover_media_id`, `pdf_url`, `publication_date`, `price_paper`, `price_pdf`, `stock_paper`, `status`. |
| **IssueSummaryEntry** | Entrée du sommaire d'un numéro : page, titre, résumé, lien vers article numérique optionnel. |

### 7.4. Domaine Commercial

| Entité | Description |
|---|---|
| **SubscriptionPlan** | Formule d'abonnement. Attributs : `id`, `code`, `name`, `description`, `price`, `currency`, `duration_months`, `features` (JSON), `is_active`. |
| **Subscription** | Abonnement actif d'un utilisateur. Attributs : `id`, `user_id`, `plan_id`, `start_date`, `end_date`, `status`, `auto_renewal`. |
| **PromoCode** | Code promotionnel : `code`, `type` (%|fixed), `value`, `valid_from`, `valid_until`, `max_uses`, `plans_eligible`. |
| **Order** | Commande (abonnement, numéro, article à l'unité). Attributs : `id`, `user_id`, `amount`, `currency`, `status`, `payment_method`, `items` (JSON). |
| **Payment** | Transaction de paiement. Attributs : `id`, `order_id`, `provider` (cinetpay...), `provider_ref`, `status`, `amount`, `fees`, `raw_response`. |
| **Invoice** | Facture générée. Attributs : `id`, `order_id`, `number` (séquentiel), `pdf_url`, `issued_at`, `amount_ht`, `tva`, `amount_ttc`. |
| **AccessRight** | Droit d'accès ponctuel à un article (achat à l'unité, article offert). Attributs : `user_id`, `article_id`, `granted_at`, `expires_at`, `source`. |

### 7.5. Domaine Communication & engagement

| Entité | Description |
|---|---|
| **Newsletter** | Liste de diffusion : nom, type, is_active. |
| **NewsletterSubscription** | Abonnement d'un utilisateur à une liste : `user_id` (ou `email` pour les non-inscrits), `newsletter_id`, `confirmed_at`, `unsubscribed_at`. |
| **Campaign** | Campagne email envoyée : `subject`, `template_id`, `target_segment`, `scheduled_at`, `sent_at`, `stats` (opens, clicks). |
| **Comment** | Commentaire posté sur un article : `author_user_id`, `article_id`, `content`, `status` (pending/approved/refused), `parent_id` pour les réponses. |
| **AuditLog** | Journal d'audit : `id`, `user_id`, `action`, `object_type`, `object_id`, `ip`, `user_agent`, `created_at`, `payload` (JSON). |

---

## 8. Backlog détaillé — 166 user stories

Les user stories sont organisées par épique. Chacune dispose d'un identifiant unique, d'un acteur principal, d'une priorité (P0 à P3), d'une phase de livraison (MVP/V1/V2/V3) et d'une estimation en T-shirt size (XS à XL).

### 8.1. Conventions

**Priorités :**

| Niveau | Signification |
|---|---|
| **P0** — Critique | Indispensable au lancement MVP. Sans cela, le produit ne peut pas fonctionner. |
| **P1** — Haute | Nécessaire pour la V1. Fonctionnalité attendue par les utilisateurs dès les premiers mois. |
| **P2** — Moyenne | Amélioration significative. À intégrer en V2 selon la maturité et les retours utilisateurs. |
| **P3** — Basse | Nice-to-have. Peut être repoussé en V3 sans impact business majeur. |

**Estimations T-shirt :** XS (< 0,5 j) · S (0,5 à 1 j) · M (1 à 3 j) · L (3 à 5 j) · XL (> 5 j)

**Format user story :** *"En tant que [rôle], je veux [action/fonctionnalité] afin de [bénéfice]"*

### 8.2. EP-01 — Gestion des utilisateurs & rôles

> *Bloc : **Backoffice** · 9 user stories*

Création et gestion des comptes backoffice (rédacteurs, chefs, admins, gestionnaires commerciaux). Rôles, permissions, sessions, authentification forte.

#### US-001 — Créer un utilisateur backoffice

| Attribut | Valeur |
|---|---|
| **Acteur** | ADM |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant qu'administrateur système, je veux créer un compte backoffice pour un nouveau collaborateur afin qu'il puisse accéder à ses outils de travail.

**Critères d'acceptation :**

- Saisie : nom, prénom, email pro, téléphone, rôle initial, statut actif/inactif
- Mot de passe provisoire envoyé par email avec lien d'activation (validité 72h)
- Email pro unique dans la plateforme
- Action tracée dans le journal d'audit

#### US-002 — Modifier un utilisateur backoffice

| Attribut | Valeur |
|---|---|
| **Acteur** | ADM |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `S` |
| **Type** | Fonctionnelle |

**User story :** En tant qu'administrateur système, je veux modifier les informations d'un utilisateur existant afin de maintenir la base à jour.

**Critères d'acceptation :**

- Modification possible : nom, email, téléphone, rôle, statut
- Changement d'email déclenche une revalidation
- Changement de rôle impacte les permissions immédiatement
- Historique des modifications conservé

#### US-003 — Désactiver / réactiver un utilisateur

| Attribut | Valeur |
|---|---|
| **Acteur** | ADM |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `S` |
| **Type** | Fonctionnelle |

**User story :** En tant qu'administrateur système, je veux désactiver un utilisateur lorsqu'il quitte l'entreprise afin de sécuriser l'accès à la plateforme.

**Critères d'acceptation :**

- Désactivation termine les sessions actives immédiatement
- Utilisateur désactivé conserve son historique
- Réactivation possible avec réenvoi d'invitation
- Jamais de suppression physique (RGPD + audit)

#### US-004 — Réinitialiser le mot de passe

| Attribut | Valeur |
|---|---|
| **Acteur** | ADM |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `S` |
| **Type** | Fonctionnelle |

**User story :** En tant qu'administrateur système, je veux réinitialiser le mot de passe d'un utilisateur qui l'a oublié afin de débloquer rapidement son accès.

**Critères d'acceptation :**

- Envoi d'un lien de réinitialisation (validité 1h)
- Nouveau mot de passe doit respecter la politique de complexité
- L'ancien mot de passe devient invalide immédiatement
- Notification au détenteur et au demandeur

#### US-005 — Définir les permissions d'un rôle

| Attribut | Valeur |
|---|---|
| **Acteur** | SUP |
| **Priorité** | `P1` |
| **Phase** | `V1` |
| **Estimation** | `L` |
| **Type** | Fonctionnelle |

**User story :** En tant que super administrateur, je veux configurer finement les permissions par rôle afin d'adapter les droits aux besoins réels.

**Critères d'acceptation :**

- Grille de permissions par module et par action (CRUD)
- Création de rôles personnalisés
- Rôles système non modifiables (Super Admin)
- Prévisualisation de l'impact avant enregistrement

#### US-006 — Authentifier avec 2FA

| Attribut | Valeur |
|---|---|
| **Acteur** | Tous BO |
| **Priorité** | `P1` |
| **Phase** | `V1` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |
| **Dépendances** | Sécurité |

**User story :** En tant qu'utilisateur backoffice, je veux activer une double authentification afin de sécuriser l'accès à des données éditoriales et commerciales sensibles.

**Critères d'acceptation :**

- Options : TOTP (Google Auth, Microsoft Auth) et SMS
- Code de récupération à usage unique généré
- Obligatoire pour les rôles ADM et SUP
- Journal des tentatives

#### US-007 — Consulter les sessions actives

| Attribut | Valeur |
|---|---|
| **Acteur** | Tous BO |
| **Priorité** | `P2` |
| **Phase** | `V1` |
| **Estimation** | `S` |
| **Type** | Fonctionnelle |

**User story :** En tant qu'utilisateur, je veux voir mes sessions actives afin de pouvoir déconnecter un appareil suspect.

**Critères d'acceptation :**

- Liste : appareil, navigateur, localisation approximative, date de dernière activité
- Déconnexion distante d'une session
- Déconnexion de toutes les sessions sauf la courante

#### US-008 — Journal d'activité utilisateur

| Attribut | Valeur |
|---|---|
| **Acteur** | ADM |
| **Priorité** | `P2` |
| **Phase** | `V1` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant qu'administrateur, je veux consulter l'historique détaillé des actions d'un utilisateur afin de détecter d'éventuels comportements à risque.

**Critères d'acceptation :**

- Filtres par période, action, objet
- Export CSV
- Conservation 12 mois minimum
- Conforme RGPD

#### US-009 — Politique de mot de passe

| Attribut | Valeur |
|---|---|
| **Acteur** | ADM |
| **Priorité** | `P1` |
| **Phase** | `V1` |
| **Estimation** | `S` |
| **Type** | Fonctionnelle |
| **Dépendances** | NF |

**User story :** En tant qu'administrateur système, je veux définir une politique de mot de passe afin de respecter les standards de sécurité.

**Critères d'acceptation :**

- Longueur minimale (12 caractères conseillés)
- Complexité (majuscules, chiffres, caractères spéciaux)
- Expiration optionnelle (90 jours)
- Historique de non-réutilisation des 5 derniers

### 8.3. EP-02 — Gestion éditoriale — Articles

> *Bloc : **Backoffice** · 14 user stories*

CRUD complet des articles avec éditeur riche WYSIWYG, insertion médias, métadonnées SEO, gestion des auteurs, versions et historique.

#### US-010 — Créer un brouillon d'article

| Attribut | Valeur |
|---|---|
| **Acteur** | RED |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant que rédacteur, je veux créer un brouillon d'article afin de commencer à rédiger un sujet.

**Critères d'acceptation :**

- Auto-sauvegarde toutes les 30 secondes
- Statut par défaut : Brouillon
- Rédacteur courant est l'auteur par défaut
- Slug généré automatiquement à partir du titre (modifiable)

#### US-011 — Éditeur riche WYSIWYG

| Attribut | Valeur |
|---|---|
| **Acteur** | RED |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `XL` |
| **Type** | Fonctionnelle |

**User story :** En tant que rédacteur, je veux un éditeur visuel avec mise en forme afin de produire un article soigné sans connaître HTML.

**Critères d'acceptation :**

- Blocs : paragraphe, titre H2/H3/H4, liste, citation, encadré, séparateur, code
- Formatage : gras, italique, souligné, lien, surlignage
- Insertion : image, vidéo YouTube, tweet, PDF, infographie, tableau
- Mode plein écran et mode focus

#### US-012 — Insérer des médias dans l'article

| Attribut | Valeur |
|---|---|
| **Acteur** | RED |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |
| **Dépendances** | EP-05 |

**User story :** En tant que rédacteur, je veux insérer des images et vidéos dans mon article afin de l'illustrer.

**Critères d'acceptation :**

- Sélection depuis la bibliothèque ou upload direct
- Tailles d'affichage : large, medium, inline
- Légende, alt text, crédit photo obligatoires
- Previsualisation responsive

#### US-013 — Définir l'image à la Une (cover)

| Attribut | Valeur |
|---|---|
| **Acteur** | RED |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant que rédacteur, je veux définir une image de couverture afin qu'elle serve d'illustration principale.

**Critères d'acceptation :**

- Une seule cover par article, obligatoire pour publier
- Recadrage intégré (ratios 16:9, 4:3, 1:1)
- Alt text obligatoire
- Prévisualisation dans les différents formats (card, slider, social)

#### US-014 — Saisir les métadonnées SEO

| Attribut | Valeur |
|---|---|
| **Acteur** | RED |
| **Priorité** | `P1` |
| **Phase** | `MVP` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |
| **Dépendances** | EP-21 |

**User story :** En tant que rédacteur, je veux renseigner titre SEO, meta description et URL personnalisée afin d'optimiser le référencement.

**Critères d'acceptation :**

- Titre SEO (limite 60 car., compteur)
- Meta description (limite 160 car., compteur)
- URL/slug personnalisable
- Aperçu Google en temps réel
- Aperçu Open Graph Facebook / Twitter

#### US-015 — Gérer l'auteur d'un article

| Attribut | Valeur |
|---|---|
| **Acteur** | CHEF |
| **Priorité** | `P1` |
| **Phase** | `V1` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant que rédacteur en chef, je veux attribuer un article à un auteur interne ou externe afin de correctement créditer la contribution.

**Critères d'acceptation :**

- Sélection dans la liste des auteurs
- Création à la volée d'un auteur externe (invité, pigiste)
- Co-signature possible (plusieurs auteurs)
- Photo et bio auteur affichées sur l'article

#### US-016 — Ajouter rubrique et tags

| Attribut | Valeur |
|---|---|
| **Acteur** | RED |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `S` |
| **Type** | Fonctionnelle |
| **Dépendances** | EP-04 |

**User story :** En tant que rédacteur, je veux classer mon article par rubrique et tags afin de faciliter la navigation et le SEO.

**Critères d'acceptation :**

- Une rubrique principale obligatoire
- Sous-rubrique optionnelle
- Tags libres avec autocomplétion (min 2, max 10)
- Catégorie éditoriale (Succès, Échec constructif, Analyse, Opinion)

#### US-017 — Rédiger un chapô (lede)

| Attribut | Valeur |
|---|---|
| **Acteur** | RED |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `XS` |
| **Type** | Fonctionnelle |

**User story :** En tant que rédacteur, je veux rédiger un chapô distinct afin de résumer l'article en quelques lignes percutantes.

**Critères d'acceptation :**

- Chapô obligatoire pour publier (150-300 caractères)
- Affiché dans les cartes d'article et en haut du contenu
- Pas de mise en forme (texte simple)

#### US-018 — Format dossier multi-chapitres

| Attribut | Valeur |
|---|---|
| **Acteur** | CHEF |
| **Priorité** | `P2` |
| **Phase** | `V1` |
| **Estimation** | `L` |
| **Type** | Fonctionnelle |

**User story :** En tant que rédacteur en chef, je veux créer un dossier composé de plusieurs chapitres afin de proposer des enquêtes longues.

**Critères d'acceptation :**

- Un dossier est un parent avec N chapitres ordonnés
- Chaque chapitre est un article à part entière
- Navigation inter-chapitres sur le front
- Sommaire du dossier généré automatiquement

#### US-019 — Ajouter une citation mise en avant

| Attribut | Valeur |
|---|---|
| **Acteur** | RED |
| **Priorité** | `P2` |
| **Phase** | `V1` |
| **Estimation** | `S` |
| **Type** | Fonctionnelle |

**User story :** En tant que rédacteur, je veux insérer une citation visuellement distinctive afin de faire ressortir une phrase clé.

**Critères d'acceptation :**

- Citation mise en forme en pleine largeur avec attribution
- Option partage direct de la citation sur réseaux sociaux
- Image de fond optionnelle

#### US-020 — Historique et versions

| Attribut | Valeur |
|---|---|
| **Acteur** | RED |
| **Priorité** | `P1` |
| **Phase** | `V1` |
| **Estimation** | `L` |
| **Type** | Fonctionnelle |

**User story :** En tant que rédacteur, je veux accéder à l'historique des modifications afin de revenir à une version antérieure en cas d'erreur.

**Critères d'acceptation :**

- Une version créée à chaque sauvegarde manuelle
- Horodatage, auteur de la modification
- Diff visuelle entre deux versions
- Restauration possible (crée une nouvelle version)

#### US-021 — Dupliquer un article

| Attribut | Valeur |
|---|---|
| **Acteur** | RED |
| **Priorité** | `P2` |
| **Phase** | `V1` |
| **Estimation** | `XS` |
| **Type** | Fonctionnelle |

**User story :** En tant que rédacteur, je veux dupliquer un article existant afin de gagner du temps sur un contenu similaire.

**Critères d'acceptation :**

- Duplication avec tout le contenu et les métadonnées
- Titre suffixé "(copie)"
- Statut forcé à Brouillon
- URL/slug régénéré

#### US-022 — Supprimer un article (soft delete)

| Attribut | Valeur |
|---|---|
| **Acteur** | CHEF |
| **Priorité** | `P1` |
| **Phase** | `MVP` |
| **Estimation** | `S` |
| **Type** | Fonctionnelle |

**User story :** En tant que rédacteur en chef, je veux supprimer un article afin de retirer un contenu obsolète ou erroné.

**Critères d'acceptation :**

- Suppression logique (non physique)
- Corbeille accessible 30 jours
- Restauration possible depuis la corbeille
- URL retourne 410 Gone avec redirection si configurée

#### US-023 — Articles liés manuels

| Attribut | Valeur |
|---|---|
| **Acteur** | RED |
| **Priorité** | `P2` |
| **Phase** | `V2` |
| **Estimation** | `S` |
| **Type** | Fonctionnelle |

**User story :** En tant que rédacteur, je veux sélectionner manuellement des articles liés afin d'enrichir les recommandations de lecture.

**Critères d'acceptation :**

- Jusqu'à 4 articles liés sélectionnés manuellement
- Affichés en bas d'article sur le front
- Complément aux recommandations automatiques

#### US-024 — Contenu sponsorisé / publi-info

| Attribut | Valeur |
|---|---|
| **Acteur** | EDIT |
| **Priorité** | `P2` |
| **Phase** | `V2` |
| **Estimation** | `S` |
| **Type** | Fonctionnelle |

**User story :** En tant qu'administrateur éditorial, je veux pouvoir marquer un contenu comme sponsorisé afin de respecter les obligations de transparence publicitaire.

**Critères d'acceptation :**

- Flag "Contenu sponsorisé" avec nom de l'annonceur
- Badge visible sur les cards et en tête d'article
- Traçabilité dans les reportings

### 8.4. EP-03 — Workflow éditorial

> *Bloc : **Backoffice** · 8 user stories*

Cycle de vie : brouillon → relecture → validation → planification → publication → archivage. Notifications, kanban rédactionnel.

#### US-025 — Soumettre un article à relecture

| Attribut | Valeur |
|---|---|
| **Acteur** | RED |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `S` |
| **Type** | Fonctionnelle |

**User story :** En tant que rédacteur, je veux soumettre mon article à relecture afin qu'il soit validé avant publication.

**Critères d'acceptation :**

- Passage du statut Brouillon → En relecture
- Notification au rédacteur en chef
- Verrouillage de l'édition pendant la relecture (ou mode collaboratif)
- Champ message au relecteur

#### US-026 — Relire et commenter un article

| Attribut | Valeur |
|---|---|
| **Acteur** | CHEF |
| **Priorité** | `P1` |
| **Phase** | `MVP` |
| **Estimation** | `L` |
| **Type** | Fonctionnelle |

**User story :** En tant que rédacteur en chef, je veux relire un article soumis et laisser des commentaires afin de guider l'auteur vers une version publiable.

**Critères d'acceptation :**

- Commentaires contextuels sur des passages (annotation)
- Commentaire général de retour
- Suggestions de modification visibles
- Historique des échanges

#### US-027 — Valider ou refuser un article

| Attribut | Valeur |
|---|---|
| **Acteur** | CHEF |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `S` |
| **Type** | Fonctionnelle |

**User story :** En tant que rédacteur en chef, je veux valider un article afin qu'il devienne publiable ou le renvoyer en révision.

**Critères d'acceptation :**

- Bouton "Valider" → statut Validé, prêt à publier
- Bouton "Refuser / À corriger" → retour à Brouillon avec commentaire obligatoire
- Notification à l'auteur

#### US-028 — Planifier la publication

| Attribut | Valeur |
|---|---|
| **Acteur** | CHEF |
| **Priorité** | `P1` |
| **Phase** | `MVP` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant que rédacteur en chef, je veux planifier la publication d'un article afin de respecter un calendrier éditorial.

**Critères d'acceptation :**

- Date et heure de publication (fuseau Côte d'Ivoire / UTC)
- Statut passe à Planifié
- Publication automatique à l'heure dite
- Annulation possible avant l'échéance

#### US-029 — Publier immédiatement

| Attribut | Valeur |
|---|---|
| **Acteur** | CHEF |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant que rédacteur en chef, je veux publier un article validé afin qu'il devienne accessible au public.

**Critères d'acceptation :**

- Statut Validé → Publié, horodatage de publication
- Invalidation du cache CDN des pages impactées (rubrique, accueil)
- Envoi éventuel vers la newsletter
- Diffusion réseaux sociaux (si configurée)

#### US-030 — Dépublier un article

| Attribut | Valeur |
|---|---|
| **Acteur** | CHEF |
| **Priorité** | `P1` |
| **Phase** | `MVP` |
| **Estimation** | `S` |
| **Type** | Fonctionnelle |

**User story :** En tant que rédacteur en chef, je veux dépublier un article afin de le retirer temporairement du site.

**Critères d'acceptation :**

- Statut Publié → Dépublié
- URL retourne 404 ou redirige selon configuration
- Action tracée avec justification
- Republication en 1 clic

#### US-031 — Kanban rédactionnel

| Attribut | Valeur |
|---|---|
| **Acteur** | CHEF |
| **Priorité** | `P2` |
| **Phase** | `V1` |
| **Estimation** | `L` |
| **Type** | Fonctionnelle |

**User story :** En tant que rédacteur en chef, je veux une vue kanban des articles par statut afin de piloter visuellement la chaîne de production.

**Critères d'acceptation :**

- Colonnes : Idée, Brouillon, En relecture, Validé, Planifié, Publié
- Drag & drop pour changer le statut (droits requis)
- Filtres : auteur, rubrique, période
- Badges : priorité, urgence

#### US-032 — Notifications workflow

| Attribut | Valeur |
|---|---|
| **Acteur** | Tous BO |
| **Priorité** | `P1` |
| **Phase** | `V1` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant qu'utilisateur impliqué dans le workflow, je veux être notifié des changements d'état afin de réagir rapidement.

**Critères d'acceptation :**

- Notifications in-app et email
- Événements : soumission, validation, refus, publication
- Préférences par utilisateur (fréquence, canaux)
- Digest quotidien en option

### 8.5. EP-04 — Gestion des taxonomies

> *Bloc : **Backoffice** · 6 user stories*

Rubriques (La Une, Analyses, Succès, Boutique), sous-rubriques, tags, catégories Succès/Échec, slugs et URLs.

#### US-033 — Gérer les rubriques principales

| Attribut | Valeur |
|---|---|
| **Acteur** | EDIT |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant qu'administrateur éditorial, je veux créer et gérer les rubriques principales afin de structurer le site.

**Critères d'acceptation :**

- CRUD : nom, slug, description, ordre d'affichage, icône, couleur d'accent
- 4 rubriques initiales : La Une, Analyses & Stratégies, Succès Stories, Boutique
- Impossible de supprimer une rubrique contenant des articles actifs

#### US-034 — Gérer les sous-rubriques

| Attribut | Valeur |
|---|---|
| **Acteur** | EDIT |
| **Priorité** | `P1` |
| **Phase** | `V1` |
| **Estimation** | `S` |
| **Type** | Fonctionnelle |

**User story :** En tant qu'administrateur éditorial, je veux créer des sous-rubriques afin d'affiner la taxonomie.

**Critères d'acceptation :**

- Rattachement à une rubrique parent
- Hiérarchie sur 2 niveaux maximum
- Slug hérité + suffixe (ex: /analyses/etudes-de-cas)

#### US-035 — Gérer les tags

| Attribut | Valeur |
|---|---|
| **Acteur** | EDIT |
| **Priorité** | `P1` |
| **Phase** | `V1` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant qu'administrateur éditorial, je veux gérer les tags afin d'offrir une navigation transversale.

**Critères d'acceptation :**

- Création libre par les rédacteurs (avec autocomplétion)
- Fusion de tags (opération administrateur)
- Page de tag avec listing d'articles
- Comptage d'usage

#### US-036 — Gérer les catégories éditoriales

| Attribut | Valeur |
|---|---|
| **Acteur** | EDIT |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `S` |
| **Type** | Fonctionnelle |

**User story :** En tant qu'administrateur éditorial, je veux gérer les catégories (Succès, Échec constructif, Interview, Portrait, Tribune, Analyse) afin de filtrer par angle éditorial.

**Critères d'acceptation :**

- Liste fixe configurable
- Obligatoire par article
- Badge distinctif sur le front

#### US-037 — URLs et redirections

| Attribut | Valeur |
|---|---|
| **Acteur** | EDIT |
| **Priorité** | `P1` |
| **Phase** | `V1` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |
| **Dépendances** | EP-21 |

**User story :** En tant qu'administrateur éditorial, je veux personnaliser les URLs et gérer les redirections afin de préserver le SEO lors de réorganisations.

**Critères d'acceptation :**

- Éditer le slug d'un article ou d'une rubrique
- Créer manuellement une redirection 301
- Redirection automatique si le slug change

#### US-038 — Taxonomie auteurs

| Attribut | Valeur |
|---|---|
| **Acteur** | EDIT |
| **Priorité** | `P1` |
| **Phase** | `V1` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant qu'administrateur éditorial, je veux gérer les pages auteurs afin de valoriser les signatures.

**Critères d'acceptation :**

- Fiche auteur : photo, bio, spécialités, liens sociaux
- Page auteur listant tous ses articles
- Séparation entre compte utilisateur BO et fiche auteur publique

### 8.6. EP-05 — Gestion des médias

> *Bloc : **Backoffice** · 8 user stories*

Bibliothèque centralisée, upload, transformation automatique (WebP, thumbnails), métadonnées, droits et crédits photo.

#### US-039 — Bibliothèque de médias centralisée

| Attribut | Valeur |
|---|---|
| **Acteur** | RED |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `L` |
| **Type** | Fonctionnelle |

**User story :** En tant que rédacteur, je veux parcourir une bibliothèque centralisée afin de réutiliser des médias existants.

**Critères d'acceptation :**

- Vue grille et liste
- Miniatures, métadonnées, filtres (type, date, tag, rubrique)
- Recherche plein texte (nom, légende, alt)
- Pagination ou scroll infini

#### US-040 — Uploader des images

| Attribut | Valeur |
|---|---|
| **Acteur** | RED |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant que rédacteur, je veux uploader une image afin de l'utiliser dans un article.

**Critères d'acceptation :**

- Formats : JPG, PNG, WebP, SVG
- Taille max 10 Mo, dimensions min 800px de large
- Upload multiple
- Détection de doublons (hash)

#### US-041 — Transformation automatique d'images

| Attribut | Valeur |
|---|---|
| **Acteur** | Système |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `L` |
| **Type** | Fonctionnelle |
| **Dépendances** | NF |

**User story :** En tant que système, je dois générer automatiquement les déclinaisons d'une image afin d'optimiser la performance.

**Critères d'acceptation :**

- Variantes : 320, 640, 1024, 1920px
- Formats WebP et AVIF en plus de l'original
- Compression adaptative
- Servir en responsive srcset

#### US-042 — Uploader des PDFs

| Attribut | Valeur |
|---|---|
| **Acteur** | RED |
| **Priorité** | `P1` |
| **Phase** | `MVP` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant que rédacteur, je veux uploader un PDF afin de le proposer en téléchargement ou lecture.

**Critères d'acceptation :**

- Format PDF uniquement, taille max 50 Mo
- Prévisualisation en ligne
- Option de téléchargement protégé (abonnés)

#### US-043 — Métadonnées média

| Attribut | Valeur |
|---|---|
| **Acteur** | RED |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `S` |
| **Type** | Fonctionnelle |
| **Dépendances** | NF |

**User story :** En tant que rédacteur, je veux renseigner les métadonnées d'un média afin de respecter les obligations d'accessibilité et de crédits.

**Critères d'acceptation :**

- Alt text obligatoire (accessibilité)
- Légende
- Crédit photo (obligatoire)
- Licence (propre, Creative Commons, agence, droits réservés)
- Tags

#### US-044 — Organiser en collections

| Attribut | Valeur |
|---|---|
| **Acteur** | RED |
| **Priorité** | `P2` |
| **Phase** | `V1` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant que rédacteur, je veux organiser les médias en collections afin de faciliter leur retrouvement.

**Critères d'acceptation :**

- Collections / dossiers hiérarchiques
- Un média peut appartenir à plusieurs collections
- Droits par collection possibles

#### US-045 — Supprimer un média avec contrôle

| Attribut | Valeur |
|---|---|
| **Acteur** | EDIT |
| **Priorité** | `P1` |
| **Phase** | `V1` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant qu'administrateur éditorial, je veux supprimer un média en toute sécurité afin d'éviter les liens cassés.

**Critères d'acceptation :**

- Alerte si le média est utilisé dans des articles (liste affichée)
- Suppression soft delete + purge après 30 jours
- Remplacement proposé

#### US-046 — Watermark dynamique pour PDF abonnés

| Attribut | Valeur |
|---|---|
| **Acteur** | EDIT |
| **Priorité** | `P2` |
| **Phase** | `V1` |
| **Estimation** | `L` |
| **Type** | Fonctionnelle |
| **Dépendances** | Sécurité |

**User story :** En tant qu'administrateur éditorial, je veux qu'un watermark contenant l'email de l'abonné soit incrusté dans les PDFs téléchargés afin de tracer les fuites.

**Critères d'acceptation :**

- Watermark discret sur chaque page
- Personnalisation position, opacité
- Traçabilité : qui a téléchargé quand
- Configurable par numéro

### 8.7. EP-06 — Gestion des numéros magazine

> *Bloc : **Backoffice** · 9 user stories*

Création de numéros (papier/PDF), sommaire, couverture, association d'articles, stock papier, archives.

#### US-047 — Créer un numéro de magazine

| Attribut | Valeur |
|---|---|
| **Acteur** | EDIT |
| **Priorité** | `P1` |
| **Phase** | `V1` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant qu'administrateur éditorial, je veux créer un nouveau numéro afin de constituer l'édition mensuelle.

**Critères d'acceptation :**

- Métadonnées : n°, titre, sous-titre, mois, année, thème
- Date de parution
- Prix papier et prix PDF distincts

#### US-048 — Uploader la couverture

| Attribut | Valeur |
|---|---|
| **Acteur** | EDIT |
| **Priorité** | `P1` |
| **Phase** | `V1` |
| **Estimation** | `S` |
| **Type** | Fonctionnelle |
| **Dépendances** | EP-05 |

**User story :** En tant qu'administrateur éditorial, je veux uploader la couverture du numéro afin de l'afficher en boutique et sur la fiche.

**Critères d'acceptation :**

- Formats JPG/PNG/WebP
- Dimensions min 1200×1600 (ratio 3:4)
- Variantes auto (thumbnail, card, full)

#### US-049 — Saisir le sommaire d'un numéro

| Attribut | Valeur |
|---|---|
| **Acteur** | EDIT |
| **Priorité** | `P1` |
| **Phase** | `V1` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant qu'administrateur éditorial, je veux définir le sommaire paginé afin qu'il s'affiche en fiche produit.

**Critères d'acceptation :**

- Liste : numéro de page, titre de l'article, résumé court
- Réordonnancement drag & drop
- Association optionnelle à un article numérique publié

#### US-050 — Uploader le PDF du numéro

| Attribut | Valeur |
|---|---|
| **Acteur** | EDIT |
| **Priorité** | `P1` |
| **Phase** | `V1` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant qu'administrateur éditorial, je veux téléverser le PDF complet afin de le rendre disponible aux abonnés digitaux.

**Critères d'acceptation :**

- Upload sécurisé
- Stockage chiffré
- Extraction automatique du nombre de pages
- Validation manuelle avant mise en ligne

#### US-051 — Gérer le stock papier

| Attribut | Valeur |
|---|---|
| **Acteur** | COM |
| **Priorité** | `P2` |
| **Phase** | `V1` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant que gestionnaire commercial, je veux suivre le stock papier d'un numéro afin d'éviter les ventes en rupture.

**Critères d'acceptation :**

- Stock initial à la parution
- Décrément automatique à chaque commande
- Alerte sous seuil configurable
- Désactivation auto de la commande papier en rupture

#### US-052 — Archiver un numéro

| Attribut | Valeur |
|---|---|
| **Acteur** | EDIT |
| **Priorité** | `P1` |
| **Phase** | `V1` |
| **Estimation** | `S` |
| **Type** | Fonctionnelle |

**User story :** En tant qu'administrateur éditorial, je veux archiver un ancien numéro afin qu'il reste consultable en archives.

**Critères d'acceptation :**

- Statut Archivé après N mois
- Apparaît dans la section Archives
- Prix et conditions d'accès spécifiques (gratuit pour abonnés, payant à l'unité)

#### US-053 — Fiche détaillée d'un numéro

| Attribut | Valeur |
|---|---|
| **Acteur** | VIS/AB |
| **Priorité** | `P1` |
| **Phase** | `V1` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant que visiteur/abonné, je veux consulter la fiche complète d'un numéro afin de décider de son achat.

**Critères d'acceptation :**

- Couverture en grand, prix, description
- Sommaire complet
- CTA selon profil (acheter/lire pour abonnés)
- Extraits gratuits possibles

#### US-054 — Hors-séries et spéciaux

| Attribut | Valeur |
|---|---|
| **Acteur** | EDIT |
| **Priorité** | `P3` |
| **Phase** | `V2` |
| **Estimation** | `S` |
| **Type** | Fonctionnelle |

**User story :** En tant qu'administrateur éditorial, je veux créer des hors-séries afin de publier des numéros thématiques exceptionnels.

**Critères d'acceptation :**

- Flag "Hors-série" avec badge spécifique
- Prix et disponibilité indépendants
- Cycle de parution non mensuel

#### US-055 — Extraits gratuits d'un numéro

| Attribut | Valeur |
|---|---|
| **Acteur** | EDIT |
| **Priorité** | `P2` |
| **Phase** | `V2` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant qu'administrateur éditorial, je veux offrir des extraits gratuits d'un numéro payant afin d'inciter à l'achat.

**Critères d'acceptation :**

- Sélection d'articles extraits accessibles à tous
- Watermark "Extrait" sur le PDF extrait
- Analytics de conversion extrait → achat

### 8.8. EP-07 — Abonnements & offres commerciales

> *Bloc : **Backoffice** · 7 user stories*

Formules (Digital, Combo, Entreprise), tarification, durées, codes promo, périodes d'essai, avantages.

#### US-056 — Gérer les formules d'abonnement

| Attribut | Valeur |
|---|---|
| **Acteur** | ADM |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant qu'administrateur système, je veux créer et paramétrer les formules d'abonnement afin de proposer plusieurs offres.

**Critères d'acceptation :**

- Formules initiales : Digital 24k, Combo 48k, Entreprise 320k (tous FCFA/an)
- Nom, description, prix, durée, devise
- Liste des avantages (features) incluses
- Statut actif/archivé

#### US-057 — Paramétrer les avantages par formule

| Attribut | Valeur |
|---|---|
| **Acteur** | ADM |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `S` |
| **Type** | Fonctionnelle |

**User story :** En tant qu'administrateur système, je veux lister les avantages spécifiques d'une formule afin de les afficher clairement en boutique.

**Critères d'acceptation :**

- Avantages : accès PDF, nombre de numéros, événements, newsletter premium, licences multiples
- Ordre d'affichage
- Icône par avantage

#### US-058 — Créer un code promo

| Attribut | Valeur |
|---|---|
| **Acteur** | COM |
| **Priorité** | `P1` |
| **Phase** | `V1` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant que gestionnaire commercial, je veux créer un code promotionnel afin de lancer des campagnes d'acquisition.

**Critères d'acceptation :**

- Code, libellé, type (% ou montant fixe)
- Période de validité, nombre d'utilisations max, par client
- Formules éligibles
- Cumul autorisé ou non

#### US-059 — Période d'essai gratuite

| Attribut | Valeur |
|---|---|
| **Acteur** | ADM |
| **Priorité** | `P2` |
| **Phase** | `V1` |
| **Estimation** | `L` |
| **Type** | Fonctionnelle |

**User story :** En tant qu'administrateur système, je veux configurer une période d'essai afin de permettre aux prospects de tester.

**Critères d'acceptation :**

- Durée paramétrable (7 ou 14 jours)
- Unique par personne (détection email + moyen de paiement)
- Conversion auto en abonnement payant si moyen de paiement fourni
- Notification J-3 avant fin d'essai

#### US-060 — Offres spéciales temporaires

| Attribut | Valeur |
|---|---|
| **Acteur** | COM |
| **Priorité** | `P2` |
| **Phase** | `V2` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant que gestionnaire commercial, je veux créer des offres spéciales datées afin de stimuler les ventes lors d'événements.

**Critères d'acceptation :**

- Prix remisé pour une période
- Badge "Offre spéciale" visible sur la page de comparaison
- Retour automatique au tarif standard à la fin

#### US-061 — Formules sur-mesure Entreprise

| Attribut | Valeur |
|---|---|
| **Acteur** | COM |
| **Priorité** | `P2` |
| **Phase** | `V2` |
| **Estimation** | `L` |
| **Type** | Fonctionnelle |

**User story :** En tant que gestionnaire commercial, je veux créer des contrats Entreprise personnalisés afin de répondre aux demandes grands comptes.

**Critères d'acceptation :**

- Devis en mode projet avec nombre de licences, durée, prix négocié
- Validation après signature du devis
- Facturation adaptée (échéancier possible)

#### US-062 — Renouvellement automatique

| Attribut | Valeur |
|---|---|
| **Acteur** | ADM |
| **Priorité** | `P1` |
| **Phase** | `V1` |
| **Estimation** | `L` |
| **Type** | Fonctionnelle |

**User story :** En tant qu'administrateur système, je veux configurer le renouvellement automatique afin de maximiser la rétention.

**Critères d'acceptation :**

- Par défaut activé pour Digital et Combo
- Notification J-15, J-7, J-1 avant renouvellement
- Possibilité de désactiver pour l'abonné
- Tentatives de paiement et gestion d'échec

### 8.9. EP-08 — Gestion des abonnés (CRM)

> *Bloc : **Backoffice** · 9 user stories*

Base abonnés, segmentation, historique, modifications manuelles, relances, licences multi-utilisateurs.

#### US-063 — Liste des abonnés

| Attribut | Valeur |
|---|---|
| **Acteur** | COM |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant que gestionnaire commercial, je veux voir la liste des abonnés avec filtres afin de piloter la base.

**Critères d'acceptation :**

- Colonnes : nom, email, formule, statut, date début/fin, montant total
- Filtres : formule, statut (actif, expiré, résilié), période d'inscription
- Recherche plein texte
- Pagination, tri

#### US-064 — Fiche détaillée d'un abonné

| Attribut | Valeur |
|---|---|
| **Acteur** | COM |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `L` |
| **Type** | Fonctionnelle |

**User story :** En tant que gestionnaire commercial, je veux consulter la fiche complète d'un abonné afin d'assurer un service client de qualité.

**Critères d'acceptation :**

- Onglets : profil, abonnement actuel, historique, commandes, factures, tickets de support, communications
- Notes internes
- Tags

#### US-065 — Modifier manuellement un abonnement

| Attribut | Valeur |
|---|---|
| **Acteur** | COM |
| **Priorité** | `P1` |
| **Phase** | `V1` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |
| **Dépendances** | EP-14 |

**User story :** En tant que gestionnaire commercial, je veux modifier manuellement un abonnement en cas de situation particulière.

**Critères d'acceptation :**

- Changer la formule
- Prolonger / raccourcir la date de fin
- Offrir des jours gracieux
- Toute modification tracée avec justification obligatoire

#### US-066 — Suspendre / résilier

| Attribut | Valeur |
|---|---|
| **Acteur** | COM |
| **Priorité** | `P1` |
| **Phase** | `V1` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant que gestionnaire commercial, je veux suspendre ou résilier un abonnement afin de gérer les cas de non-paiement ou de demande client.

**Critères d'acceptation :**

- Suspension temporaire (accès bloqué, durée paramétrable)
- Résiliation (fin immédiate ou à date d'échéance)
- Motif obligatoire
- Notification à l'abonné

#### US-067 — Exporter la base abonnés

| Attribut | Valeur |
|---|---|
| **Acteur** | COM |
| **Priorité** | `P1` |
| **Phase** | `V1` |
| **Estimation** | `S` |
| **Type** | Fonctionnelle |
| **Dépendances** | RGPD |

**User story :** En tant que gestionnaire commercial, je veux exporter la base en CSV afin de l'utiliser dans des outils externes.

**Critères d'acceptation :**

- Filtres applicables avant export
- Choix des colonnes à exporter
- Traçabilité de l'export (qui, quand, quoi)
- Conformité RGPD (données strictement nécessaires)

#### US-068 — Segmenter les abonnés

| Attribut | Valeur |
|---|---|
| **Acteur** | COM |
| **Priorité** | `P2` |
| **Phase** | `V2` |
| **Estimation** | `L` |
| **Type** | Fonctionnelle |
| **Dépendances** | EP-10 |

**User story :** En tant que gestionnaire commercial, je veux créer des segments afin de cibler des communications précises.

**Critères d'acceptation :**

- Critères : formule, ancienneté, engagement, activité, zone géographique
- Segments dynamiques (recalculés automatiquement)
- Utilisation dans les campagnes newsletter

#### US-069 — Relances d'abonnements expirants

| Attribut | Valeur |
|---|---|
| **Acteur** | COM |
| **Priorité** | `P1` |
| **Phase** | `V1` |
| **Estimation** | `L` |
| **Type** | Fonctionnelle |

**User story :** En tant que gestionnaire commercial, je veux automatiser les relances afin de maximiser le taux de renouvellement.

**Critères d'acceptation :**

- Cycle : J-30, J-15, J-7, J-1, J+3, J+7
- Canaux : email, SMS
- Templates personnalisables
- Statistiques de conversion

#### US-070 — Gérer les licences Entreprise

| Attribut | Valeur |
|---|---|
| **Acteur** | AB-E |
| **Priorité** | `P2` |
| **Phase** | `V1` |
| **Estimation** | `L` |
| **Type** | Fonctionnelle |

**User story :** En tant qu'administrateur d'un abonnement Entreprise, je veux gérer les licences de mon entreprise afin d'en contrôler l'usage.

**Critères d'acceptation :**

- Liste des licences actives, inactives
- Invitation d'un collaborateur par email
- Désactivation / réactivation
- Rapport d'usage mensuel

#### US-071 — Notes et suivi commercial

| Attribut | Valeur |
|---|---|
| **Acteur** | COM |
| **Priorité** | `P2` |
| **Phase** | `V2` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant que gestionnaire commercial, je veux prendre des notes sur un abonné afin de tracer l'historique de la relation.

**Critères d'acceptation :**

- Notes libres horodatées et signées
- Catégorisation (appel, email, rdv, réclamation)
- Visibilité équipe commerciale
- Intégration dans la timeline de la fiche

### 8.10. EP-09 — Paywall & règles d'accès

> *Bloc : **Backoffice** · 6 user stories*

Définition des niveaux d'accès par article, freemium avec compteur, preview partiel, achat à l'unité.

#### US-072 — Définir le niveau d'accès d'un article

| Attribut | Valeur |
|---|---|
| **Acteur** | CHEF |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant que rédacteur en chef, je veux définir le niveau d'accès d'un article afin de configurer le paywall.

**Critères d'acceptation :**

- Niveaux : Libre (tous), Freemium (compteur), Premium (abonnés), Premium+ (niveau sup), Achat à l'unité
- Par défaut selon la rubrique
- Prévisualisation du rendu front

#### US-073 — Configurer le compteur freemium

| Attribut | Valeur |
|---|---|
| **Acteur** | ADM |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant qu'administrateur système, je veux configurer le nombre d'articles gratuits par mois pour les visiteurs non abonnés.

**Critères d'acceptation :**

- Quota mensuel paramétrable (défaut: 3 articles)
- Identification par compte ou cookie (30j)
- Reset mensuel
- Message pédagogique à l'approche du quota

#### US-074 — Afficher le paywall (front)

| Attribut | Valeur |
|---|---|
| **Acteur** | VIS |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `L` |
| **Type** | Fonctionnelle |

**User story :** En tant que visiteur ayant épuisé ses articles gratuits, je veux voir un paywall clair afin de comprendre les conditions d'accès.

**Critères d'acceptation :**

- Preview du début de l'article (2-3 paragraphes)
- Bloc paywall avec CTA : s'abonner, se connecter
- Message personnalisé selon le contexte (quota atteint, premium pur)
- Responsive mobile

#### US-075 — Achat d'un article à l'unité

| Attribut | Valeur |
|---|---|
| **Acteur** | VIS |
| **Priorité** | `P2` |
| **Phase** | `V1` |
| **Estimation** | `L` |
| **Type** | Fonctionnelle |
| **Dépendances** | EP-19 |

**User story :** En tant que visiteur, je veux acheter un article à l'unité afin d'accéder sans m'abonner.

**Critères d'acceptation :**

- Prix paramétrable par article ou par catégorie
- Paiement direct sans création de compte (option)
- Accès permanent au contenu acheté (30 jours min)
- Reçu par email

#### US-076 — Partage entre abonnés (offrir un article)

| Attribut | Valeur |
|---|---|
| **Acteur** | AB-D/C/E |
| **Priorité** | `P3` |
| **Phase** | `V2` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant qu'abonné, je veux offrir un article à un non-abonné afin de partager un contenu intéressant.

**Critères d'acceptation :**

- Quota mensuel paramétrable (ex: 5 articles/mois)
- Lien unique expirant sous 7 jours
- Destinataire accède sans s'inscrire
- Traçabilité

#### US-077 — Bypass paywall pour réseaux de diffusion

| Attribut | Valeur |
|---|---|
| **Acteur** | EDIT |
| **Priorité** | `P3` |
| **Phase** | `V2` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |
| **Dépendances** | EP-21 |

**User story :** En tant qu'administrateur éditorial, je veux autoriser certains réseaux (Google News, Apple News) à contourner le paywall afin d'améliorer le SEO.

**Critères d'acceptation :**

- Liste blanche de referers / user agents
- Délivre l'article complet pour indexation
- Trackable

### 8.11. EP-10 — Newsletter & campagnes

> *Bloc : **Backoffice** · 9 user stories*

Listes de diffusion, templates, campagnes, A/B testing, statistiques, double opt-in, désabonnements.

#### US-078 — Gérer les listes de diffusion

| Attribut | Valeur |
|---|---|
| **Acteur** | COM |
| **Priorité** | `P1` |
| **Phase** | `V1` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant que gestionnaire commercial, je veux créer plusieurs listes afin de cibler des communications distinctes.

**Critères d'acceptation :**

- Listes : Hebdo grand public, Premium abonnés, Professionnels B2B, Événements
- Auto-ajout selon l'abonnement
- Double opt-in obligatoire

#### US-079 — Concevoir une campagne email

| Attribut | Valeur |
|---|---|
| **Acteur** | COM |
| **Priorité** | `P1` |
| **Phase** | `V1` |
| **Estimation** | `XL` |
| **Type** | Fonctionnelle |

**User story :** En tant que gestionnaire commercial, je veux concevoir une campagne afin d'envoyer une newsletter.

**Critères d'acceptation :**

- Éditeur visuel (drag & drop) ou code HTML
- Personnalisation (nom, formule)
- Variables dynamiques (articles récents)
- Prévisualisation desktop/mobile

#### US-080 — Gérer les templates

| Attribut | Valeur |
|---|---|
| **Acteur** | EDIT |
| **Priorité** | `P2` |
| **Phase** | `V1` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant qu'administrateur éditorial, je veux gérer des templates réutilisables afin d'assurer une cohérence graphique.

**Critères d'acceptation :**

- Templates par type : newsletter hebdo, relance abonnement, confirmation, alerte dossier
- Variables dynamiques
- Versioning des templates

#### US-081 — Envoyer une campagne immédiate

| Attribut | Valeur |
|---|---|
| **Acteur** | COM |
| **Priorité** | `P1` |
| **Phase** | `V1` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant que gestionnaire commercial, je veux envoyer une campagne à une liste afin de diffuser une information.

**Critères d'acceptation :**

- Sélection liste / segment
- Test d'envoi préalable obligatoire
- Confirmation avant envoi masse
- Rate limiting

#### US-082 — Planifier une campagne

| Attribut | Valeur |
|---|---|
| **Acteur** | COM |
| **Priorité** | `P1` |
| **Phase** | `V1` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant que gestionnaire commercial, je veux planifier une campagne à une date précise afin d'optimiser les horaires d'envoi.

**Critères d'acceptation :**

- Date/heure précises, fuseau horaire
- Mode récurrent (hebdomadaire)
- Annulation possible avant envoi

#### US-083 — Test A/B

| Attribut | Valeur |
|---|---|
| **Acteur** | COM |
| **Priorité** | `P3` |
| **Phase** | `V2` |
| **Estimation** | `L` |
| **Type** | Fonctionnelle |

**User story :** En tant que gestionnaire commercial, je veux tester deux variantes afin d'optimiser la performance des campagnes.

**Critères d'acceptation :**

- Variantes sur objet, contenu, CTA
- Échantillon paramétrable (ex: 20%)
- Winner automatique sur métrique (ouverture, clic) envoyé au reste

#### US-084 — Statistiques de campagne

| Attribut | Valeur |
|---|---|
| **Acteur** | COM |
| **Priorité** | `P1` |
| **Phase** | `V1` |
| **Estimation** | `L` |
| **Type** | Fonctionnelle |

**User story :** En tant que gestionnaire commercial, je veux consulter les statistiques détaillées afin de piloter la performance.

**Critères d'acceptation :**

- KPIs : taux d'ouverture, clic, désabonnement, bounce, conversion
- Par lien (heatmap)
- Comparaison campagnes
- Export CSV

#### US-085 — Gérer les désabonnements

| Attribut | Valeur |
|---|---|
| **Acteur** | VIS/AB |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `S` |
| **Type** | Fonctionnelle |
| **Dépendances** | RGPD |

**User story :** En tant qu'abonné newsletter, je veux me désabonner en un clic afin de respecter mon droit de retrait.

**Critères d'acceptation :**

- Lien unique sécurisé dans chaque email
- Désabonnement simple sans reconnexion
- Choix granulaire par liste
- Confirmation visuelle

#### US-086 — Double opt-in

| Attribut | Valeur |
|---|---|
| **Acteur** | ADM |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |
| **Dépendances** | RGPD |

**User story :** En tant qu'administrateur système, je veux que toute inscription soit confirmée par email afin de respecter les obligations RGPD et de qualité de base.

**Critères d'acceptation :**

- Email de confirmation avec lien de validation
- Compte inactif jusqu'à validation
- Expiration du lien sous 72h
- Log du consentement

### 8.12. EP-11 — Commandes & facturation

> *Bloc : **Backoffice** · 8 user stories*

Suivi commandes, factures PDF, relances, remboursements, rapports de revenus, paramétrage moyens de paiement.

#### US-087 — Consulter les commandes

| Attribut | Valeur |
|---|---|
| **Acteur** | COM |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant que gestionnaire commercial, je veux voir la liste des commandes afin de suivre l'activité.

**Critères d'acceptation :**

- Filtres : statut, période, montant, type (abonnement, numéro, article)
- Tri, pagination
- Export CSV

#### US-088 — Fiche détaillée d'une commande

| Attribut | Valeur |
|---|---|
| **Acteur** | COM |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant que gestionnaire commercial, je veux consulter le détail d'une commande afin de gérer les incidents.

**Critères d'acceptation :**

- Client, date, montant TTC/HT, moyen de paiement
- Statut : en attente, payée, échec, remboursée
- Timeline des événements (tentatives, webhooks, ...)
- Actions : relance, remboursement

#### US-089 — Générer une facture PDF

| Attribut | Valeur |
|---|---|
| **Acteur** | Système |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `L` |
| **Type** | Fonctionnelle |
| **Dépendances** | Légal |

**User story :** En tant que système, je dois générer une facture PDF conforme afin de respecter les obligations comptables.

**Critères d'acceptation :**

- Numérotation séquentielle continue (obligation légale CI)
- Mentions obligatoires : TVA, RCCM, NCC, CNPS, coordonnées
- PDF archivé de manière inaltérable
- Envoi auto par email après paiement

#### US-090 — Relancer un paiement échoué

| Attribut | Valeur |
|---|---|
| **Acteur** | COM |
| **Priorité** | `P1` |
| **Phase** | `V1` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant que gestionnaire commercial, je veux relancer un client en cas d'échec de paiement afin de sécuriser la vente.

**Critères d'acceptation :**

- Email de relance à J+1, J+3, J+7
- SMS à J+3 si numéro disponible
- Lien direct vers la page de paiement
- Désactivation de l'accès à J+10 si non régularisé

#### US-091 — Rembourser une commande

| Attribut | Valeur |
|---|---|
| **Acteur** | COM |
| **Priorité** | `P1` |
| **Phase** | `V1` |
| **Estimation** | `L` |
| **Type** | Fonctionnelle |

**User story :** En tant que gestionnaire commercial, je veux rembourser une commande afin de traiter une demande client.

**Critères d'acceptation :**

- Remboursement total ou partiel
- Motif obligatoire
- Avoir PDF généré automatiquement
- Mise à jour du compte client (abonnement ou accès)

#### US-092 — Rapport des revenus

| Attribut | Valeur |
|---|---|
| **Acteur** | SUP/COM |
| **Priorité** | `P1` |
| **Phase** | `V1` |
| **Estimation** | `L` |
| **Type** | Fonctionnelle |

**User story :** En tant que direction, je veux un rapport des revenus par période afin de piloter le business.

**Critères d'acceptation :**

- Ventes par type : abonnements, numéros, articles à l'unité
- Comparaison période / année précédente
- MRR, ARR, churn, LTV
- Export CSV et PDF

#### US-093 — Moyens de paiement configurés

| Attribut | Valeur |
|---|---|
| **Acteur** | ADM |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |
| **Dépendances** | EP-19 |

**User story :** En tant qu'administrateur système, je veux activer / désactiver les moyens de paiement afin d'adapter l'offre.

**Critères d'acceptation :**

- Mobile Money (Orange, MTN, Moov, Wave)
- Carte bancaire (Visa, Mastercard)
- Virement bancaire (facture)
- Bascule actif/inactif par moyen

#### US-094 — Taxes et fiscalité

| Attribut | Valeur |
|---|---|
| **Acteur** | ADM |
| **Priorité** | `P1` |
| **Phase** | `V1` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |
| **Dépendances** | Légal |

**User story :** En tant qu'administrateur système, je veux configurer les taxes applicables afin de respecter la fiscalité ivoirienne.

**Critères d'acceptation :**

- TVA 18% par défaut
- Exonérations paramétrables (export, certaines catégories)
- Affichage TTC/HT selon contexte
- Retenues à la source si applicable

### 8.13. EP-12 — Modération & interactions

> *Bloc : **Backoffice** · 5 user stories*

Modération des commentaires, filtrage automatique, bannissements, gestion des signalements.

#### US-095 — Déposer un commentaire (abonné)

| Attribut | Valeur |
|---|---|
| **Acteur** | AB-* |
| **Priorité** | `P2` |
| **Phase** | `V1` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant qu'abonné, je veux commenter un article afin de participer au débat.

**Critères d'acceptation :**

- Authentification obligatoire
- Longueur max 1500 caractères
- Règles de modération visibles
- File d'attente modération par défaut

#### US-096 — File de modération

| Attribut | Valeur |
|---|---|
| **Acteur** | CHEF |
| **Priorité** | `P2` |
| **Phase** | `V1` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant que modérateur / rédacteur en chef, je veux une file des commentaires en attente afin de les traiter rapidement.

**Critères d'acceptation :**

- Vue liste avec contexte (article)
- Actions : approuver, refuser, marquer comme spam
- Réponse de la rédaction possible
- SLA interne affiché

#### US-097 — Modération automatique

| Attribut | Valeur |
|---|---|
| **Acteur** | EDIT |
| **Priorité** | `P3` |
| **Phase** | `V2` |
| **Estimation** | `L` |
| **Type** | Fonctionnelle |

**User story :** En tant qu'administrateur éditorial, je veux configurer une modération automatique afin d'accélérer le traitement.

**Critères d'acceptation :**

- Liste noire de mots / expressions
- Détection d'URLs suspectes
- Flag automatique selon score
- Auto-approbation pour commentateurs de confiance (après N commentaires validés)

#### US-098 — Signaler un commentaire

| Attribut | Valeur |
|---|---|
| **Acteur** | AB-* |
| **Priorité** | `P3` |
| **Phase** | `V2` |
| **Estimation** | `S` |
| **Type** | Fonctionnelle |

**User story :** En tant qu'abonné, je veux signaler un commentaire inapproprié afin d'alerter la modération.

**Critères d'acceptation :**

- Bouton "Signaler" avec motif
- Limite d'abus (1 signalement/commentaire/utilisateur)
- Notification au modérateur

#### US-099 — Bannir un utilisateur

| Attribut | Valeur |
|---|---|
| **Acteur** | EDIT |
| **Priorité** | `P3` |
| **Phase** | `V2` |
| **Estimation** | `S` |
| **Type** | Fonctionnelle |

**User story :** En tant qu'administrateur éditorial, je veux bannir un utilisateur des commentaires afin de préserver la qualité du débat.

**Critères d'acceptation :**

- Bannissement temporaire (durée) ou définitif
- Motif obligatoire
- Notification à l'utilisateur
- Contestable via support

### 8.14. EP-13 — Dashboards & analytics

> *Bloc : **Backoffice** · 7 user stories*

Tableaux de bord personnalisés rédacteur / chef / admin, analytics d'articles et d'abonnés, exports.

#### US-100 — Dashboard rédacteur

| Attribut | Valeur |
|---|---|
| **Acteur** | RED |
| **Priorité** | `P1` |
| **Phase** | `V1` |
| **Estimation** | `L` |
| **Type** | Fonctionnelle |

**User story :** En tant que rédacteur, je veux un tableau de bord personnel afin de suivre mes articles et performances.

**Critères d'acceptation :**

- Articles : en cours, publiés ce mois, total
- Performances : top 5 articles (vues), taux d'engagement
- Commentaires en attente sur mes articles

#### US-101 — Dashboard chef de rédaction

| Attribut | Valeur |
|---|---|
| **Acteur** | CHEF |
| **Priorité** | `P1` |
| **Phase** | `V1` |
| **Estimation** | `L` |
| **Type** | Fonctionnelle |

**User story :** En tant que rédacteur en chef, je veux une vue d'ensemble afin de piloter la rédaction.

**Critères d'acceptation :**

- Pipeline : nombre par statut
- Calendrier éditorial des planifiés
- Top articles de la semaine
- Charge par rédacteur

#### US-102 — Analytics d'article

| Attribut | Valeur |
|---|---|
| **Acteur** | RED |
| **Priorité** | `P1` |
| **Phase** | `V1` |
| **Estimation** | `L` |
| **Type** | Fonctionnelle |

**User story :** En tant que rédacteur, je veux consulter les performances d'un article afin d'évaluer son impact.

**Critères d'acceptation :**

- Vues, visiteurs uniques, temps de lecture moyen, taux de scroll
- Sources de trafic
- Partages sociaux
- Commentaires

#### US-103 — Top auteurs / articles

| Attribut | Valeur |
|---|---|
| **Acteur** | EDIT |
| **Priorité** | `P1` |
| **Phase** | `V1` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant qu'administrateur éditorial, je veux voir les classements afin d'identifier les contenus et signatures performants.

**Critères d'acceptation :**

- Top articles par période (7j, 30j, 90j, 1 an)
- Top auteurs
- Top rubriques
- Top tags

#### US-104 — Analytics abonnés

| Attribut | Valeur |
|---|---|
| **Acteur** | SUP/COM |
| **Priorité** | `P1` |
| **Phase** | `V1` |
| **Estimation** | `XL` |
| **Type** | Fonctionnelle |

**User story :** En tant que direction, je veux des métriques sur les abonnés afin de piloter la croissance.

**Critères d'acceptation :**

- Acquisition : nouveaux abonnés par canal, par formule
- Rétention : churn rate, cohortes
- Revenus : MRR, ARR, LTV, CAC
- Graphiques évolutifs

#### US-105 — Tableau de bord exécutif (NS)

| Attribut | Valeur |
|---|---|
| **Acteur** | SUP |
| **Priorité** | `P2` |
| **Phase** | `V2` |
| **Estimation** | `L` |
| **Type** | Fonctionnelle |

**User story :** En tant que direction, je veux un tableau de bord synthétique agrégeant toutes les métriques clés afin d'avoir une vision 360°.

**Critères d'acceptation :**

- Vues synthétiques : audience, acquisition, revenus, éditorial
- Alertes automatiques (seuils anormaux)
- Envoi périodique par email

#### US-106 — Exports analytics

| Attribut | Valeur |
|---|---|
| **Acteur** | Tous BO |
| **Priorité** | `P2` |
| **Phase** | `V1` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant qu'utilisateur avec droit analytics, je veux exporter les données afin de les exploiter hors plateforme.

**Critères d'acceptation :**

- Export CSV et Excel
- Plages horaires et filtres applicables
- Planification d'export récurrent

### 8.15. EP-14 — Audit & journalisation

> *Bloc : **Backoffice** · 3 user stories*

Journal horodaté de toutes les actions sensibles, export, recherche, conservation réglementaire.

#### US-107 — Journal d'audit centralisé

| Attribut | Valeur |
|---|---|
| **Acteur** | ADM |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `L` |
| **Type** | Fonctionnelle |
| **Dépendances** | Légal |

**User story :** En tant qu'administrateur système, je veux consulter un journal horodaté de toutes les actions sensibles afin de garantir la traçabilité.

**Critères d'acceptation :**

- Événements tracés : login, changement de rôle, modification/suppression contenu, gestion abonnés, paiements
- Données : acteur, action, objet, IP, date
- Conservation 24 mois
- Non-modifiable

#### US-108 — Recherche dans les logs

| Attribut | Valeur |
|---|---|
| **Acteur** | ADM |
| **Priorité** | `P1` |
| **Phase** | `V1` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant qu'administrateur système, je veux rechercher dans le journal d'audit afin d'enquêter rapidement.

**Critères d'acceptation :**

- Filtres : utilisateur, action, objet, période
- Plein texte
- Pagination performante sur gros volumes

#### US-109 — Export des logs

| Attribut | Valeur |
|---|---|
| **Acteur** | ADM |
| **Priorité** | `P2` |
| **Phase** | `V1` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant qu'administrateur système, je veux exporter les logs afin de les transmettre à un auditeur externe.

**Critères d'acceptation :**

- Export CSV / JSON
- Sélection par période
- Signature numérique du fichier
- Traçabilité de l'export lui-même

### 8.16. EP-15 — Paramètres de la plateforme

> *Bloc : **Backoffice** · 6 user stories*

Paramètres globaux, pages légales, bannières, SMTP, SEO global, branding.

#### US-110 — Paramètres généraux

| Attribut | Valeur |
|---|---|
| **Acteur** | ADM |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant qu'administrateur système, je veux configurer les paramètres généraux afin de personnaliser la plateforme.

**Critères d'acceptation :**

- Nom, baseline, logo (clair + foncé), favicon
- URLs publiques, CGU, CGV, mentions
- Coordonnées légales
- Fuseau horaire par défaut

#### US-111 — Intégration réseaux sociaux

| Attribut | Valeur |
|---|---|
| **Acteur** | ADM |
| **Priorité** | `P2` |
| **Phase** | `V1` |
| **Estimation** | `L` |
| **Type** | Fonctionnelle |

**User story :** En tant qu'administrateur système, je veux lier les comptes sociaux afin de publier automatiquement.

**Critères d'acceptation :**

- Configuration : Facebook, LinkedIn, X, Instagram, YouTube, WhatsApp
- Tokens OAuth
- Publication automatique à la publication d'un article (opt-in par compte)

#### US-112 — SEO global

| Attribut | Valeur |
|---|---|
| **Acteur** | ADM |
| **Priorité** | `P1` |
| **Phase** | `MVP` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |
| **Dépendances** | EP-21 |

**User story :** En tant qu'administrateur système, je veux configurer les paramètres SEO globaux afin de poser les bases d'un bon référencement.

**Critères d'acceptation :**

- Titre par défaut, meta description par défaut
- Verified tags (Google, Bing)
- Code Google Analytics / Matomo
- Sitemap config

#### US-113 — Configuration SMTP / ESP

| Attribut | Valeur |
|---|---|
| **Acteur** | ADM |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant qu'administrateur système, je veux configurer le serveur d'envoi d'emails afin d'assurer la délivrabilité.

**Critères d'acceptation :**

- Configuration d'un ESP transactionnel (SendGrid, Mailjet, SES)
- DKIM, SPF, DMARC
- Email d'envoi par défaut et de contact
- Test d'envoi

#### US-114 — Pages légales

| Attribut | Valeur |
|---|---|
| **Acteur** | EDIT |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |
| **Dépendances** | Légal |

**User story :** En tant qu'administrateur éditorial, je veux éditer les pages légales afin de maintenir la conformité.

**Critères d'acceptation :**

- Pages : Mentions légales, CGU, CGV, Politique de confidentialité, Politique cookies
- Éditeur riche
- Versioning et date de dernière mise à jour

#### US-115 — Gestion de bannières / annonces

| Attribut | Valeur |
|---|---|
| **Acteur** | EDIT |
| **Priorité** | `P2` |
| **Phase** | `V1` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant qu'administrateur éditorial, je veux gérer des bandeaux et annonces afin de communiquer ponctuellement (nouveau numéro, événement, urgence).

**Critères d'acceptation :**

- Bandeau haut de page avec texte, CTA, couleur, dates
- Ciblage par rubrique ou global
- Fermeture mémorisée par utilisateur

### 8.17. EP-16 — Page d'accueil (La Une) & rubriques

> *Bloc : **Frontend** · 7 user stories*

Slider, fil d'actu, choix de la rédaction, CTA newsletter, navigation par rubriques avec filtres et pagination.

#### US-116 — Afficher le slider principal

| Attribut | Valeur |
|---|---|
| **Acteur** | VIS |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `L` |
| **Type** | Fonctionnelle |

**User story :** En tant que visiteur, je veux voir un slider mettant en avant les 3 derniers dossiers afin de découvrir les contenus phares.

**Critères d'acceptation :**

- 3 à 5 slides configurables par la rédaction
- Défilement auto (7s) + navigation manuelle
- Responsive
- Accessible clavier

#### US-117 — Afficher le fil d'actualités

| Attribut | Valeur |
|---|---|
| **Acteur** | VIS |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant que visiteur, je veux consulter le fil des articles récents afin de m'informer rapidement.

**Critères d'acceptation :**

- 6 à 9 derniers articles publiés (hors ceux du slider)
- Tri chronologique
- Pagination "charger plus" ou scroll

#### US-118 — Bloc "Choix de la Rédaction"

| Attribut | Valeur |
|---|---|
| **Acteur** | VIS |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant que visiteur, je veux voir un contenu mis en avant par la rédaction afin de découvrir une analyse de fond.

**Critères d'acceptation :**

- Article unique sélectionné par le chef de rédaction
- Visuel grand format + citation ou kicker
- Mise à jour manuelle depuis le backoffice

#### US-119 — CTA Newsletter

| Attribut | Valeur |
|---|---|
| **Acteur** | VIS |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `S` |
| **Type** | Fonctionnelle |
| **Dépendances** | EP-10 |

**User story :** En tant que visiteur, je veux voir un bloc d'inscription à la newsletter afin de rester informé.

**Critères d'acceptation :**

- Bloc visible en bas de la Une
- Formulaire simplifié (email uniquement)
- Confirmation RGPD
- Double opt-in déclenché

#### US-120 — Page de rubrique avec filtres

| Attribut | Valeur |
|---|---|
| **Acteur** | VIS |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `L` |
| **Type** | Fonctionnelle |

**User story :** En tant que visiteur, je veux filtrer les articles d'une rubrique afin de trouver ce qui m'intéresse.

**Critères d'acceptation :**

- Filtres : sous-rubrique, catégorie, tag, période
- Tri : récent, populaire
- Pagination ou scroll infini
- URL reflétant les filtres (SEO)

#### US-121 — Dernier numéro en page d'accueil

| Attribut | Valeur |
|---|---|
| **Acteur** | VIS |
| **Priorité** | `P1` |
| **Phase** | `V1` |
| **Estimation** | `S` |
| **Type** | Fonctionnelle |

**User story :** En tant que visiteur, je veux voir le dernier numéro du magazine afin de le commander.

**Critères d'acceptation :**

- Couverture, titre, CTA achat
- Position paramétrable
- Lien vers la boutique

#### US-122 — Live / événement en cours

| Attribut | Valeur |
|---|---|
| **Acteur** | VIS |
| **Priorité** | `P3` |
| **Phase** | `V2` |
| **Estimation** | `S` |
| **Type** | Fonctionnelle |

**User story :** En tant que visiteur, je veux être alerté d'un événement en direct (couverture d'un salon, live) afin de le suivre.

**Critères d'acceptation :**

- Bandeau live configurable (titre, lien)
- Alerte visuelle (point animé rouge)
- Traçabilité

### 8.18. EP-17 — Consultation d'articles

> *Bloc : **Frontend** · 7 user stories*

Lecteur article avec sommaire auto, bios auteurs, suggestions, temps de lecture, galeries, partage.

#### US-123 — Page de lecture d'article

| Attribut | Valeur |
|---|---|
| **Acteur** | VIS/AB |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `XL` |
| **Type** | Fonctionnelle |

**User story :** En tant que visiteur/abonné, je veux lire un article dans une mise en page soignée afin de profiter pleinement du contenu.

**Critères d'acceptation :**

- Typo éditoriale, largeur de lecture optimale (max 720px)
- Image à la Une grande, chapô distinct
- Auteur(s) avec bio compacte
- Date de publication et temps de lecture
- Paywall selon niveau d'accès

#### US-124 — Sommaire automatique

| Attribut | Valeur |
|---|---|
| **Acteur** | VIS/AB |
| **Priorité** | `P1` |
| **Phase** | `V1` |
| **Estimation** | `L` |
| **Type** | Fonctionnelle |

**User story :** En tant que lecteur, je veux un sommaire cliquable pour les articles longs afin de naviguer plus facilement.

**Critères d'acceptation :**

- Généré à partir des H2/H3
- Sticky sur desktop, accordéon sur mobile
- Scroll smooth vers la section
- Mise à jour scroll active (hash visible)

#### US-125 — Bios des auteurs

| Attribut | Valeur |
|---|---|
| **Acteur** | VIS/AB |
| **Priorité** | `P1` |
| **Phase** | `V1` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant que lecteur, je veux voir la bio des auteurs afin de contextualiser leur expertise.

**Critères d'acceptation :**

- Photo, nom, fonction, bio 2-3 lignes
- Lien vers la page auteur
- Articles récents de l'auteur (max 3)

#### US-126 — Suggestions d'articles similaires

| Attribut | Valeur |
|---|---|
| **Acteur** | VIS/AB |
| **Priorité** | `P1` |
| **Phase** | `V1` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant que lecteur, je veux des suggestions pertinentes afin de prolonger ma lecture.

**Critères d'acceptation :**

- Algorithme : même rubrique + tags + catégorie
- 3 à 6 suggestions en bas d'article
- Exclusion de l'article courant
- Priorité aux articles de la même semaine

#### US-127 — Temps de lecture estimé

| Attribut | Valeur |
|---|---|
| **Acteur** | VIS/AB |
| **Priorité** | `P2` |
| **Phase** | `V1` |
| **Estimation** | `XS` |
| **Type** | Fonctionnelle |

**User story :** En tant que lecteur, je veux connaître le temps de lecture afin d'organiser ma consultation.

**Critères d'acceptation :**

- Calcul automatique (200 mots/minute)
- Affiché en tête d'article et dans les cartes
- Précision ajustée pour articles multimédias

#### US-128 — Galeries et médias enrichis

| Attribut | Valeur |
|---|---|
| **Acteur** | VIS/AB |
| **Priorité** | `P1` |
| **Phase** | `V1` |
| **Estimation** | `L` |
| **Type** | Fonctionnelle |

**User story :** En tant que lecteur, je veux profiter de galeries photos et de contenus riches afin de mieux comprendre l'article.

**Critères d'acceptation :**

- Galeries d'images avec lightbox
- Infographies zoomables
- Vidéos embarquées (YouTube, Vimeo)
- Citations et encadrés stylisés

#### US-129 — Impression et version lecture apaisée

| Attribut | Valeur |
|---|---|
| **Acteur** | AB-* |
| **Priorité** | `P3` |
| **Phase** | `V2` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant qu'abonné, je veux imprimer ou enregistrer en PDF un article afin de le consulter hors ligne.

**Critères d'acceptation :**

- Mode impression optimisée (sans nav, en 1 colonne)
- Export PDF propre
- Mode sombre optionnel

### 8.19. EP-18 — Inscription, connexion & compte abonné

> *Bloc : **Frontend** · 12 user stories*

Inscription email / social, connexion, 2FA, espace abonné complet, gestion préférences et données.

#### US-130 — Inscription email / mot de passe

| Attribut | Valeur |
|---|---|
| **Acteur** | VIS |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `L` |
| **Type** | Fonctionnelle |
| **Dépendances** | RGPD |

**User story :** En tant que prospect, je veux créer un compte afin d'accéder aux fonctionnalités réservées.

**Critères d'acceptation :**

- Formulaire : nom, prénom, email, mot de passe, téléphone (optionnel)
- Validation du mot de passe (complexité)
- Acceptation CGU / politique
- Email de confirmation (double opt-in)

#### US-131 — Inscription via réseaux sociaux

| Attribut | Valeur |
|---|---|
| **Acteur** | VIS |
| **Priorité** | `P2` |
| **Phase** | `V1` |
| **Estimation** | `L` |
| **Type** | Fonctionnelle |

**User story :** En tant que prospect, je veux m'inscrire avec Google ou Facebook afin de gagner du temps.

**Critères d'acceptation :**

- Boutons OAuth Google, Facebook
- Création automatique du compte
- Possibilité de lier des comptes existants
- Respect des consentements RGPD

#### US-132 — Création de compte après souscription

| Attribut | Valeur |
|---|---|
| **Acteur** | VIS→AB |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `L` |
| **Type** | Fonctionnelle |
| **Dépendances** | EP-19 |

**User story :** En tant que nouvel abonné ayant payé, je veux que mon compte soit automatiquement créé afin d'accéder immédiatement au contenu.

**Critères d'acceptation :**

- Compte pré-créé avec l'email du paiement
- Email de bienvenue avec lien de définition du mot de passe
- Abonnement déjà actif au premier login
- Statut "Activation en attente" si mdp non défini

#### US-133 — Connexion

| Attribut | Valeur |
|---|---|
| **Acteur** | AB-* |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant qu'utilisateur, je veux me connecter afin d'accéder à mon espace.

**Critères d'acceptation :**

- Login email + mot de passe
- Option "Rester connecté" (30j)
- Protection anti-bruteforce (blocage après 5 échecs, délai 15 min)
- Redirection vers la page initialement demandée

#### US-134 — Mot de passe oublié

| Attribut | Valeur |
|---|---|
| **Acteur** | AB-* |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant qu'utilisateur, je veux réinitialiser mon mot de passe afin de retrouver l'accès à mon compte.

**Critères d'acceptation :**

- Lien de réinitialisation envoyé par email
- Lien valide 1h, usage unique
- Pas de confirmation si l'email n'existe pas (énumération)
- Invalidation de l'ancien mdp après changement

#### US-135 — 2FA pour abonnés (optionnel)

| Attribut | Valeur |
|---|---|
| **Acteur** | AB-* |
| **Priorité** | `P2` |
| **Phase** | `V1` |
| **Estimation** | `L` |
| **Type** | Fonctionnelle |
| **Dépendances** | Sécurité |

**User story :** En tant qu'abonné, je veux activer une double authentification afin de mieux protéger mon compte.

**Critères d'acceptation :**

- Options : TOTP ou SMS
- Codes de secours
- Désactivation avec confirmation
- Obligatoire pour les comptes Entreprise admins

#### US-136 — Tableau de bord du compte

| Attribut | Valeur |
|---|---|
| **Acteur** | AB-* |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `L` |
| **Type** | Fonctionnelle |

**User story :** En tant qu'abonné, je veux un espace personnel afin de gérer mon abonnement et mes données.

**Critères d'acceptation :**

- Synthèse : formule active, prochaine échéance, dernières factures
- Accès rapide : favoris, historique, téléchargements
- Alertes (renouvellement, offres)

#### US-137 — Gérer mes coordonnées

| Attribut | Valeur |
|---|---|
| **Acteur** | AB-* |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant qu'abonné, je veux mettre à jour mes coordonnées afin qu'elles restent exactes.

**Critères d'acceptation :**

- Édition : nom, email (vérification), téléphone, adresse livraison
- Historique des modifications
- Vérification à chaque changement critique

#### US-138 — Gérer mes préférences newsletter

| Attribut | Valeur |
|---|---|
| **Acteur** | AB-* |
| **Priorité** | `P1` |
| **Phase** | `V1` |
| **Estimation** | `S` |
| **Type** | Fonctionnelle |
| **Dépendances** | EP-10 |

**User story :** En tant qu'abonné, je veux choisir mes préférences de réception afin de n'avoir que le contenu pertinent.

**Critères d'acceptation :**

- Sélection par thème / liste
- Fréquence (hebdo, mensuel)
- Désabonnement total en un clic

#### US-139 — Historique d'activité

| Attribut | Valeur |
|---|---|
| **Acteur** | AB-* |
| **Priorité** | `P2` |
| **Phase** | `V1` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant qu'abonné, je veux voir mon historique afin de retrouver mes lectures et achats.

**Critères d'acceptation :**

- Articles lus (30 derniers)
- Numéros achetés / téléchargés
- Factures et reçus
- Commentaires postés

#### US-140 — Exporter mes données (RGPD)

| Attribut | Valeur |
|---|---|
| **Acteur** | AB-* |
| **Priorité** | `P1` |
| **Phase** | `V1` |
| **Estimation** | `L` |
| **Type** | Fonctionnelle |
| **Dépendances** | RGPD |

**User story :** En tant qu'abonné, je veux exporter l'ensemble de mes données afin d'exercer mon droit à la portabilité.

**Critères d'acceptation :**

- Export complet en JSON + PDF synthétique
- Envoi par email sécurisé
- Traitement sous 72h max
- Gratuit

#### US-141 — Supprimer mon compte

| Attribut | Valeur |
|---|---|
| **Acteur** | AB-* |
| **Priorité** | `P1` |
| **Phase** | `V1` |
| **Estimation** | `L` |
| **Type** | Fonctionnelle |
| **Dépendances** | RGPD |

**User story :** En tant qu'abonné, je veux supprimer mon compte afin d'exercer mon droit à l'oubli.

**Critères d'acceptation :**

- Formulaire avec confirmation renforcée
- Annulation de l'abonnement en cours (sans remboursement pro rata sauf motif)
- Anonymisation des données + effacement après délai légal
- Conservation minimale pour obligations comptables (10 ans factures)

### 8.20. EP-19 — Tunnel d'abonnement & paiement

> *Bloc : **Frontend** · 8 user stories*

Page comparatif, checkout, paiement Mobile Money/carte, confirmation, renouvellement automatique.

#### US-142 — Page de comparaison des formules

| Attribut | Valeur |
|---|---|
| **Acteur** | VIS |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `L` |
| **Type** | Fonctionnelle |

**User story :** En tant que visiteur, je veux comparer les formules afin de choisir celle qui me convient.

**Critères d'acceptation :**

- Tableau comparatif clair
- Mise en avant visuelle de la formule recommandée
- CTA par formule
- FAQ intégrée

#### US-143 — Choix d'une formule

| Attribut | Valeur |
|---|---|
| **Acteur** | VIS |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant que prospect, je veux choisir une formule afin de démarrer mon abonnement.

**Critères d'acceptation :**

- Sélection formule + durée (mensuel / annuel)
- Récap dynamique (prix, TVA, total)
- Application code promo
- Passage à l'étape paiement

#### US-144 — Checkout — saisie des informations

| Attribut | Valeur |
|---|---|
| **Acteur** | VIS→AB |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `L` |
| **Type** | Fonctionnelle |

**User story :** En tant que prospect, je veux saisir mes informations afin de finaliser ma commande.

**Critères d'acceptation :**

- Email, nom, prénom, téléphone, adresse (pour Combo)
- Création automatique de compte ou login si existant
- Validation mail + téléphone
- Étapes visuelles (1 seule page préférée)

#### US-145 — Paiement Mobile Money

| Attribut | Valeur |
|---|---|
| **Acteur** | VIS |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `XL` |
| **Type** | Fonctionnelle |
| **Dépendances** | EP-23 Intégration |

**User story :** En tant que prospect, je veux payer par Mobile Money (Orange Money, MTN, Moov, Wave) afin d'utiliser le moyen de paiement le plus répandu en Côte d'Ivoire.

**Critères d'acceptation :**

- Intégration via CinetPay / PayDunya / Wave API
- Saisie du numéro, envoi push de validation
- Retour de confirmation synchrone ou polling
- Gestion des timeouts (pending, échec)

#### US-146 — Paiement carte bancaire

| Attribut | Valeur |
|---|---|
| **Acteur** | VIS |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `XL` |
| **Type** | Fonctionnelle |
| **Dépendances** | EP-23 Intégration |

**User story :** En tant que prospect, je veux payer par carte afin d'utiliser mon moyen de paiement habituel.

**Critères d'acceptation :**

- Intégration PCI DSS via passerelle (CinetPay, Flutterwave, Stripe)
- 3D Secure
- Enregistrement du moyen de paiement pour renouvellement (tokenization)
- Retour de statut et gestion des échecs

#### US-147 — Page de confirmation

| Attribut | Valeur |
|---|---|
| **Acteur** | VIS→AB |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant que nouvel abonné, je veux recevoir une confirmation afin d'être rassuré sur mon achat.

**Critères d'acceptation :**

- Page de succès avec récap et CTA d'accès au contenu
- Envoi email de confirmation + facture
- Envoi SMS de confirmation
- Journal de la commande

#### US-148 — Échec de paiement

| Attribut | Valeur |
|---|---|
| **Acteur** | VIS |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant que prospect, je veux savoir pourquoi mon paiement a échoué afin de pouvoir réessayer.

**Critères d'acceptation :**

- Message d'erreur explicite et actionnable
- Possibilité de réessayer avec un autre moyen
- Panier conservé (session)
- Tentative tracée

#### US-149 — Renouvellement automatique

| Attribut | Valeur |
|---|---|
| **Acteur** | AB-* |
| **Priorité** | `P1` |
| **Phase** | `V1` |
| **Estimation** | `L` |
| **Type** | Fonctionnelle |

**User story :** En tant qu'abonné, je veux un renouvellement automatique afin de ne pas interrompre mon accès.

**Critères d'acceptation :**

- Débit automatique à J-0 (fin de période)
- Notification J-15, J-7, J-1
- Échec → relance + période de grâce 7j
- Désactivation en 1 clic depuis l'espace abonné

### 8.21. EP-20 — Boutique magazine & lecture PDF

> *Bloc : **Frontend** · 6 user stories*

Dernier numéro, archives filtrables, commande papier/PDF, lecteur PDF protégé, watermark.

#### US-150 — Page dernier numéro

| Attribut | Valeur |
|---|---|
| **Acteur** | VIS |
| **Priorité** | `P1` |
| **Phase** | `MVP` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |

**User story :** En tant que visiteur, je veux consulter la page du dernier numéro afin de l'acheter.

**Critères d'acceptation :**

- Couverture grand format, sommaire, description
- CTA : papier, PDF, combo
- Prix TTC visibles
- Options livraison

#### US-151 — Archives consultables

| Attribut | Valeur |
|---|---|
| **Acteur** | VIS/AB |
| **Priorité** | `P1` |
| **Phase** | `V1` |
| **Estimation** | `L` |
| **Type** | Fonctionnelle |

**User story :** En tant que visiteur, je veux parcourir les archives afin de retrouver un ancien numéro.

**Critères d'acceptation :**

- Grille de couvertures par année
- Filtres : année, thème, format
- Gratuit pour abonnés, payant pour visiteurs
- Moteur de recherche intégré

#### US-152 — Acheter un numéro à l'unité

| Attribut | Valeur |
|---|---|
| **Acteur** | VIS |
| **Priorité** | `P1` |
| **Phase** | `V1` |
| **Estimation** | `L` |
| **Type** | Fonctionnelle |

**User story :** En tant que visiteur, je veux acheter un numéro papier ou PDF afin d'accéder à un contenu précis.

**Critères d'acceptation :**

- Choix format + quantité (papier)
- Adresse livraison
- Paiement via tunnel standard
- Livraison papier tracée

#### US-153 — Lecteur PDF en ligne

| Attribut | Valeur |
|---|---|
| **Acteur** | AB-D/C/E |
| **Priorité** | `P1` |
| **Phase** | `V1` |
| **Estimation** | `L` |
| **Type** | Fonctionnelle |

**User story :** En tant qu'abonné digital, je veux lire le PDF en ligne afin de consulter sans télécharger.

**Critères d'acceptation :**

- Viewer fluide (pdf.js ou équivalent)
- Navigation pages, recherche, zoom
- Mode plein écran
- Adapté mobile

#### US-154 — Téléchargement PDF protégé

| Attribut | Valeur |
|---|---|
| **Acteur** | AB-D/C/E |
| **Priorité** | `P1` |
| **Phase** | `V1` |
| **Estimation** | `L` |
| **Type** | Fonctionnelle |
| **Dépendances** | Sécurité |

**User story :** En tant qu'abonné, je veux télécharger le PDF afin de le lire hors ligne.

**Critères d'acceptation :**

- Watermark dynamique (email abonné + date + IP)
- Limitation d'usage (ex: 3 téléchargements/numéro)
- Traçabilité

#### US-155 — Livraison papier

| Attribut | Valeur |
|---|---|
| **Acteur** | AB-C |
| **Priorité** | `P2` |
| **Phase** | `V1` |
| **Estimation** | `L` |
| **Type** | Fonctionnelle |

**User story :** En tant qu'abonné Combo, je veux recevoir le magazine papier afin d'en profiter en format physique.

**Critères d'acceptation :**

- Gestion adresse de livraison
- Suivi logistique (partenaire postal)
- Notification de chaque envoi
- Interface pour mettre en pause la livraison (vacances)

### 8.22. EP-21 — SEO, performance & partage social

> *Bloc : **Transverse** · 6 user stories*

Sitemap, Open Graph, Schema.org, cache CDN, images optimisées, lazy-loading, SSR.

#### US-156 — Sitemap.xml dynamique

| Attribut | Valeur |
|---|---|
| **Acteur** | Système |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |
| **Dépendances** | NF |

**User story :** En tant que moteur de recherche, j'ai besoin d'un sitemap à jour afin d'indexer efficacement le site.

**Critères d'acceptation :**

- Génération automatique
- Découpage en sous-sitemaps (articles, rubriques, auteurs, numéros)
- lastmod à jour
- Soumission Google Search Console

#### US-157 — Meta Open Graph / Twitter Cards

| Attribut | Valeur |
|---|---|
| **Acteur** | Système |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |
| **Dépendances** | NF |

**User story :** En tant qu'utilisateur partageant un article, je veux que l'aperçu soit soigné afin d'inciter au clic.

**Critères d'acceptation :**

- og:title, og:description, og:image (ratio 1200x630), og:type
- twitter:card summary_large_image
- Configurable par article (override possible)

#### US-158 — Schema.org JSON-LD

| Attribut | Valeur |
|---|---|
| **Acteur** | Système |
| **Priorité** | `P1` |
| **Phase** | `MVP` |
| **Estimation** | `M` |
| **Type** | Fonctionnelle |
| **Dépendances** | NF |

**User story :** En tant que moteur de recherche, j'ai besoin de données structurées afin de mieux comprendre les articles.

**Critères d'acceptation :**

- Types : NewsArticle, Article, Person, Organization
- Inclus date publi, auteurs, image, publisher
- Validation via Rich Results Test

#### US-159 — Cache CDN

| Attribut | Valeur |
|---|---|
| **Acteur** | Système |
| **Priorité** | `P1` |
| **Phase** | `MVP` |
| **Estimation** | `L` |
| **Type** | Fonctionnelle |
| **Dépendances** | NF |

**User story :** En tant qu'utilisateur, je veux que les pages se chargent rapidement afin de profiter d'une bonne expérience.

**Critères d'acceptation :**

- Cache CDN des pages publiques (TTL adaptatif)
- Purge ciblée à la publication / modification d'un article
- Cache-busting sur les assets statiques

#### US-160 — Images responsives optimisées

| Attribut | Valeur |
|---|---|
| **Acteur** | Système |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `L` |
| **Type** | Fonctionnelle |
| **Dépendances** | NF |

**User story :** En tant qu'utilisateur mobile, je veux des images adaptées afin de ne pas consommer trop de data.

**Critères d'acceptation :**

- srcset + sizes conformes
- WebP / AVIF prioritaires, fallback JPEG
- Lazy-loading natif
- LCP < 2.5s

#### US-161 — Flux RSS

| Attribut | Valeur |
|---|---|
| **Acteur** | VIS |
| **Priorité** | `P2` |
| **Phase** | `V1` |
| **Estimation** | `S` |
| **Type** | Fonctionnelle |

**User story :** En tant que lecteur, je veux un flux RSS afin de suivre le magazine dans mon agrégateur.

**Critères d'acceptation :**

- Flux principal + par rubrique
- Description enrichie, enclosures
- Mise à jour en temps réel

### 8.23. EP-22 — Sécurité, conformité & accessibilité

> *Bloc : **Transverse** · 5 user stories*

OWASP Top 10, RGPD / Loi CI 2013-450, WCAG 2.1 AA, cookies, registre des traitements.

#### US-162 — Conformité RGPD / Loi 2013-450 CI

| Attribut | Valeur |
|---|---|
| **Acteur** | VIS/AB |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `XL` |
| **Type** | Fonctionnelle |
| **Dépendances** | Légal |

**User story :** En tant qu'utilisateur, je veux que mes données soient traitées conformément afin que mes droits soient respectés.

**Critères d'acceptation :**

- Registre des traitements tenu
- Bandeau cookies conforme (accept, refuser, paramétrer)
- Mentions légales + politique confidentialité
- DPO désigné et joignable

#### US-163 — Cookies et consentement

| Attribut | Valeur |
|---|---|
| **Acteur** | VIS/AB |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `L` |
| **Type** | Fonctionnelle |
| **Dépendances** | Légal |

**User story :** En tant qu'utilisateur, je veux choisir finement mes cookies afin de contrôler mes données.

**Critères d'acceptation :**

- Catégories : nécessaires, préférences, statistiques, marketing
- Granularité par catégorie
- Journal des consentements (timestamp, version)
- Révocation en 1 clic

#### US-164 — Accessibilité WCAG 2.1 AA

| Attribut | Valeur |
|---|---|
| **Acteur** | VIS/AB |
| **Priorité** | `P1` |
| **Phase** | `V1` |
| **Estimation** | `XL` |
| **Type** | Fonctionnelle |
| **Dépendances** | NF |

**User story :** En tant qu'utilisateur en situation de handicap, je veux que le site soit accessible afin de l'utiliser sans obstacle.

**Critères d'acceptation :**

- Contrastes conformes (4.5:1 minimum)
- Navigation clavier complète
- Compatibilité lecteurs d'écran (NVDA, VoiceOver)
- Audit externe à la V1 (attestation)

#### US-165 — Sécurité OWASP Top 10

| Attribut | Valeur |
|---|---|
| **Acteur** | Système |
| **Priorité** | `P0` |
| **Phase** | `MVP` |
| **Estimation** | `XL` |
| **Type** | Fonctionnelle |
| **Dépendances** | NF |

**User story :** En tant que système, je dois me protéger contre les menaces courantes afin de garantir l'intégrité des données.

**Critères d'acceptation :**

- Protection SQLi, XSS, CSRF, SSRF
- Rate limiting, WAF
- HTTPS/HSTS, CSP, en-têtes sécurisés
- Audit de sécurité avant mise en prod

#### US-166 — Pentest annuel

| Attribut | Valeur |
|---|---|
| **Acteur** | Système |
| **Priorité** | `P2` |
| **Phase** | `V1` |
| **Estimation** | `L` |
| **Type** | Fonctionnelle |
| **Dépendances** | NF |

**User story :** En tant qu'équipe sécurité, je veux un test d'intrusion annuel afin d'identifier les vulnérabilités.

**Critères d'acceptation :**

- Pentest externe par un tiers qualifié
- Scope : backoffice + frontend + APIs
- Rapport avec plan de remédiation
- Budget : 3 à 5 M FCFA

---

## 9. Roadmap et phasage

Le programme est découpé en **4 itérations successives sur 12 à 15 mois**, chacune livrant de la valeur mesurable et autonome.

### 9.1. Vue synthétique des phases

| Phase | Durée | Jalon | Nb US | Équipe | Budget indicatif |
|---|---|---|:-:|---|---|
| **MVP** | 3 mois (M1-M3) | Lancement commercial | 38 | 2 back + 2 front + 1 UX + 1 PO + 1 QA | 25 à 35 M FCFA |
| **V1** | 3 mois (M4-M6) | Plateforme complète | 48 | Même équipe + 1 data | 22 à 30 M FCFA |
| **V2** | 3 mois (M7-M9) | Engagement & scale | 30 | Équipe réduite (core + 2) | 15 à 20 M FCFA |
| **V3** | 3+ mois (M10+) | Évolutions | 17 | Équipe sur projets ciblés | 10 M FCFA + selon projets |

### 9.2. Phase MVP — Lancement commercial (M1 à M3)

**Objectif :** lancer une plateforme fonctionnelle capable de vendre des abonnements et de publier des articles, avec les standards de qualité minimum.

**Périmètre fonctionnel :**

- **Backoffice** : gestion des utilisateurs, création/publication d'articles avec éditeur riche, gestion des rubriques et tags, bibliothèque média de base, formules d'abonnement, commandes et factures, paramètres généraux, journal d'audit.
- **Frontend** : page d'accueil avec slider et fil d'actu, pages rubriques, lecture d'articles avec paywall basique, tunnel d'abonnement Mobile Money et carte, inscription après paiement, CTA newsletter.
- **Transverse** : SEO de base (sitemap, Open Graph), sécurité fondamentale (OWASP), conformité RGPD minimale (cookies, mentions légales), monitoring applicatif.

**Critère de succès :** le magazine peut vendre un premier abonnement, publier ses articles, et donner accès aux abonnés sans friction.

### 9.3. Phase V1 — Plateforme complète (M4 à M6)

**Objectif :** atteindre la maturité fonctionnelle et lancer la campagne marketing d'acquisition.

- **Backoffice** : workflow éditorial complet avec kanban, historique des versions, relecture collaborative, gestion du magazine papier/PDF, création et envoi de campagnes newsletter, renouvellements automatiques, dashboards personnalisés, licences Entreprise.
- **Frontend** : espace abonné complet (factures, préférences, gestion abonnement), lecteur PDF protégé avec watermark, archives consultables, recherche plein texte, mot de passe oublié, 2FA optionnelle, droits RGPD (export, suppression).
- **Transverse** : accessibilité WCAG 2.1 AA avec audit externe, Schema.org complet, optimisations de performance.

**Objectif business :** 2 000 abonnés payants.

### 9.4. Phase V2 — Engagement et scale (M7 à M9)

**Objectif :** renforcer l'engagement, enrichir les capacités analytiques et préparer la montée en charge.

- Commentaires et modération (file, modération automatique, signalements, bannissements).
- Favoris, articles liés, suggestions avancées, partage d'articles entre abonnés.
- A/B testing newsletter, segmentation abonnés avancée, offres spéciales temporaires.
- Tableau de bord exécutif synthétique, analytics article avancés.
- Gestion Entreprise avancée : reporting d'usage, gestion fine des licences.

**Objectif business :** 5 000 abonnés payants.

### 9.5. Phase V3 — Évolutions (M10+)

**Objectif :** ouvrir le champ des possibles et différencier la plateforme.

- PWA mobile ou application mobile native.
- API publique pour partenaires.
- Multi-langues (anglais en priorité).
- Événements / billetterie (GM Days).
- Intégrations IA (suggestions de sujets, résumés automatiques, relecture assistée).
- Gamification (badges, niveaux de lecture).

### 9.6. Jalons clés du programme

| Jalon | Événement | Livrables attendus |
|:-:|---|---|
| **M0** | Kick-off | Cadrage complet, choix techniques définitifs, constitution de l'équipe, mise en place CI/CD. |
| **M1** | Fin sprint 2 | Architecture validée, design system en place, premiers écrans backoffice. |
| **M2** | Beta interne | Backoffice opérationnel pour la rédaction, premiers articles publiés en environnement de test. |
| **M3** | **Lancement MVP public** | Mise en production, communication de lancement, premiers paiements réels. |
| **M4** | Retours terrain | Consolidation, priorisation ajustée selon les retours early adopters. |
| **M6** | **Release V1** | Plateforme complète, campagne marketing d'acquisition, objectif 2 000 abonnés. |
| **M9** | **Release V2** | Maturité fonctionnelle, analytics consolidés, objectif 5 000 abonnés. |
| **M12** | Bilan année 1 | Bilan business et technique, décisions stratégiques pour l'année 2. |

---

## 10. Exigences non-fonctionnelles

Les exigences non-fonctionnelles conditionnent la qualité de service globale de la plateforme. 34 exigences sont formalisées, réparties en 9 domaines.

| Domaine | Exigence | Cible mesurable | Priorité |
|---|---|---|:-:|
| **Performance** | Temps de chargement page accueil | LCP < 2.5s (p75 mobile 4G) | `P0` |
| **Performance** | Temps de réponse API | p95 < 500ms, p99 < 1s | `P0` |
| **Performance** | Core Web Vitals | LCP, FID, CLS dans le vert (Good) | `P0` |
| **Performance** | TTFB (Time To First Byte) | < 200ms sur CDN | `P1` |
| **Performance** | Tenue en charge | 1 000 utilisateurs concurrents sans dégradation | `P1` |
| **Disponibilité** | Disponibilité site public | 99.5% (max 3h40 de downtime/mois) | `P0` |
| **Disponibilité** | Disponibilité backoffice | 99% (en heures ouvrées) | `P0` |
| **Disponibilité** | Fenêtre de maintenance | Planifiée, communiquée 48h avant, hors horaires métier | `P1` |
| **Disponibilité** | RTO (reprise) | < 4h en cas d'incident majeur | `P0` |
| **Disponibilité** | RPO (perte de données) | < 24h (sauvegarde quotidienne) | `P0` |
| **Sécurité** | Chiffrement en transit | TLS 1.3, HSTS activé, note A sur SSL Labs | `P0` |
| **Sécurité** | Chiffrement au repos | AES-256 pour mots de passe (argon2), BD, backups, PDF | `P0` |
| **Sécurité** | OWASP Top 10 | Aucune vulnérabilité critique (OWASP 2021) | `P0` |
| **Sécurité** | CSP, en-têtes sécurisés | CSP strict, X-Frame-Options, Referrer-Policy en place | `P0` |
| **Sécurité** | Gestion des secrets | Vault / équivalent, rotation tous les 90j | `P1` |
| **Sécurité** | Rate limiting | API publique : 60 req/min/IP, login : 5 tentatives/15min/IP | `P0` |
| **Scalabilité** | Volumétrie cible année 1 | 100 000 utilisateurs, 10 000 abonnés, 5 000 articles | `P1` |
| **Scalabilité** | Scalabilité horizontale | Architecture stateless, services scalables | `P1` |
| **Scalabilité** | CDN | 95% des requêtes statiques servies par CDN | `P0` |
| **Accessibilité** | WCAG 2.1 niveau AA | Conformité complète | `P1` |
| **Accessibilité** | Compatibilité lecteurs d'écran | NVDA (Win), VoiceOver (Mac/iOS), TalkBack (Android) | `P1` |
| **Accessibilité** | Navigation clavier | 100% des fonctionnalités utilisables au clavier | `P1` |
| **Conformité** | Loi 2013-450 CI | Déclaration à l'ARTCI, DPO désigné | `P0` |
| **Conformité** | RGPD | Registre de traitement, DPO, procédure droits (accès, portabilité, oubli) | `P0` |
| **Conformité** | Cookies | Consentement granulaire conforme CNIL / ARTCI | `P0` |
| **Conformité** | Facturation | Facturation conforme (numérotation, TVA 18%, mentions légales CI) | `P0` |
| **UX** | Design responsive | Affichage correct de 320px (iPhone SE) à 1920px (desktop) | `P0` |
| **UX** | Temps moyen d'apprentissage backoffice | < 1h pour un rédacteur, < 4h pour un rédacteur en chef | `P1` |
| **UX** | Taux d'erreur (saisie) | < 2% sur les formulaires critiques (inscription, paiement) | `P1` |
| **Observabilité** | Logs centralisés | ELK / Loki ou équivalent, rétention 90j | `P0` |
| **Observabilité** | Métriques & alerting | Métriques business & techniques, alerting 24/7 sur incidents critiques | `P0` |
| **Observabilité** | Tracing distribué | OpenTelemetry sur parcours critiques | `P2` |
| **i18n** | Langue principale | Français (CI) | `P0` |
| **i18n** | Fuseau horaire | Africa/Abidjan (UTC+0) par défaut | `P0` |

---

## 11. Intégrations externes

La plateforme s'appuie sur **20 intégrations externes**, dont 7 sont critiques dès le MVP.

| Domaine | Besoin | Solution recommandée | Alternative | Priorité | Coût indicatif |
|---|---|---|---|:-:|---|
| **Paiement** | Mobile Money + Carte | CinetPay (CI leader) | PayDunya, Flutterwave, Wave direct | `P0` | ~2,5% commission |
| **Paiement récurrent** | Renouvellement auto | Tokenization CinetPay | Gestion manuelle + relances | `P1` | Inclus |
| **Email transactionnel** | Envois critiques | SendGrid | Mailjet, AWS SES | `P0` | ~15k FCFA/mois |
| **Email marketing** | Campagnes newsletter | Mailjet | Brevo, SendGrid | `P1` | ~45k FCFA/mois |
| **SMS** | Relances, 2FA, confirmations | Orange CI API / InfoBip | TextMe, Twilio | `P1` | ~25 FCFA/SMS |
| **CDN** | Accélération + anti-DDoS | Cloudflare Pro | AWS CloudFront, Bunny.net | `P0` | ~12k FCFA/mois |
| **Hébergement** | Serveurs + BD | OVH ou AWS | Azure, Scaleway, local CI | `P0` | 200 à 500k FCFA/mois |
| **Stockage objet** | Médias, PDF, archives | AWS S3 / Scaleway Object Storage | MinIO self-hosted | `P0` | ~10k FCFA/mois |
| **Recherche** | Moteur plein-texte | MeiliSearch self-hosted | Algolia, Elasticsearch | `P1` | Gratuit (self-hosted) |
| **Analytics** | Statistiques site | Matomo self-hosted | Plausible, Google Analytics 4 | `P1` | Gratuit (self-hosted) |
| **APM / Monitoring** | Supervision | Sentry + UptimeRobot | Datadog, New Relic | `P0` | ~15k FCFA/mois |
| **Logs** | Centralisation | Grafana Loki | ELK, Datadog Logs | `P1` | Gratuit |
| **Auth sociale** | Login Google, Facebook | OAuth direct | Auth0, Firebase Auth | `P2` | Gratuit |
| **Livraison colis** | Suivi papier | La Poste / Chronopost CI | Transporteur privé | `P2` | Selon contrat |
| **Viewer PDF** | Lecture en ligne | PDF.js | PSPDFKit | `P1` | Gratuit |
| **Comptabilité** | Export comptable | Connecteur Sage / CSV | API comptable | `P2` | Selon éditeur |
| **Réseaux sociaux** | Publication auto | Buffer API | Zapier | `P2` | ~12k FCFA/mois |
| **Chat support** | Assistance en ligne | Crisp (free tier) | Intercom, Zendesk | `P2` | Gratuit |
| **IA / LLM** | Résumés, suggestions | API Claude (Anthropic) | OpenAI, Azure OpenAI | `P3` | Selon usage |
| **KYC Entreprise** | Vérif identité pro | Connect CI (si dispo) | Vérification manuelle | `P3` | À évaluer |

---

## 12. Risques et mitigations

Dix risques majeurs ont été identifiés, classés par domaine. Chaque risque est associé à une stratégie de mitigation.

| ID | Domaine | Risque | Prob. | Impact | Mitigation |
|:-:|---|---|:-:|:-:|---|
| **R1** | Produit | Faible adoption initiale des abonnements | Moyen | Élevé | Plan marketing dès le lancement, offre d'essai gratuite 14 jours, prix d'appel, partenariats influenceurs, contenu gratuit de qualité pour attirer. |
| **R2** | Produit | Contenu éditorial insuffisant ou non différenciant | Moyen | Élevé | Constitution d'une rédaction solide dès M0, charte éditoriale claire, plan de production pré-lancement (stock d'articles), partenariats avec écoles/universités. |
| **R3** | Technique | Dépendance excessive à un prestataire de paiement | Faible | Élevé | Architecture adaptateur/connecteur, couche d'abstraction permettant le basculement vers PayDunya ou Flutterwave en cas de défaillance CinetPay. |
| **R4** | Technique | Performance dégradée sur réseaux mobiles bas débit | Élevé | Moyen | Optimisation mobile-first, CDN, images WebP/AVIF, lazy-loading, SSR, bundles légers, tests réels sur 3G simulés. |
| **R5** | Sécurité | Fuite de données abonnés (RGPD, loi 2013-450) | Faible | Critique | Chiffrement au repos, 2FA obligatoire pour ADM/SUP, principe du moindre privilège, pentest annuel, procédure de notification sous 72h, souscription cyber-assurance. |
| **R6** | Sécurité | Partage illicite de PDF premium entre utilisateurs | Élevé | Moyen | Watermark dynamique (email + IP + timestamp), limitation du nombre de téléchargements, surveillance des forums/groupes de partage, actions juridiques. |
| **R7** | Conformité | Non-conformité cookies / consentement | Moyen | Moyen | Bandeau conforme, granularité par catégorie, journal des consentements, audit juridique avant prod, DPO désigné. |
| **R8** | Opérationnel | Difficulté à maintenir la cadence éditoriale | Moyen | Moyen | Calendrier éditorial anticipé, planification par sprints, réseau de pigistes de confiance, hiérarchisation claire des contenus. |
| **R9** | Financier | Dépassement budgétaire sur le MVP | Moyen | Élevé | Cadrage serré en phase 0, pilotage par jalons, sprints time-boxés, arbitrage rigoureux P0 vs P1 dès que des risques apparaissent. |
| **R10** | Marché | Concurrence d'un média existant qui digitalise son offre | Moyen | Moyen | Différenciation forte par la qualité éditoriale, rapidité d'exécution, création d'une communauté engagée, positionnement exclusif "marketing ivoirien". |

---

## 13. Gouvernance projet

### 13.1. Instances de pilotage

Trois instances rythment la vie du projet :

- **COPIL — Comité de pilotage** *(mensuel)* : sponsor (DG), direction produit, responsable éditorial, lead technique. Décisions stratégiques, arbitrages budgétaires, validation des jalons.
- **COPROJ — Comité projet** *(hebdomadaire)* : direction produit, lead technique, rédactrice en chef, responsable commercial. Suivi d'avancement, blocages, arbitrages de priorité.
- **Sprints & dailys** : cadence Scrum de 2 semaines, daily de 15 min, planning/review/rétrospective à chaque sprint. Le Product Owner arbitre le backlog au fil de l'eau.

### 13.2. Équipe cible

| Profil | ETP | Responsabilités |
|---|:-:|---|
| Product Owner / Chef de projet | 1 | Backlog, priorisation, interface métier ↔ tech, suivi livraisons. |
| Lead technique / Architecte | 1 | Architecture, choix techniques, revue de code, mentoring. |
| Développeurs backend | 2 | API, logique métier, intégrations, paiement, données. |
| Développeurs frontend | 2 | Frontend public (Next.js), backoffice (React), intégration design. |
| UX / UI Designer | 1 | Design system, maquettes Figma, prototypes, design review. |
| QA / Test | 1 | Plan de tests, tests manuels, automatisation, non-régression. |
| DevOps (mi-temps) | 0,5 | Infrastructure, CI/CD, monitoring, sauvegardes, sécurité technique. |
| Data / Analytics (V1+) | 0,5 | Dashboards, KPIs, segmentation, reporting. |

**Total :** équipe cœur de **8 à 9 ETP** en phase MVP / V1, réduction possible à 5 à 6 ETP en V2, équipe à géométrie variable en V3 selon les projets retenus.

### 13.3. Outillage projet

| Domaine | Outils recommandés |
|---|---|
| Gestion du backlog | Jira, Azure DevOps, Linear (import direct depuis l'Excel fourni) |
| Documentation | Confluence, Notion, GitBook |
| Design | Figma (design system partagé) |
| Code | GitHub ou GitLab (revue de code systématique via Pull Requests) |
| CI/CD | GitHub Actions, GitLab CI (déploiement auto sur INT et REC) |
| Communication | Slack, Teams (canaux dédiés : tech, produit, rédaction, incidents) |

---

## 14. Annexes

### 14.1. Synthèse chiffrée du backlog

**Répartition par priorité :**

| Priorité | Nombre d'US | % |
|---|:-:|:-:|
| `P0` | 61 | 36% |
| `P1` | 62 | 37% |
| `P2` | 34 | 20% |
| `P3` | 9 | 5% |
| **Total** | **166** | **100%** |

**Répartition par phase :**

| Phase | Nombre d'US | % |
|---|:-:|:-:|
| `MVP` | 71 | 42% |
| `V1` | 78 | 46% |
| `V2` | 17 | 10% |
| `V3` | 0 | 0% |
| **Total** | **166** | **100%** |

**Répartition par estimation T-shirt :**

| Taille | Nombre d'US | Équivalent en jours |
|---|:-:|---|
| `XS` | 3 | < 0,5 j |
| `S` | 29 | 0,5 à 1 j |
| `M` | 73 | 1 à 3 j |
| `L` | 52 | 3 à 5 j |
| `XL` | 9 | > 5 j |

### 14.2. Documents connexes

Pour compléter ce document, les livrables suivants sont à produire :

- [ ] Charte graphique et design system complet (palette, typographie, composants)
- [ ] Maquettes Figma des écrans clés (backoffice + frontend)
- [ ] Dossier d'architecture technique détaillé (DAT) — à produire lors du kick-off
- [ ] Plan de tests et de recette — à produire en phase de conception
- [ ] Registre des traitements RGPD — à produire avec le DPO
- [ ] Plan de reprise d'activité (PRA) — à produire avant la mise en production

### 14.3. Prochaines étapes

Pour initier concrètement le projet :

1. **Validation du présent document** par le COPIL (arbitrages périmètre, budget, calendrier)
2. **Choix définitif des briques techniques** (stack, hébergement, fournisseurs)
3. **Constitution de l'équipe projet** (recrutements internes ou prestataires)
4. **Production des maquettes Figma** pour les parcours critiques
5. **Mise en place de l'environnement de développement** (repos, CI/CD, outils)
6. **Lancement du premier sprint MVP**

### 14.4. Fichiers sources

Ce document Markdown fédère le contenu des deux livrables précédents :

- `Backlog_Genie_Marketing_Mag.xlsx` — fichier Excel exploitable (importable dans Jira / Azure DevOps)
- `Backlog_Genie_Marketing_Mag_Architecture.docx` — document Word narratif

---

*Fin du document · GÉNIE MARKETING Mag · Backlog produit v1.0 · 20 avril 2026*
