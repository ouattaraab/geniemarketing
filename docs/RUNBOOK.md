# Runbook opérations — GÉNIE MARKETING Mag

Procédures opérationnelles de la plateforme en production.
Audience : équipe technique (DevOps, lead tech, astreinte).

## 0. Convention de nommage

Dans toute la suite :
- `COMPOSE` = `docker compose -f /opt/gm/infra/docker/docker-compose.prod.yml`
- `APP_EXEC` = `$COMPOSE exec app`
- Répertoire projet : `/opt/gm`
- Backups : `/opt/gm/backups/`

---

## 1. Déploiement d'une nouvelle version

### 1.1 Via tag (flux recommandé)

```bash
# 1. Sur le poste du dev
git tag -a v1.2.3 -m "Release 1.2.3"
git push --tags
# → GitHub Actions build + push ghcr.io/geniemag/gm-web:1.2.3
```

### 1.2 Sur le serveur

```bash
# Pull de la nouvelle image
$COMPOSE pull app

# Rollout (zero-downtime acceptable : php-fpm graceful)
$COMPOSE up -d --no-deps app

# Migrations
$APP_EXEC php artisan migrate --force

# Invalider les caches
$APP_EXEC php artisan config:cache
$APP_EXEC php artisan route:cache
$APP_EXEC php artisan view:cache
$APP_EXEC php artisan event:cache

# Vérifier santé
curl -f https://geniemag.ci/up && echo "OK"
```

---

## 2. Rollback

```bash
# 1. Identifier la version précédente
$COMPOSE images app
# ex: ghcr.io/geniemag/gm-web:1.2.2

# 2. Rouler avec cette image
APP_IMAGE=ghcr.io/geniemag/gm-web:1.2.2 $COMPOSE up -d --no-deps app

# 3. Si la migration inclut une modification de schéma incompatible :
$APP_EXEC php artisan migrate:rollback --step=1
```

**Règle d'or** : une migration destructive (suppression de colonne) doit toujours être déployée en 2 versions
— une qui arrête d'écrire sur la colonne, une qui la supprime — pour permettre un rollback sans perte.

---

## 3. Backups

### 3.1 MySQL (quotidien à 02:00 UTC)

```cron
0 2 * * * cd /opt/gm && $COMPOSE exec -T mysql sh -c \
  'mysqldump -u root -p"$MYSQL_ROOT_PASSWORD" --single-transaction --routines gm' \
  | gzip > /opt/gm/backups/mysql-$(date +\%Y\%m\%d).sql.gz
```

Rétention : 30 jours locaux + archivage mensuel vers S3 Glacier (1 an).

### 3.2 Storage (médias locaux si `FILESYSTEM_DISK=local`)

```bash
# Si on est sur S3, rien à faire : versioning activé côté bucket.
# En local :
$COMPOSE exec app tar -cz -C /var/www/html/storage/app . > \
  /opt/gm/backups/storage-$(date +%Y%m%d).tar.gz
```

### 3.3 Restauration

```bash
# MySQL
gunzip < /opt/gm/backups/mysql-20260422.sql.gz | \
  $COMPOSE exec -T mysql mysql -u root -p"$MYSQL_ROOT_PASSWORD" gm

# Storage
$COMPOSE exec -T app tar -xz -C /var/www/html/storage/app < \
  /opt/gm/backups/storage-20260422.tar.gz
```

---

## 4. Incidents courants

### 4.1 Paiement : webhook Paystack non reçu

**Symptôme** : commande `paid` côté Paystack dashboard mais status `pending` chez nous.

```bash
# Vérifier les logs webhook
$APP_EXEC grep "webhooks/paystack" storage/logs/laravel.log | tail -20

# Resynchroniser manuellement : forcer la vérification
$APP_EXEC php artisan tinker --execute="
  \$order = \App\Models\Order::where('reference', 'GM-2026-XXXXXX')->first();
  \$gw = app(\App\Contracts\PaymentGateway::class);
  \$result = \$gw->verify(\$order->reference);
  if (\$result->status->value === 'success') {
    app(\App\Services\Commerce\CheckoutService::class)->finalizeOrder(\$order, \$result->raw);
    echo 'Resynchronisé' . PHP_EOL;
  }
"
```

