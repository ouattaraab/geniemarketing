<?php

/*
|--------------------------------------------------------------------------
| Front Controller — Hostinger Mutualisé
|--------------------------------------------------------------------------
|
| À déposer tel quel dans `public_html/index.php` sur le serveur Hostinger.
|
| Sur un hébergement mutualisé, le code Laravel doit vivre HORS de
| public_html/ pour que app/, config/, .env etc. ne soient pas exposés.
| Arborescence type :
|
|   /home/uXXXXXXXXX/
|     ├── domains/geniemag.ci/public_html/   ← ce fichier + contenu web/public/
|     └── geniemag-app/                       ← le reste du projet Laravel
|         ├── app/ bootstrap/ config/ database/ resources/ routes/
|         ├── storage/ vendor/ artisan .env
|         └── ...
|
| Ajuster la constante APP_BASE_PATH si vous utilisez un autre nom.
|
*/

define('APP_BASE_PATH', __DIR__.'/../geniemag-app');
define('LARAVEL_START', microtime(true));

// Mode maintenance
if (file_exists($maintenance = APP_BASE_PATH.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Autoloader Composer
require APP_BASE_PATH.'/vendor/autoload.php';

// Bootstrap Laravel (override des chemins de base)
$app = require_once APP_BASE_PATH.'/bootstrap/app.php';

$app->usePublicPath(__DIR__);

$app->handleRequest(Illuminate\Http\Request::capture());
