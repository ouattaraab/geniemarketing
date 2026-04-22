# GÉNIE MARKETING Mag

[![Tests](https://img.shields.io/badge/tests-81%20passing-B40F1E?style=flat-square)](./web/tests)
[![Laravel](https://img.shields.io/badge/Laravel-11-B40F1E?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-4F5D95?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![Livewire](https://img.shields.io/badge/Livewire-3-FF2D20?style=flat-square)](https://livewire.laravel.com)
[![License](https://img.shields.io/badge/license-Proprietary-2D2D2D?style=flat-square)](#licence)

Plateforme éditoriale du magazine de référence du marketing ivoirien et panafricain — **frontend public (lecteurs + abonnés)** et **backoffice (rédaction + commerce + admin)** dans un monolithe Laravel unique partageant un design éditorial soigné.

> **Décrypter · Inspirer · Transformer** — le magazine qui décode le marketing ivoirien.

## Sommaire

- [État du projet](#état-du-projet)
- [Fonctionnalités](#fonctionnalités)
- [Stack technique](#stack-technique)
- [Structure du repo](#structure-du-repo)
- [Démarrage rapide](#démarrage-rapide)
- [Tests](#tests)
- [Déploiement](#déploiement)
- [Documentation](#documentation)

## État du projet

- **Version actuelle** : `v0.1.0-rc1` — release candidate MVP + V1 complète
- **22 épiques du backlog livrées** (165 user stories)
- **81 tests Pest · 204 assertions · 100 % green**
- **Production-ready** : Dockerfile multi-stage, CI/CD, sécurité OWASP, conformité RGPD/ARTCI

## Fonctionnalités

### Pour les lecteurs (frontend)
- Accueil éditorial avec slider La Une + derniers articles
- Navigation par rubriques (Analyses, Succès, Interviews, Tribunes, Magazine)
- Lecture d'article avec sommaire, auteurs, temps de lecture, boutons de partage social (LinkedIn/X/WhatsApp/Email/copie lien)
- **Paywall v2** : accès libre / registered / abonnés / premium, avec compteur freemium (3 articles gratuits/mois sans cookie)
- Recherche plein-texte (MeiliSearch via Scout)
- Inscription newsletter avec double opt-in
- Commentaires sous modération systématique (réservé abonnés)
- Magazine papier + PDF avec **lecteur protégé** (pdf.js + watermark dynamique `email·IP·timestamp`)

### Pour les abonnés (espace compte)
- Tableau de bord abonnement actif (dates, renouvellement, trial)
- Historique des commandes + factures PDF téléchargeables
- Gestion préférences newsletter + données RGPD (export/suppression)

### Pour la rédaction (backoffice)
- **Éditeur WYSIWYG TipTap** (H2-H4, gras, italique, listes, citations, liens, images depuis bibliothèque, embed YouTube)
- Workflow complet : brouillon → relecture → planifié → publié → archivé
- Bibliothèque média centralisée (upload multiple, alt obligatoire, filtres par type)
- Gestion des rubriques (hiérarchie 1 niveau) et catégories éditoriales
- Versions d'articles automatiques (snapshot à chaque save)

### Pour le commerce
- **Paiement Paystack** (cartes, Mobile Money, virement) avec webhook signé HMAC SHA512
- 3 formules : Digital 24k · Combo 48k · Entreprise 320k FCFA/an
- Codes promo (% ou fixe), périodes d'essai 14 j, facturation auto avec TVA CI
- Newsletter : campagnes Markdown, envoi asynchrone (queue), stats d'envoi
- Dashboards KPIs : revenus 30j + sparkline, top articles, MRR, expirations ≤ 30j

### Pour l'administration
- Gestion utilisateurs BO (création avec lien d'activation, rôles Spatie)
- **2FA TOTP obligatoire pour ADM/SUP** (Google Auth + 8 codes de récupération)
- Journal d'audit horodaté avec export CSV (rétention 12 mois RGPD)
- Paramètres administrables (branding, contact, légal, social, promo, paywall)
- Modération commentaires avec files approuvés/rejetés

## Stack technique

| Couche | Choix |
|---|---|
| Application | **Laravel 11** monolithique (PHP 8.2+) |
| SSR / vues | Blade + **Livewire 3** + Alpine.js |
| Auth | Sessions + Breeze + **2FA TOTP** (Google2FA) |
| Permissions | `spatie/laravel-permission` (9 rôles métier) |
| CSS | Tailwind CSS 3 + design tokens `@gm/ui-tokens` (Zilla Slab, Mulish, JetBrains Mono) |
| BD | MySQL 8+ (32 migrations) |
| Cache / Queue | Redis 7 |
| Stockage | S3-compatible (AWS S3, Scaleway, MinIO en dev) |
| Recherche | **MeiliSearch** via Scout (driver collection en dev) |
| Email | SendGrid/Mailjet (transactionnel + marketing) |
| Paiement | **Paystack** (abstraction `PaymentGateway` permettant de switcher) |
| PDF | DomPDF (factures) + pdf.js (lecteur magazine) |
| Monitoring | Sentry (erreurs), UptimeRobot (healthcheck `/up`) |
| Infra | Docker multi-stage · nginx TLS · supervisord |

## Structure du repo

```
gm/
├── web/                          Laravel monolithe
│   ├── app/
│   │   ├── Concerns/Auditable    trait audit automatique
│   │   ├── Contracts/            PaymentGateway interface
│   │   ├── Enums/                ArticleStatus, OrderStatus, ...
│   │   ├── Http/Controllers/     Public/ + Admin/ + Auth/
│   │   ├── Http/Middleware/      EnsureBackofficeUser, RequireTwoFactor, ...
│   │   ├── Livewire/Admin/       17 composants Livewire BO
│   │   ├── Livewire/Public/      CommentSection
│   │   ├── Mail/                 4 mailables
│   │   ├── Models/               24 modèles Eloquent
│   │   └── Services/             Audit, CheckoutService, FreemiumCounter,
│   │                             MediaManager, PaystackGateway, TiptapRenderer,
│   │                             ArticleViewTracker, TwoFactorAuth
│   ├── database/migrations/      32 migrations
│   ├── database/seeders/         Role, Category, EditorialCategory, Plan,
│   │                             Newsletter, Setting, DemoArticle
│   ├── resources/views/
│   │   ├── components/gm/        top-strip, header, footer, article-card,
│   │   │                         rich-editor (TipTap), cookie-banner, share-buttons
│   │   ├── components/layouts/   public, admin
│   │   ├── public/               home, article, magazine, subscribe, account, legal/*
│   │   ├── admin/                dashboard
│   │   ├── livewire/admin/       17 vues Livewire
│   │   └── emails/               4 templates (confirmation abonnement,
│   │                             newsletter confirmation/campaign, renewal)
│   ├── routes/                   web, admin, auth, api, console (scheduler)
│   ├── tests/                    Pest — 81 tests · 204 assertions
│   ├── Dockerfile                multi-stage production
│   └── infra/docker/prod/        compose + nginx + php-fpm + supervisord
├── packages/ui-tokens/           design tokens partagés
├── docs/                         ARCHITECTURE, RUNBOOK
├── infra/docker/                 dev compose (MySQL, Redis, MinIO, Mailpit, Meili)
├── template/                     charte graphique HTML de référence
├── .github/workflows/            web.yml (tests) + release.yml (build ghcr.io)
├── Makefile                      16 cibles DX
├── CHANGELOG.md                  12 sprints documentés
└── Backlog_Genie_Marketing_Mag.md  backlog produit complet (166 US, 22 épiques)
```

## Démarrage rapide

```bash
# Prérequis : PHP 8.4+, Composer 2, Node 20+, MySQL 8 ou Docker, Git
git clone https://github.com/ouattaraab/geniemarketing.git gm
cd gm

# Services Docker (MySQL, Redis, MinIO, Mailpit, MeiliSearch)
make up

# Laravel
cd web
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install && npm run build
php artisan serve          # http://localhost:8000
```

Compte super admin seedé : `admin@geniemag.ci` / `ChangeMe!2026` (à changer au premier login).

## Tests

```bash
make test                    # 81 tests · ~2.5s
make test-coverage           # avec couverture (requires Xdebug/pcov)
```

Suites :
- **Unit** (18) : FreemiumCounter, PaystackGateway (signatures webhook), TiptapRenderer (XSS-safe, marques, YouTube, links)
- **Feature** (63) : Auth Breeze, ArticleAccess (policies + subs + AccessRight + BO), CheckoutService (idempotence), Newsletter (opt-in/confirm/unsubscribe), MagazineAccess (abonnement requis), Auditable (trait), 2FA (activation/désactivation/codes récup), Livewire ArticleEditor, Livewire CommentSection, Profile, Registration

## Déploiement

Voir [`docs/RUNBOOK.md`](docs/RUNBOOK.md) pour les procédures complètes.

```bash
# Release d'une nouvelle version
git tag -a v1.2.3 -m "Release 1.2.3"
git push --tags
# → GitHub Actions build + push ghcr.io/ouattaraab/gm-web:1.2.3

# Sur le serveur
make prod-deploy             # pull + rollout + migrations + cache
make pre-launch              # check-list automatique
```

## Documentation

- [**Backlog produit**](Backlog_Genie_Marketing_Mag.md) — 22 épiques, 166 user stories
- [**Architecture technique**](docs/ARCHITECTURE.md) — choix de stack, sécurité, modèle de données
- [**Runbook ops**](docs/RUNBOOK.md) — déploiement, rollback, backups, incidents
- [**Infra production**](web/infra/docker/prod/README.md) — compose prod + nginx + TLS
- [**Design tokens**](packages/ui-tokens/README.md) — palette + typographies
- [**Changelog**](CHANGELOG.md) — 12 sprints de développement

## Charte graphique

Couleur primaire : **`#B40F1E`** (rouge crimson) · Accent : `#D81B2A` · Fond : `#FAF8F4` (papier)

Typographies : **Zilla Slab** (italique, titres éditoriaux) · **Mulish** (corps) · **JetBrains Mono** (métadonnées, dates)

## Contribution

Voir [CONTRIBUTING.md](CONTRIBUTING.md).

## Licence

Propriétaire — GÉNIE MARKETING Mag, Abidjan. Code confidentiel, usage soumis à autorisation écrite préalable de l'éditeur.
