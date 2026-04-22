# GÉNIE MARKETING Mag — Plateforme

Plateforme éditoriale du magazine GÉNIE MARKETING : frontend public (grand public et abonnés) + backoffice de production, **dans un monolithe Laravel** partageant le même design issu du template `template/genie-marketing-v2.html`.

## Structure

```
gm/
├── api/              # Projet Laravel 11 monolithique (à renommer en app/ après pivot)
│                     # · Frontend public SSR (Blade + Livewire)
│                     # · Backoffice (Blade + Livewire sous /admin)
├── packages/
│   └── ui-tokens/    # Design tokens extraits du template (CSS + config)
├── infra/
│   └── docker/       # docker-compose dev (MySQL, Redis, MinIO, Mailpit, MeiliSearch)
├── docs/             # Documentation (architecture, décisions, API)
└── template/         # Template HTML de référence (charte graphique)
```

## Stack technique

| Couche | Technologie |
|---|---|
| Backend + SSR | Laravel 11 (PHP 8.2+) |
| Vues | Blade + Livewire 3 + Alpine.js |
| Auth | Sessions Laravel + Breeze (2FA Fortify en V1) |
| Permissions | `spatie/laravel-permission` |
| CSS | Tailwind CSS + design tokens `@gm/ui-tokens` |
| DB | MySQL 8+ |
| Cache / Queue | Redis 7 |
| Stockage | S3-compatible (MinIO en dev) |
| Recherche | MeiliSearch (`laravel/scout`) |
| Email | SendGrid (transactionnel), Mailjet (marketing) |
| Paiement | CinetPay |
| CDN | Cloudflare |

## Démarrage rapide (après pivot Livewire)

### Prérequis

- PHP 8.2+ · Composer 2+
- Node 20+ · npm 10+ (pour Vite/Tailwind)
- MySQL 8+ (ou Docker)
- Git

### Installation

```bash
# 1. Services Docker
cd infra/docker && cp .env.example .env && docker compose up -d

# 2. Laravel
cd ../../api
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run dev   # Vite + Tailwind
php artisan serve
```

## Environnements

| Env | URL | Usage |
|---|---|---|
| LOCAL | `http://localhost:8000` (public), `/admin` (BO) | Docker + Laravel serve |
| INT | À définir | CI, branches candidates |
| REC | À définir | Recette fonctionnelle |
| PROD | `geniemag.ci`, `admin.geniemag.ci` (ou `/admin`) | Production |

## Documentation

- [Backlog produit](Backlog_Genie_Marketing_Mag.md)
- [Architecture](docs/ARCHITECTURE.md)
- [Design tokens](packages/ui-tokens/README.md)

## Charte graphique

Couleur primaire : **rouge crimson `#B40F1E`** · Typographies : **Zilla Slab** (titres italiques) + **Mulish** (corps) + **JetBrains Mono** (labels).
