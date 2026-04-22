# Changelog — GÉNIE MARKETING Mag

Format inspiré de [Keep a Changelog](https://keepachangelog.com/fr/1.1.0/).
Versionning : [SemVer](https://semver.org/spec/v2.0.0.html).

## [Unreleased] — consolidation avant lancement

### Sprint 10 — Conformité & découverte (2026-04-22)
- **Bandeau cookies CNIL/ARTCI** Alpine.js (Tout accepter / Refuser / Personnaliser) avec stockage `localStorage`.
- **4 pages légales** câblées depuis les paramètres : `/mentions-legales`, `/confidentialite`, `/cgu`, `/cookies`.
- **Tracking vues d'articles** : table `article_views` avec fingerprint IP+UA+session, dédoublonnage horaire, `articles.views_count` auto-incrémenté, colonne Vues dans l'admin.
- **Recherche plein-texte** : `Article` devient `Searchable` via Scout, driver `collection` en dev et `meilisearch` en prod. Route `/recherche` + icône loupe dans le header.
- `SCOUT_DRIVER=null` forcé en test pour isoler l'indexation.

### Sprint 9 — Déploiement (2026-04-22)
- **Dockerfile production multi-stage** (composer → vite → php-fpm 8.2-alpine + supervisord).
- **docker-compose.prod.yml** : app + nginx + mysql 8.4 + redis 7 avec healthchecks.
- Configs prod : opcache JIT, php-fpm tuning, nginx TLS + HSTS/CSP + cache immuable.
- **Workflow release GitHub Actions** : build + push vers ghcr.io sur tag `v*.*.*`.
- **EP-15 Paramètres** : table `settings`, 18 paramètres seedés en 6 groupes, UI admin `/admin/parametres`, bannière promo activable.
- **EP-21 SEO** : sitemap.xml dynamique, robots.txt, Open Graph complet, middleware `AddPublicCacheHeaders`.

### Sprint 8 — Newsletter (2026-04-22)
- **EP-10 complet** : listes (`newsletters`), abonnements avec double opt-in (`newsletter_subscriptions`), campagnes (`campaigns`) avec Markdown + CTA.
- `SendCampaignJob` (chunk 100) pour l'envoi asynchrone via queue Redis.
- Admin Livewire complet : `CampaignList` + `CampaignEditor` avec KPIs et bouton "Envoyer maintenant".
- **EP-04 CRUD rubriques** complet (create + edit + delete en modal Livewire).
- Routes publiques newsletter sécurisées par tokens uniques.
- 11 tests Pest additionnels (Auditable, Newsletter, MagazineAccess). **Total : 67 tests verts.**

### Sprint 7 — Engagement & magazine (2026-04-22)
- **EP-14 Audit** : trait `Auditable` automatique sur Article/Order/Subscription/User/Comment/MagazineIssue, `AuditAuthEvents` (login/logout/failed), page `/admin/audit` avec export CSV.
- **EP-06 Magazine** : CRUD numéros (cover + PDF), lecteur pdf.js avec watermark dynamique `email·IP·timestamp` + overlay diagonale + raccourcis Ctrl+S/P bloqués.
- **EP-12 Commentaires** : réservés aux abonnés, modération systématique, page `/admin/commentaires` avec files approuvés/rejetés.
- **Image picker TipTap** : bouton toolbar → modal `MediaPicker` → insertion node image avec alt.

### Sprint 6 — WYSIWYG & tests (2026-04-22)
- **WYSIWYG TipTap** via importmap ESM + Alpine.js, toolbar (H2–H4, B/I/U/S, listes, citations, liens, YouTube).
- Service `TiptapRenderer` côté public (XSS-safe, clamp headings, block `javascript:`).
- Migration transparente legacy `{blocks:[...]}` → doc TipTap JSON.
- **Bibliothèque média** `/admin/medias` (upload multiple, filtres type, modale métadonnées, suppression avec garde cover).
- **31 tests Pest** (FreemiumCounter, TiptapRenderer, PaystackGateway, CheckoutService, ArticleAccess).
- Fixes Breeze par défaut après renommage `name` → `first_name`/`last_name`.

### Sprint 5 — Paywall mature & comms (2026-04-22)
- **EP-09 Paywall v2** : `isAccessibleBy` refondu (subscription active + AccessRight non expiré + BO), `FreemiumCounter` (3 articles/mois/fingerprint sans cookie).
- Mail `SubscriptionConfirmed` envoyé depuis `CheckoutService::finalizeOrder`.
- Admin commercial Livewire : `OrderList` (KPIs revenue, filtres) et `SubscriptionList` (KPIs MRR, expirations ≤ 30j).
- **Facture PDF** via DomPDF : template `invoices.pdf`, route `/compte/factures/{number}/pdf`, policy ownership.

### Sprint 4 — Commercialisation Paystack (2026-04-22)
- **EP-07/EP-11** : 7 tables (plans, subscriptions, orders, payments, invoices, promo_codes, access_rights), 3 enums.
- **Abstraction `PaymentGateway`** : `PaystackGateway` (initialize, verify, parseWebhook HMAC SHA512). `PaymentServiceProvider` configure le driver via `PAYMENT_GATEWAY`.
- **`CheckoutService`** : création Order pending, finalisation idempotente (Order paid + Subscription trialing/active + Invoice + rôle ab-d/c/e), markFailed.
- Endpoints `POST /abonnement/{plan:code}/checkout`, `GET /paiement/callback`, `POST /webhooks/paystack`.
- UI `/abonnement` branchée sur vraies données + `/compte` (abonnement actif, historique commandes, factures).

### Sprint 3 — Backoffice éditorial complet (2026-04-22)
- Middleware `EnsureBackofficeUser` + `ArticlePolicy`.
- Vrais articles sur home + route générique `/rubriques/{category:slug}`.
- **`ArticleEditor` Livewire** : WYSIWYG basique, workflow (draft→review→published→archived), upload cover, snapshot ArticleVersion.
- **`ArticleList`** avec filtres + pagination, RED ne voit que ses propres articles.
- Paywall basique avec preview 30% + CTA.

### Sprint 2 — Chaîne éditoriale (2026-04-22)
- CRUD utilisateurs BO (avec UserPolicy, envoi lien activation).
- 10 migrations éditoriales + modèles Eloquent + seeders (catégories + articles de démo).
- Livewire `CategoryList` (liste).

### Sprint 1 — Fondation (2026-04-22)
- 3 scaffolding (Laravel 11, Next.js 15, Vite+React) — abandonnés après pivot.
- Pivot monolithe Laravel + Blade + Livewire 3 + Tailwind 3.
- Template HTML v2 découpé en layouts/components Blade.
- Routing dual : public + `/admin/*`, Breeze auth.
- Tests Pest bootstrap.

### Sprint 0 — Setup (2026-04-21)
- Monorepo initial, Dockerfile dev, CI GitHub Actions, design tokens, ADR initiaux.
- Choix stack définitif : Laravel + Livewire + MySQL + Sessions.

## Conventions

- **Versions** : `v0.1.0-rc1` = première candidate release, puis `v1.0.0` au lancement commercial.
- **Migrations** : ordonnées par timestamp, préfixe domaine pour grouper visuellement.
- **Tests** : `vendor/bin/pest` doit rester vert à chaque sprint. Objectif > 80 % de couverture sur `app/Services/*` et `app/Models/*`.
