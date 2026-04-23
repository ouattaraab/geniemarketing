# Déploiement sur Hostinger (hébergement mutualisé)

Ce guide cible un plan **Hostinger Mutualisé** (Premium, Business ou Cloud Startup). L'application est adaptée aux contraintes de cet hébergement : pas de Redis, pas de Docker, pas de Supervisor, queue/cache/session en base de données, recherche Scout en mémoire.

> Plan recommandé : **Business** minimum (pour avoir SSH + cron illimité). Sur **Premium** le déploiement reste possible via File Manager mais sans SSH les migrations doivent passer par un script d'init PHP.

---

## 1. Pré-requis sur votre poste

- PHP 8.3+ et Composer (pour bâtir le `vendor/`).
- Node 22+ et npm (pour bâtir les assets Vite).
- Un client FTP/SFTP (ou hPanel File Manager).

## 2. Provisioning côté hPanel

1. **Base MySQL** : hPanel → *Databases* → *MySQL Databases*
   - Nom de la base : `uXXXXXXXXX_geniemag`
   - Utilisateur : `uXXXXXXXXX_geniemag`, mot de passe fort 32+ caractères
   - Privilèges : tous

2. **Email** : hPanel → *Emails* → *Email Accounts*
   - Créer `no-reply@geniemag.ci` (pour les envois transactionnels + newsletter)
   - Noter le mot de passe

3. **Domaine + SSL** : hPanel → *Domains*
   - Pointer le domaine `geniemag.ci`
   - Activer **SSL Let's Encrypt** + *Force HTTPS*

4. **Version PHP** : hPanel → *Advanced* → *PHP Configuration*
   - Sélectionner **PHP 8.3** (ou 8.4 si disponible)
   - Extensions à activer : `mbstring`, `xml`, `curl`, `mysqli`, `pdo_mysql`, `gd`, `zip`, `intl`, `bcmath`, `fileinfo`, `openssl`, `tokenizer`
   - Upload max : 20 Mo minimum (pour le PDF magazines — 40 Mo idéal)

## 3. Construire l'archive de déploiement

Depuis votre poste, à la racine du repo :

```bash
scripts/build-for-hostinger.sh
```

Ce script produit deux archives dans `dist/hostinger/` :

| Archive | Destination sur le serveur |
|---|---|
| `geniemag-app.zip` | `/home/uXXXXXXXXX/geniemag-app/` (au-dessus de `public_html/`) |
| `public_html.zip` | `/home/uXXXXXXXXX/domains/geniemag.ci/public_html/` |

Il embarque : vendor composer (no-dev), assets Vite buildés, `public_html/index.php` qui chaîne vers `../geniemag-app`, un `.htaccess` durci.

## 4. Upload via hPanel File Manager

1. hPanel → *File Manager*.

2. Remonter au niveau `/home/uXXXXXXXXX/` (parent de `domains/`).

3. Uploader **`geniemag-app.zip`** → clic droit → *Extract*.
   Un dossier `geniemag-app/` est créé à côté de `domains/`.

4. Naviguer dans `domains/geniemag.ci/public_html/`.
   Supprimer le `index.html` par défaut de Hostinger.

5. Uploader **`public_html.zip`** → *Extract*.
   `index.php`, `.htaccess`, `build/`, `favicon.png`, etc. sont déployés.

Arborescence finale attendue :

```
/home/uXXXXXXXXX/
├── domains/
│   └── geniemag.ci/
│       └── public_html/
│           ├── index.php          ← chaîne vers ../geniemag-app
│           ├── .htaccess
│           ├── build/             ← assets Vite
│           ├── favicon.png
│           └── robots.txt
└── geniemag-app/
    ├── app/ bootstrap/ config/ database/ resources/ routes/ storage/
    ├── vendor/ artisan
    └── .env.example                ← à copier en .env
```

## 5. Configuration `.env`

### Avec SSH (plan Business+)