**Root cause possible** : URL webhook mal configurée dans le dashboard Paystack, ou firewall IP côté serveur.
Paystack envoie depuis 52.31.139.75, 52.49.173.169, 52.214.14.220 — whitelister si besoin.

### 4.2 Queue bloquée (worker crashé)

**Symptôme** : emails de confirmation non envoyés, campagne en status `sending` indéfini.

```bash
# État de la file
$APP_EXEC php artisan queue:monitor default --max=50

# Jobs échoués
$APP_EXEC php artisan queue:failed

# Redémarrer les workers
$APP_EXEC supervisorctl restart queue-worker:*

# Rejouer les jobs failed
$APP_EXEC php artisan queue:retry all
```

### 4.3 Disk space saturé (uploads trop lourds)

```bash
# Identifier les fichiers
$APP_EXEC du -sh /var/www/html/storage/app/* | sort -rh | head
$APP_EXEC du -sh /var/www/html/storage/logs/*

# Rotation logs (s'il n'y a pas de logrotate)
$APP_EXEC sh -c 'echo "" > /var/www/html/storage/logs/laravel.log'
```

### 4.4 DB : deadlock ou slow query

```bash
# Processus actifs
$COMPOSE exec mysql mysql -u root -p"$MYSQL_ROOT_PASSWORD" -e "SHOW PROCESSLIST;"

# Slow log
$COMPOSE exec mysql tail -f /var/lib/mysql/slow.log
```

---

## 5. Maintenance planifiée

```bash
# Active le mode maintenance avec secret d'accès pour les admins
$APP_EXEC php artisan down --secret=admin-bypass-token --render=errors::503

# Pendant la fenêtre, les admins peuvent bypasser via https://geniemag.ci/admin-bypass-token
# Puis :
$APP_EXEC php artisan up
```

Communiquer **48h avant** minimum à l'équipe éditoriale et via newsletter si > 30 min d'indispo.

---

## 6. Monitoring & alertes

| Métrique | Seuil | Action |
|---|---|---|
| Disponibilité `/up` | < 99.5% / mois | Investigation Sentry + logs |
| Queue jobs en échec | > 50 / heure | Vérifier worker + SMTP |
| Taux d'erreur HTTP 5xx | > 1% | Rollback si régression récente |
| Abonnements expirés non renouvelés | > 10% / semaine | Vérifier renouvellement auto Paystack |
| Commandes `pending` > 24 h | > 5 | Investigation webhook Paystack |

Outils :
- **Sentry** (DSN dans `.env.prod`) : erreurs PHP + JS
- **UptimeRobot** : check HTTPS `/up` toutes les 1 min
- **Logs** : `$COMPOSE logs -f --tail=100 app`
- **Grafana + Prometheus** (V2) : métriques business

---

## 7. Sécurité — incident

En cas de suspicion de compromission :

1. **Isoler** : `$COMPOSE stop app` (nginx reste UP pour afficher une page statique).
2. **Préserver les logs** : `cp -r /opt/gm/logs /opt/gm/incidents/$(date +%Y%m%d-%H%M)`.
3. **Rotation secrets** : générer nouveau `APP_KEY`, invalider toutes les sessions + tokens Paystack.
4. **Notifier** : ARTCI (< 72 h, obligation Loi 2013-450 CI), utilisateurs impactés, DPO.
5. **Audit** : vérifier `/admin/audit` pour actions suspectes sur les 30 derniers jours.
6. **Post-mortem** : documenter dans `docs/incidents/YYYY-MM-DD-titre.md`.

---

## 8. Contacts (à remplir au kick-off)

| Rôle | Nom | Contact | Astreinte |
|---|---|---|---|
| Lead tech | — | — | 24/7 |
| DevOps | — | — | heures ouvrées |
| DPO | — | `dpo@geniemag.ci` | — |
| Support Paystack | — | `support@paystack.com` | — |
| Hébergeur | — | — | — |
