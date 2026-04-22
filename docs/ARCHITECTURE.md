# Architecture technique — GÉNIE MARKETING Mag

> **Version** : 0.1 (DAT initial, Phase 0) · **Statut** : Draft

## 1. Vue d'ensemble

```
┌──────────────────────────────────────────────────────┐
│              Utilisateurs                            │
│   Visiteurs · Abonnés · Équipe BO · Admins           │
└──────────────────────────────────────────────────────┘
                         │
                         ▼
              ┌──────────────────┐
              │  Cloudflare CDN  │
              │  (WAF + cache)   │
              └──────────────────┘
              │                │
              ▼                ▼
      ┌─────────────┐   ┌─────────────┐
      │   web/      │   │  admin/     │
      │  Next.js 15 │   │  React+Vite │
      │  SSR + ISR  │   │   SPA       │
      │ geniemag.ci │   │ admin.*.ci  │
      └─────────────┘   └─────────────┘
              │                │
              └────────┬───────┘
                       ▼
            ┌──────────────────────┐
            │       api/           │
            │   Laravel 11 (PHP)   │
            │  JWT + Spatie roles  │
            └──────────────────────┘
                       │
     ┌─────────┬───────┼────────┬──────────┐
     ▼         ▼       ▼        ▼          ▼
  ┌──────┐ ┌────────┐ ┌─────┐ ┌──────┐ ┌────────┐
  │MySQL │ │Meili   │ │Redis│ │ S3 / │ │Horizon │
  │  8+  │ │Search  │ │Queue│ │MinIO │ │Workers │
  └──────┘ └────────┘ └─────┘ └──────┘ └────────┘
                       │
                       ▼
      ┌────────────────────────────────────┐
      │  Intégrations externes             │
      │  CinetPay · SendGrid · Mailjet ·   │
      │  Orange SMS · Sentry · Matomo      │
      └────────────────────────────────────┘
```

## 2. Stack validée (monolithe Laravel)

| Couche | Choix | Justification |
|---|---|---|
| **App** | Laravel 11 (PHP 8.2+) — un seul projet `web/` | SSR natif pour SEO éditorial, cohérence BO/public |
| **Vues** | Blade + Livewire 3 + Alpine.js | Composants dynamiques sans SPA, design 100% aligné template |
| **Auth** | Sessions Laravel + Breeze (Blade scaffolding) | Simple, CSRF natif, 2FA via Fortify en V1 |
| **Permissions** | `spatie/laravel-permission` | Rôles & permissions granulaires |
| **DB** | MySQL 8+ | Choix équipe, robustesse, support Laravel natif |
| **CSS** | Tailwind CSS 3 + tokens GM | Palette `#B40F1E`, fontes Zilla Slab / Mulish / JetBrains Mono |
| **Cache / Queue** | Redis 7 (prod), file/sync (dev) | Cache app + queues (emails, PDF, image) |
| **Stockage** | S3-compatible (MinIO dev) | Médias, PDFs, archives |
| **Recherche** | MeiliSearch via `laravel/scout` | Plein-texte FR, self-hosted |

## 3. Auth & sécurité

### Sessions Laravel (Breeze)

- Session cookie HttpOnly SameSite=Lax, chiffré, CSRF natif.
- Un seul modèle `User` avec colonne `type` (`backoffice` | `subscriber`) + rôles Spatie.
- Backoffice sous `/admin` avec middleware `auth` + contrôle rôle (et `type = backoffice`) ; frontend public sans authentification requise, sauf espace abonné.
- 2FA (TOTP) obligatoire pour rôles `adm` et `sup` dès la V1 via `laravel/fortify`.
- Password hashing : **bcrypt** (default Laravel 11, argon2id disponible si besoin).

### Permissions

- `spatie/laravel-permission` pour les rôles & permissions granulaires.
- Rôles seedés : `red`, `chef`, `edit`, `com`, `adm`, `sup`, `ab-d`, `ab-c`, `ab-e`.
- Permissions : format `{objet}.{action}` (ex. `article.create`, `subscription.manage`).

### Sécurité (OWASP Top 10)

- TLS 1.3 + HSTS + CSP stricte.
- Rate limiting Laravel : `throttle:api` (60/min) + `throttle:login` (5/15 min).
- Validation stricte via Form Requests, `array`/`integer`/`email` explicites.
- CSRF : frontend public stateless (JWT), admin avec cookie Sanctum si SPA same-site.

## 4. Modèle de données (domaines)

Cf. `Backlog_Genie_Marketing_Mag.md` §7. 20 entités principales en 5 domaines :

