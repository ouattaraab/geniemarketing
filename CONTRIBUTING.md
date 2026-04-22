# Contribuer à GÉNIE MARKETING Mag

Ce projet est maintenu par l'équipe technique de GÉNIE MARKETING Mag.
Si vous êtes contributeur interne ou prestataire, ce guide vous permet de démarrer rapidement.

## Workflow Git

### Branches

- **`main`** : branche de référence (protection active : PR review + CI green obligatoires)
- **`develop`** : branche d'intégration (optionnelle, pour gros chantiers)
- **`feat/US-XXX-titre-court`** : feature issue du backlog (préfixe US-XXX quand applicable)
- **`fix/description`** : correction de bug
- **`chore/description`** : outillage, docs, refactor non-fonctionnel
- **`hotfix/description`** : correction urgente en prod (branche depuis `main`)

### Commits

Format **Conventional Commits** : `<type>(<scope>): <description concise>`

Types : `feat`, `fix`, `chore`, `docs`, `refactor`, `test`, `perf`, `security`

Exemples :
```
feat(newsletter): ajouter la segmentation par rôle abonné
fix(paystack): gérer le webhook charge.dispute
docs(runbook): section rollback avec exemple
test(article): couvrir la transition scheduled → published
chore(deps): bump laravel/framework 11.x
```

Le commit doit être **auto-explicatif** : pas besoin d'ouvrir le diff pour comprendre.
Préférer plusieurs petits commits thématiques à un gros commit fourre-tout.

### Pull Requests

1. Une PR = une feature / fix / chore cohérent
2. Description : **quoi** + **pourquoi** (référence backlog, issue, incident)
3. Checklist obligatoire dans le template PR :
   - [ ] Tests Pest ajoutés/modifiés et verts (`make test`)
   - [ ] Lint OK (`make lint`)
   - [ ] Migration réversible si applicable
   - [ ] CHANGELOG.md mis à jour
   - [ ] Capture d'écran si changement UI

## Setup local

```bash
git clone git@github.com:ouattaraab/geniemarketing.git gm
cd gm
make up                      # services Docker
cd web && cp .env.example .env && php artisan key:generate
php artisan migrate --seed
npm install && npm run build
make serve                   # http://localhost:8000
```

Compte super admin : `admin@geniemag.ci` / `ChangeMe!2026`

## Conventions de code

### PHP (Laravel)

- **PHP 8.2+** avec `declare(strict_types=1);` dans les nouveaux fichiers
- **PSR-12** via `./vendor/bin/pint` (pre-commit recommandé)
- Structure : Controllers fins → Form Requests (validation) → Services (logique métier) → Models
- **Action over God-classes** : une classe, une responsabilité
- Tests **Pest** obligatoires pour tout service métier et composant Livewire critique

### Nommage

- Classes : `PascalCase`
- Méthodes : `camelCase`
- Tables : `snake_case_plural` (ex. `subscription_plans`)
- Colonnes : `snake_case`
- Routes : `kebab-case-fr` (ex. `/mentions-legales`, `/recherche`)

### Messages utilisateur

- **En français**, avec les accents corrects (pas de "fran�ais" ou "emploi").
- Ton éditorial : sobre, précis, pas d'emojis dans les UI de production.
- Ne jamais exposer les détails techniques à l'utilisateur final (log Sentry, message générique).

### Blade

- Composants `<x-gm.*>` pour les primitives de design (top-strip, header, article-card, ...)
- Layouts dans `components/layouts/` (public, admin)
- Jamais de SQL ou de logique métier en Blade — passer par un Service injecté via controller ou computed Livewire

### Livewire

- Namespace : `App\Livewire\Admin\*` et `App\Livewire\Public\*`
- Properties publiques = état synchronisé → valider chaque input
- Actions côté serveur = method public qui retourne void (pas de rendu custom sauf exception)
- Un composant par responsabilité — ne pas réutiliser un composant admin côté public

### Tests

- Feature test pour chaque user story P0/P1
- Unit test pour les services pures (pas de BD ni HTTP)
- `RefreshDatabase` automatique sur `Feature/*`
- `SCOUT_DRIVER=null` en test pour désactiver l'indexation

### Sécurité

- **Jamais** commiter `.env`, clés privées, tokens Paystack.
- Utiliser `Crypt::encryptString()` pour les données sensibles en BD (exemple : `2fa_secret`).
- CSRF activé partout sauf `api/*` et `webhooks/*`.
- Policies Laravel obligatoires pour les ressources BO (pas de check manuel ad-hoc).
- Content Security Policy stricte via nginx (`Strict-Transport-Security`, `X-Frame-Options`, etc.).

## Checklist avant de publier un article (côté produit)

Pour la rédaction, rappel du workflow :

1. Créer l'article en **brouillon**, rédiger avec le WYSIWYG TipTap
2. Uploader la **cover** (alt obligatoire) et renseigner SEO (meta_title, meta_description)
3. Soumettre à **relecture** → rédacteur en chef reçoit la notification
4. Valider les **métadonnées** : rubrique, catégorie éditoriale, tags (min 2, max 10), niveau d'accès
5. **Publier immédiatement** ou planifier avec date future

L'image de couverture est obligatoire pour publier (US-013). Sans elle, la transition `review → published` est bloquée avec un message d'erreur.

## Support

- **Incident prod** → voir [docs/RUNBOOK.md](docs/RUNBOOK.md)
- **Bug ou feature** → GitHub Issues avec label approprié
- **Sécurité** → email direct au lead tech (pas d'issue publique)

## Licence

Code propriétaire. Toute reproduction, utilisation ou distribution hors de l'organisation GÉNIE MARKETING Mag est soumise à autorisation écrite préalable.