```bash
ssh uXXXXXXXXX@geniemag.ci -p 65002   # port SSH Hostinger
cd ~/geniemag-app
cp .env.example .env
nano .env
# Remplir :
#   - DB_PASSWORD, MAIL_PASSWORD (mots de passe forts)
#   - APP_KEY (laisser vide → key:generate juste après)
#   - WAVE_API_KEY (wave_ci_prod_… depuis business.wave.com/dev-portal)
#   - WAVE_WEBHOOK_SECRET (affiché UNE SEULE FOIS à la création du webhook)
#   - TRUSTED_PROXIES (voir §9 — Cloudflare CIDRs recommandé)
php artisan key:generate
php artisan migrate --force
# Seed complet : rôles, plans, moyens de paiement (Wave actif), newsletters, settings légaux
php artisan db:seed --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### Sans SSH (plan Premium)

1. hPanel → *File Manager* → éditer `geniemag-app/.env.example`, renommer en `.env`, remplir les valeurs (voir bloc SSH ci-dessus).
2. `APP_KEY` : générer en local avec `cd web && php artisan key:generate --show`, copier la clé dans `.env`.
3. Migrations : hPanel → *Databases* → *phpMyAdmin* → importer `database/migrations.sql` généré via
   ```bash
   cd web && php artisan schema:dump --prune
   ```
4. Sinon, créer un script `public_html/_install.php` (à supprimer après usage) qui fait :
   ```php
   <?php
   define('APP_BASE_PATH', __DIR__.'/../geniemag-app');
   require APP_BASE_PATH.'/vendor/autoload.php';
   $app = require APP_BASE_PATH.'/bootstrap/app.php';
   $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
   $kernel->call('migrate --force');
   $kernel->call('db:seed --force'); // seed complet (rôles, plans, payment_methods, settings légaux)
   $kernel->call('storage:link');
   echo "OK";
   ```
   **À supprimer immédiatement après l'appel.**

## 6. Cron jobs

hPanel → *Advanced* → *Cron Jobs* — ajouter deux entrées :

```
# Scheduler Laravel (abonnements expirés, rappels renouvellement, prune audit…)
* * * * * cd /home/uXXXXXXXXX/geniemag-app && /usr/bin/php artisan schedule:run >> /dev/null 2>&1

# Queue worker (mails newsletter, SubscriptionConfirmed, InvoiceGenerated…)
*/5 * * * * cd /home/uXXXXXXXXX/geniemag-app && /usr/bin/php artisan queue:work --stop-when-empty --max-time=270 --tries=3 >> /dev/null 2>&1
```

> Le chemin `/usr/bin/php` peut varier selon la version PHP choisie. Vérifier avec `which php` en SSH.

## 7. Webhook Wave Business

Dashboard Wave → <https://business.wave.com/dev-portal> → *Webhooks* :

- URL : `https://geniemag.ci/webhooks/wave`
- Événements : `checkout.session.completed`, `checkout.session.payment_failed`
- Wave affiche le **webhook secret une seule fois** (format `whsec_…`) — le coller immédiatement dans `.env` → `WAVE_WEBHOOK_SECRET` puis `php artisan config:cache`.
- Tester avec un paiement de 100 XOF depuis un téléphone Wave réel — doit marquer l'`Order` en `paid`, créer la `Subscription`, émettre l'`Invoice`, envoyer le mail `SubscriptionConfirmed`.

> **Paystack** est conservé comme fallback activable via `/admin/moyens-paiement`. Si activé, son webhook pointe sur `/webhooks/paystack` (URL à configurer dans le dashboard Paystack).

## 7 bis. Configurer les mentions légales

Avant ouverture publique, dans `/admin/parametres` → groupe **« Mentions légales & juridique »** :