1. **Identité & accès** : `users`, `roles`, `permissions`, `sessions`.
2. **Contenu éditorial** : `articles`, `article_versions`, `authors`, `categories`, `tags`, `editorial_categories`, `media`, `media_collections`.
3. **Magazine** : `magazines`, `magazine_issues`, `issue_summary_entries`.
4. **Commercial** : `subscription_plans`, `subscriptions`, `promo_codes`, `orders`, `payments`, `invoices`, `access_rights`.
5. **Communication** : `newsletters`, `newsletter_subscriptions`, `campaigns`, `comments`, `audit_logs`.

Conventions :

- UUIDs (`ULID` préféré pour tri chronologique) sur toutes les entités exposées côté public.
- Soft deletes systématiques sur `articles`, `users`, `orders`.
- Timestamps `created_at`, `updated_at` + `deleted_at` (soft).
- Toutes les dates stockées en UTC, rendues en `Africa/Abidjan` (UTC+0).

## 5. Conventions code

### PHP / Laravel

- PHP 8.4, types stricts, `declare(strict_types=1);` dans les nouveaux fichiers.
- **Laravel Pint** (PSR-12) en pre-commit.
- Structure : Controllers fins → Form Requests → Actions/Services → Repositories → Models.
- Tests : **Pest** + feature tests par endpoint, min 70% couverture sur domaines métier.

### TypeScript

- Strict mode activé (`strict: true`, `noUncheckedIndexedAccess: true`).
- ESLint + Prettier (config partagée).
- Composants React : function components + hooks uniquement.
- Conventions noms : `PascalCase` composants, `camelCase` hooks (préfixe `use`), `kebab-case` fichiers route.

### Git

- Branches : `main` (prod), `develop` (intégration), `feat/US-XXX-desc`, `fix/xxx`, `chore/xxx`.
- Commits : **Conventional Commits** (`feat:`, `fix:`, `chore:`, `docs:`, `refactor:`, `test:`).
- PR obligatoires vers `develop` + 1 review + CI green.

## 6. Environnements

| Env | URL | Infra | BD |
|---|---|---|---|
| LOCAL | `http://localhost:*` | Docker compose | `gm` (MySQL local) |
| INT | `int.geniemag.ci` | À définir | `gm_int` |
| REC | `rec.geniemag.ci` | À définir | `gm_rec` |
| PROD | `geniemag.ci` / `admin.geniemag.ci` | OVH ou AWS eu-west-3 | `gm_prod` |

## 7. Qualité & observabilité

- **Sentry** : erreurs front + back.
- **Grafana Loki** : logs applicatifs centralisés, rétention 90 j.
- **Prometheus + Grafana** : métriques techniques + business (abonnements, paiements, articles publiés).
- **UptimeRobot** : checks synthétiques 1/min sur endpoints critiques.
- **Alerting** : Slack #incidents + SMS d'astreinte pour incidents critiques.

## 8. Décisions d'architecture (ADR)

Les décisions majeures sont documentées dans `docs/adr/NNNN-titre.md`.

| # | Titre | Statut |
|---|---|---|
| 0001 | Stack Laravel + JWT + MySQL | Accepté |
| 0002 | Next.js pour SSR/ISR frontend | Accepté |
| 0003 | Monorepo sans outil (pas de Nx/Turbo en Phase 0) | Accepté |
| 0004 | Design tokens partagés via package local | Accepté |

## 9. Phase 0 — livrables

- [x] Monorepo simplifié (`web/` Laravel, `packages/ui-tokens/`, `infra/`, `docs/`)
- [x] docker-compose local (MySQL, Redis, MinIO, Mailpit, MeiliSearch)
- [x] Design tokens extraits du template HTML
- [x] Laravel 11 scaffoldé + Breeze (Blade) + Livewire 3 + Spatie Permissions + Scout
- [x] Tailwind CSS 3 configuré avec les tokens GM (rouge crimson, Zilla Slab, Mulish, JetBrains Mono)
- [x] Template découpé en layouts Blade (`components/layouts/public`, `components/layouts/admin`) + composants `<x-gm.*>` (top-strip, header, footer, section-heading, article-card)
- [x] Routing dual : routes publiques (`/`, `/rubriques/*`, `/articles/{slug}`, `/magazine`, `/abonnement`) + routes backoffice (`/admin/*`) avec middleware `auth`
- [x] Controllers stubs : `HomeController`, `CategoryController`, `ArticleController`, `MagazineController`, `SubscribeController`, `Admin\DashboardController`
- [x] CI GitHub Actions (lint Pint + build Vite + migrations + tests)
- [x] Smoke test : toutes les routes publiques répondent 200, `/admin` redirige vers `/login` (302) — serveur Laravel lancé en local sans erreur
