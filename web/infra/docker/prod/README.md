# Déploiement production

## Composition

- **app** : image PHP-FPM 8.2 Alpine avec OPcache/JIT + supervisor (php-fpm + 2 workers queue + scheduler)
- **nginx** : 1.27 Alpine, TLS, headers sécurité, cache immuable sur `/build/*`, gzip
- **mysql** : 8.4 avec healthcheck et volume persistant
- **redis** : 7 Alpine avec AOF + politique LRU 512 Mo

## Prérequis serveur

- Docker Engine 24+ et Docker Compose V2
- Certificats TLS dans `infra/docker/certs/` : `fullchain.pem` et `privkey.pem`
  (ou placer un reverse-proxy Caddy/Traefik en amont et supprimer le block HTTPS du nginx.conf)
- Fichier `.env.prod` dans `infra/docker/` (copier depuis `web/.env.example` puis ajuster)

## Variables d'environnement critiques (.env.prod)

```bash
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:...
APP_URL=https://geniemag.ci
APP_TIMEZONE=Africa/Abidjan

DB_CONNECTION=mysql
DB_HOST=mysql
DB_DATABASE=gm
DB_USERNAME=gm
DB_PASSWORD=<secret>
DB_ROOT_PASSWORD=<secret>

REDIS_HOST=redis
QUEUE_CONNECTION=redis
CACHE_STORE=redis
SESSION_DRIVER=redis

MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=<sg-token>
MAIL_FROM_ADDRESS=no-reply@geniemag.ci

FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
AWS_BUCKET=gm-media
AWS_DEFAULT_REGION=eu-west-3

PAYSTACK_PUBLIC_KEY=pk_live_...
PAYSTACK_SECRET_KEY=sk_live_...

SENTRY_LARAVEL_DSN=https://...ingest.sentry.io/...
```

## Déploiement initial

```bash
# 1. Récupérer la dernière image publiée
docker compose -f docker-compose.prod.yml pull

# 2. Démarrer la stack
docker compose -f docker-compose.prod.yml up -d

# 3. Préparer Laravel (une seule fois)
docker compose -f docker-compose.prod.yml exec app php artisan storage:link
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force --seed
docker compose -f docker-compose.prod.yml exec app php artisan db:seed --class=SubscriptionPlanSeeder --force
docker compose -f docker-compose.prod.yml exec app php artisan db:seed --class=NewsletterSeeder --force

# 4. Optimisations Laravel
docker compose -f docker-compose.prod.yml exec app php artisan config:cache
docker compose -f docker-compose.prod.yml exec app php artisan route:cache
docker compose -f docker-compose.prod.yml exec app php artisan view:cache
docker compose -f docker-compose.prod.yml exec app php artisan event:cache
```

## Déploiement d'une nouvelle version

```bash
# Le workflow GitHub Actions pousse l'image sur tag/release.
docker compose -f docker-compose.prod.yml pull app
docker compose -f docker-compose.prod.yml up -d --no-deps app

# Migrations éventuelles
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force
docker compose -f docker-compose.prod.yml exec app php artisan config:cache
docker compose -f docker-compose.prod.yml exec app php artisan route:cache
docker compose -f docker-compose.prod.yml exec app php artisan view:cache
```

## Sauvegardes

```bash
# MySQL (quotidien via cron)
docker compose -f docker-compose.prod.yml exec mysql sh -c \
  'exec mysqldump -u root -p"$MYSQL_ROOT_PASSWORD" --single-transaction gm' \
  > backups/gm-$(date +%Y%m%d-%H%M).sql

# storage (médias locaux si pas S3)
docker compose -f docker-compose.prod.yml exec app tar -cz -C /var/www/html/storage . \
  > backups/storage-$(date +%Y%m%d).tar.gz
```

## Healthchecks & supervision

- `GET /up` : endpoint Laravel santé (retourne 200 si app bootée)
- `docker compose logs -f app` : logs php-fpm + queue + scheduler via supervisord
- Monitoring recommandé : UptimeRobot sur `/up`, Sentry via `SENTRY_LARAVEL_DSN`