- `legal.editor`, `legal.editor_form`, `legal.editor_capital`, `legal.editor_rccm`, `legal.editor_nif`, `legal.editor_cc` : identité de la société éditrice (telle qu'immatriculée au RCCM).
- `legal.director` : directeur de la publication nommément désigné (loi 2017-867).
- `legal.dpo_email` : Délégué à la Protection des Données (obligatoire RGPD / loi 2013-450).
- `legal.host_*` : infos hébergeur (préremplies à Hostinger International Ltd.).
- `legal.terms_updated_at`, `legal.privacy_updated_at`, `legal.mentions_updated_at`, `legal.cookies_updated_at` : dates affichées en pied de chaque page légale.

Ces settings sont lus par `LegalController` pour rendre `/cgu`, `/confidentialite`, `/mentions-legales`, `/cookies`. Les tests `LegalPagesTest` vérifient que les valeurs apparaissent bien — ne pas laisser de `—`.

## 8. Déploiements suivants

### Via `build-for-hostinger.sh` + File Manager

```bash
git pull origin main
scripts/build-for-hostinger.sh
# Uploader geniemag-app.zip → extract (écrase les fichiers existants)
# Uploader public_html.zip → extract
ssh uXXXXXXXXX@geniemag.ci -p 65002
cd geniemag-app
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
```

### Via Git deployment (hPanel)

hPanel → *Websites* → *Git* : connecter votre repo GitHub. hPanel fait un `git pull` à chaque push sur `main`, mais **ne lance pas composer/npm ni migrate**. Un script post-deploy SSH reste nécessaire.

## 9. Checklist pré-go-live

### Obligatoire

- [ ] `APP_ENV=production`, `APP_DEBUG=false`.
- [ ] `APP_KEY` régénéré, distinct du dev.
- [ ] `WAVE_API_KEY` = `wave_ci_prod_*` (jamais `wave_test_placeholder`).
- [ ] `WAVE_WEBHOOK_SECRET` = `whsec_*` collé depuis le dashboard Wave à la création du webhook.
- [ ] `TRUSTED_PROXIES` explicite (pas `*` en prod). Si Cloudflare devant : [liste officielle des CIDRs](https://www.cloudflare.com/ips-v4/). Sans Cloudflare : laisser vide accepte l'IP observée — les preuves de consentement RGPD auront alors leur pleine valeur probatoire.
- [ ] HTTPS forcé (hPanel + `.htaccess`). HSTS auto-délivré par SecurityHeaders.
- [ ] `SENTRY_LARAVEL_DSN` configuré — les `Log::critical` (mismatch paiement) doivent remonter.
- [ ] Mot de passe du super admin `admin@geniemag.ci` changé (pas `ChangeMe!2026`).
- [ ] Settings légaux remplis dans `/admin/parametres` → section « Mentions légales » (RCCM, NIF, directeur de publication, etc.).
- [ ] `php artisan gm:pre-launch` → 0 ✗ critique.
- [ ] Test d'envoi mail : un envoi réel depuis le BO.
- [ ] Backups DB réguliers : hPanel → *Files* → *Backups* + export manuel mensuel.

### Recommandé

- [ ] Tests : `vendor/bin/pest` → 105/105 verts avant upload.
- [ ] `composer audit && npm audit` : aucun High/Critical.
- [ ] Second compte `sup` avec 2FA active (ne pas dépendre d'un seul `admin@`).
- [ ] Premiers articles, rubriques et numéros créés via le BO.
- [ ] Test de bout-en-bout : inscription → paiement réel Wave (100 XOF) → accès abonné → facture PDF téléchargée.
- [ ] Smoke test RGPD : `/compte/mes-donnees/export` → JSON complet, `/profile` → suppression de compte anonymise bien.
- [ ] Retention audit logs ≥ 6 mois — prune auto via `php artisan gm:audit:prune`.

## 10. Rollback

**Sans SSH** : conserver les 3 derniers `geniemag-app.zip` localement. En cas de souci, renommer `geniemag-app/` en `geniemag-app-broken/`, upload + extract la version précédente, rebasculer le `.env` (qui n'est pas dans le zip).

**Avec SSH** : garder les anciennes versions sur le serveur :

```bash
cd ~
mv geniemag-app geniemag-app-$(date +%Y%m%d-%H%M%S)
# Upload + extract nouvelle version
```

Puis `cd geniemag-app && php artisan config:cache route:cache view:cache && php artisan queue:restart`.

## 11. Limites de l'hébergement mutualisé

| Feature | Statut mutualisé | Contournement |
|---|---|---|
| Queue temps réel | ⚠ Latence 5 min (cron) | Upgrade VPS si nécessaire |
| Meilisearch | ❌ Scout `collection` | OK jusqu'à ~1000 articles |
| Webhook volume | ⚠ OK mais throttle 60/min | OK pour Wave (trafic prévisible) |
| MinIO / S3 | ❌ Stockage local `storage/app/public` | Backup S3 externe manuel |
| Supervisor | ❌ Cron seulement | Accepter latence queue |
| Déploiement atomique | ❌ Upload remplace fichiers | Accepter downtime < 5 s |

Si le volume atteint 10 000+ articles ou plusieurs centaines de paiements/jour, migrer sur un **VPS Cloud Hostinger** devient pertinent (configs Nginx/Supervisor archivées dans l'historique git).

## 12. Dépannage

| Symptôme | Cause probable | Fix |
|---|---|---|
| 500 à l'accueil | `APP_KEY` vide ou `.env` absent | Regénérer + redéployer |
| Page blanche après login | Cache de route obsolète | `php artisan route:cache` |
| 403 sur `/admin/*` | Rôle BO non assigné | `php artisan db:seed RoleSeeder` |
| 419 PAGE EXPIRED | `SESSION_DOMAIN` mal configuré | Mettre `.geniemag.ci` |
| Wave `initialize failed (HTTP 401)` | clé test en prod ou clé expirée | Régénérer dans dashboard Wave |
| Wave webhook `signature HMAC invalide` | `WAVE_WEBHOOK_SECRET` désynchronisé après rotation | Rotation du secret dans dashboard Wave + cache:clear |
| Wave webhook `timestamp hors tolérance` | Horloge serveur décalée > 5 min | Vérifier NTP Hostinger, ticket support si dérive persistante |
| Emails non reçus | SMTP bloqué | Vérifier SPF/DKIM hPanel → Emails |
| Images uploadées absentes | `storage:link` pas exécuté | `php artisan storage:link` |
| Queue en retard | Cron `queue:work` pas actif | Vérifier hPanel → Cron Jobs |

Logs applicatifs : `geniemag-app/storage/logs/laravel-*.log` (consultables via File Manager ou `tail -f` en SSH).
