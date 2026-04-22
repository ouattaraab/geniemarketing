#!/usr/bin/env bash
# ==============================================================================
# GÉNIE MARKETING Mag — Build pour Hostinger Mutualisé
# ------------------------------------------------------------------------------
# Produit deux archives prêtes à être uploadées via hPanel → File Manager :
#
#   dist/hostinger/geniemag-app.zip       → à décompresser dans /home/uXXX/geniemag-app/
#   dist/hostinger/public_html.zip        → à décompresser dans /home/uXXX/domains/.../public_html/
#
# Le zip "app" contient app/, bootstrap/, config/, database/, resources/,
# routes/, storage/ (vide), vendor/ (no-dev), artisan, etc.
# Le zip "public_html" contient public/* + index.php adapté + .htaccess.
#
# Usage :
#   scripts/build-for-hostinger.sh
# ==============================================================================

set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
WEB="${ROOT}/web"
DIST="${ROOT}/dist/hostinger"
STAGE="${DIST}/_stage"

log() { printf "\033[1;34m▸ %s\033[0m\n" "$*"; }
ok()  { printf "\033[1;32m✓ %s\033[0m\n" "$*"; }

log "Nettoyage dist/"
rm -rf "${DIST}"
mkdir -p "${DIST}" "${STAGE}/geniemag-app" "${STAGE}/public_html"

# ---- 1. Dépendances PHP (prod-only) ---------------------------------------
log "composer install --no-dev --optimize-autoloader"
cd "${WEB}"
composer install --no-interaction --no-dev --optimize-autoloader --no-progress

# ---- 2. Build Vite --------------------------------------------------------
log "npm ci && npm run build"
npm ci --no-audit --no-fund --silent
npm run build

# ---- 3. Caches Laravel pré-compilés ---------------------------------------
# On NE fait PAS config:cache ici (lit le .env local). L'opérateur le fera
# sur le serveur après upload du vrai .env.

# ---- 4. Copie de l'application (hors public/) -----------------------------
log "Copie de l'application dans stage/geniemag-app"
rsync -a --delete \
    --exclude='node_modules/' \
    --exclude='public/' \
    --exclude='tests/' \
    --exclude='.env' \
    --exclude='.env.backup' \
    --exclude='.env.*.local' \
    --exclude='storage/logs/*' \
    --exclude='storage/framework/cache/*' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/framework/views/*' \
    --exclude='storage/debugbar/' \
    --exclude='database/database.sqlite' \
    --exclude='*.log' \
    --exclude='.DS_Store' \
    "${WEB}/" "${STAGE}/geniemag-app/"

# .gitkeep dans les dossiers storage vidés
mkdir -p "${STAGE}/geniemag-app/storage/logs"
mkdir -p "${STAGE}/geniemag-app/storage/framework/"{cache/data,sessions,views,testing}
mkdir -p "${STAGE}/geniemag-app/storage/app/public"
touch "${STAGE}/geniemag-app/storage/logs/.gitkeep"

# Exemple de .env
cp "${WEB}/.env.production.example" "${STAGE}/geniemag-app/.env.example"

# ---- 5. Assemblage public_html -------------------------------------------
log "Assemblage public_html/"
# IMPORTANT : on exclut `storage` (symlink créé par `artisan storage:link` en
# local → suivi par rsync et embarquerait les médias uploadés). L'opérateur
# relance `php artisan storage:link` sur le serveur après premier upload.
rsync -aL \
    --exclude='hot' \
    --exclude='storage' \
    --exclude='.DS_Store' \
    "${WEB}/public/" "${STAGE}/public_html/"

# Remplace le index.php Laravel par notre shim qui pointe ../geniemag-app
cp "${ROOT}/infra/hostinger/public_html-index.php" "${STAGE}/public_html/index.php"

# .htaccess Hostinger
cp "${ROOT}/infra/hostinger/public_html-htaccess" "${STAGE}/public_html/.htaccess"

# robots.txt minimal si absent
if [ ! -f "${STAGE}/public_html/robots.txt" ]; then
    cat > "${STAGE}/public_html/robots.txt" <<EOF
User-agent: *
Disallow: /admin
Disallow: /compte
Disallow: /paiement
Disallow: /livewire/
Sitemap: https://geniemag.ci/sitemap.xml
EOF
fi

# ---- 6. Création des archives --------------------------------------------
log "Création des zips"
cd "${STAGE}"
(cd geniemag-app && zip -qr "${DIST}/geniemag-app.zip" .)
(cd public_html  && zip -qr "${DIST}/public_html.zip" .)

# ---- 7. Cleanup + résumé --------------------------------------------------
rm -rf "${STAGE}"

cd "${ROOT}"
ok "Build terminé :"
ls -lh "${DIST}"/*.zip

cat <<'NEXT'

────────────────────────────────────────────────────────────────────────────
PROCHAINES ÉTAPES
────────────────────────────────────────────────────────────────────────────

1. hPanel → Databases → créer la base MySQL + utilisateur.

2. hPanel → File Manager :
   - Uploader geniemag-app.zip dans /home/uXXXXXXXXX/
     puis Extract → renommer le dossier en "geniemag-app".
   - Uploader public_html.zip dans domains/geniemag.ci/public_html/
     puis Extract (remplace le index.html par défaut).

3. SSH sur le serveur (ou Terminal hPanel) :
     cp geniemag-app/.env.example geniemag-app/.env
     nano geniemag-app/.env       # renseigner DB, MAIL, PAYSTACK, etc.
     cd geniemag-app
     php artisan key:generate
     php artisan migrate --force
     php artisan db:seed --class=RoleSeeder --force
     php artisan storage:link
     php artisan config:cache
     php artisan route:cache
     php artisan view:cache
     php artisan event:cache

4. hPanel → Advanced → Cron Jobs : ajouter les 2 entrées listées dans
   DEPLOYMENT.md §6.

5. hPanel → Domains → SSL : activer Let's Encrypt + "Force HTTPS".

6. Dashboard Paystack → Webhook : pointer sur
   https://geniemag.ci/webhooks/paystack

────────────────────────────────────────────────────────────────────────────
NEXT
