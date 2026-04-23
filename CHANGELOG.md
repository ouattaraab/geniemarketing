# Changelog — GÉNIE MARKETING Mag

Format inspiré de [Keep a Changelog](https://keepachangelog.com/fr/1.1.0/).
Versionning : [SemVer](https://semver.org/spec/v2.0.0.html).

## [Unreleased] — consolidation avant lancement

### Sprint 11 — Paiement Wave, admin commercial, RGPD & charte auth (2026-04-23)

**Passage de Paystack à Wave Business** pour le paiement des abonnements
(Mobile Money natif CI/UEMOA, frais marchand 1 %). Paystack conservé en
fallback activable via `PAYMENT_GATEWAY=paystack`.

- **`WaveGateway`** (remplace Paystack par défaut) :
  - POST `/v1/checkout/sessions` → hosted checkout `pay.wave.com`.
  - GET `/v1/checkout/sessions/:id` → verify au callback.
  - Webhook `Wave-Signature: t=…,v1=…` HMAC-SHA256 + anti-replay ±5 min/30 s.
  - Particularité XOF : conversion `centsToUnits`/`unitsToCents` pour
    préserver la défense montant/devise du `CheckoutService` (inchangée).
  - Nouveau : `WaveWebhookController`, middleware `VerifyWaveWebhookIp`,
    alias `wave.ip`, routes `/webhooks/wave`.
  - `FakePaymentGateway` paramétrable par provider code pour le dev.
  - Documentation `docs/WAVE_INTEGRATION.md` (architecture, config, tests,
    déploiement, dépannage).

- **CRUD admin des formules d'abonnement** (rôles `com`/`adm`/`sup`) :
  - `/admin/formules` — liste avec KPIs (abonnés, statut), toggle actif,
    édition prix/durée/essai/licences/avantages, suppression (sup seul,
    refusée si des abonnements sont liés).
  - `/admin/formules/nouvelle` et `/{plan:code}/editer` — `PlanEditor`
    Livewire avec code immuable après création (ré-affirmé serveur,
    pas seulement readonly UI).

- **Activation des moyens de paiement** (rôles `adm`/`sup`) :
  - Nouvelle table `payment_methods` + seeder (Wave activé, Paystack
    désactivé par défaut).
  - `/admin/moyens-paiement` — toggle on/off, cache 5 min.
  - `PaymentServiceProvider` lit `PaymentMethod::isEnabled()` et bascule
    automatiquement sur le premier provider actif. Si tous désactivés,
    `/abonnement` affiche une bannière rouge « temporairement indisponible »
    avec boutons « Indisponible » désactivés.

- **Alignement charte graphique des pages auth et /profile** (avant : layout
  Breeze en anglais) :
  - `/login`, `/register`, `/forgot-password`, `/reset-password`,
    `/confirm-password`, `/verify-email` refondues avec `x-layouts.public`,
    carte bordée, `gm-btn-primary`, `gm-meta`, libellés français.
  - `/profile` + partials (infos, mot de passe, suppression) refondus ;
    modal de suppression via Alpine (remplace `x-modal` Breeze).
  - `/dashboard` également migré.
  - **Déconnexion** : dropdown Alpine « Mon compte » dans le header
    (Mon abonnement / Mon profil / Se déconnecter) + bouton dédié sur
    `/compte`.

- **Documents légaux exhaustifs** (loi ivoirienne 2013-450 / 2013-451 /
  2016-555 / 2017-867 + RGPD UE) :
  - `/cgu` : 21 articles (rétractation, résiliation, PI, responsabilité
    plafonnée, force majeure, droit ivoirien, Abidjan).
  - `/confidentialite` : responsable, DPO, tableau finalités × bases
    légales, sous-traitants, transferts, 9 droits utilisateur, violation
    < 72 h ARTCI.
  - `/mentions-legales` : éditeur (forme, capital, RCCM, NIF, CC),
    directeur de publication (loi 2017-867), hébergeur obligatoire (loi
    2013-451), PI, PLCC.
  - `/cookies` : 4 catégories tableau, GPC respecté, pas de cookies tiers.
  - 15 nouveaux settings groupe `legal`, dates de mise à jour
    paramétrables (anti-pattern `now()` dynamique éliminé).
  - `Setting::get` rendu tolérant à l'absence de la table (tests sans
    migrations).

- **Droits RGPD concrets côté utilisateur** :
  - Traçabilité append-only des consentements (table `consents`,
    horodatage + IP + UA + version du document) — preuve opposable
    enregistrée au checkout (CGU + confidentialité + marketing optionnel).
  - Export JSON (GET `/compte/mes-donnees/export`, throttle 3/10 min) :
    compte, abonnements, commandes, paiements, factures, consentements,
    newsletter, commentaires. Exclut password / 2FA secret / remember_token.
  - Suppression de compte durcie (`AccountEraser`) : anonymise PII,
    conserve orders/invoices anonymisés (obligation comptable 10 ans),
    purge sessions, password_reset_tokens, access_rights, anonymise
    audit_logs, annule les abonnements actifs, enregistre
    `Consent::ACTION_REVOKED` comme preuve de la demande.
  - `/profile` expose l'historique des consentements et le bouton d'export.

- **Revue de sécurité ciblée et corrections** :
  - H1 — vérification `hash_equals` du session id gateway avant
    finalisation (prévient un rejeu webhook sur une order différente
    du même montant).
  - H2 — `AccountEraser` purge désormais sessions, password_reset_tokens,
    access_rights + anonymise audit_logs ; `lockForUpdate` en tête de
    transaction contre la race multi-device.
  - M3 — `PlanEditor` force l'immuabilité du `code` côté serveur.
  - M5 — `PrivacyController::export` inscrit un audit log
    (`privacy.data_exported`) pour détecter les exfiltrations.
  - M6 — `.env.production.example` : `TRUSTED_PROXIES=*` retiré avec
    un long commentaire sur l'impact probatoire (recommandation
    Cloudflare CIDRs).
  - L1 — email anonymisé contient user_id (anti-collision).
  - L4 — webhook order introuvable rétrogradé `critical` → `warning`.

- **Tests** : +20 tests Pest (`WaveGatewayTest` Unit + Feature,
  `LegalPagesTest`, `PrivacyExportTest`, `ConsentTest`, session id H1).
  **Total : 105 tests verts (312 assertions).**

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
