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
# Remplir DB_PASSWORD, MAIL_PASSWORD, PAYSTACK_*, APP_KEY (laisser vide)
php artisan key:generate
php artisan migrate --force
php artisan db:seed --class=Database\\Seeders\\RoleSeeder --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### Sans SSH (plan Premium)

1. hPanel → *File Manager* → éditer `geniemag-app/.env.example`, renommer en `.env`, remplir les valeurs.
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
   $kernel->call('db:seed --class=Database\\Seeders\\RoleSeeder --force');
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

## 7. Webhook Paystack

Dashboard Paystack → *Settings* → *API Keys & Webhooks* :

- URL : `https://geniemag.ci/webhooks/paystack`
- Événements : `charge.success`, `charge.failed`
- Tester avec le bouton *Send Test Webhook* — doit retourner `200 OK`.

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
- [ ] `PAYSTACK_SECRET_KEY` = `sk_live_*` (jamais `sk_test_placeholder`).
- [ ] `PAYSTACK_WEBHOOK_IPS` à jour depuis la [doc Paystack](https://paystack.com/docs/payments/webhooks/#ip-whitelisting).
- [ ] `TRUSTED_PROXIES=*` (obligatoire derrière le LB Hostinger).
- [ ] HTTPS forcé (hPanel + `.htaccess`). HSTS auto-délivré par SecurityHeaders.
- [ ] `SENTRY_LARAVEL_DSN` configuré — les `Log::critical` (mismatch paiement) doivent remonter.
- [ ] Test d'envoi mail : `make test-feature` en local + un envoi réel depuis le BO.
- [ ] Backups DB réguliers : hPanel → *Files* → *Backups* + export manuel mensuel.

### Recommandé

- [ ] Tests : `vendor/bin/pest` → 83/83 verts avant upload.
- [ ] `composer audit && npm audit` : aucun High/Critical.
- [ ] Second compte `sup` avec 2FA active (ne pas dépendre d'un seul `admin@`).
- [ ] Premiers articles, rubriques et numéros créés via le BO.
- [ ] Test de bout-en-bout : inscription → paiement test Paystack → accès abonné.
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
| Webhook volume | ⚠ OK mais throttle 60/min | OK pour Paystack |
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
| Paystack "invalid key" | clé test en prod | Basculer `sk_live_*` |
| Emails non reçus | SMTP bloqué | Vérifier SPF/DKIM hPanel → Emails |
| Images uploadées absentes | `storage:link` pas exécuté | `php artisan storage:link` |
| Queue en retard | Cron `queue:work` pas actif | Vérifier hPanel → Cron Jobs |

Logs applicatifs : `geniemag-app/storage/logs/laravel-*.log` (consultables via File Manager ou `tail -f` en SSH).
