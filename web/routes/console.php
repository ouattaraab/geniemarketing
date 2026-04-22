<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Tâches planifiées GÉNIE MARKETING Mag
|--------------------------------------------------------------------------
| Exécutées par le scheduler — via supervisord en production ou le cron
| système pointant sur `php artisan schedule:run`.
*/

// Bascule en `expired` les abonnements arrivés à échéance — toutes les heures.
Schedule::command('gm:subscriptions:expire')
    ->hourly()
    ->withoutOverlapping()
    ->onOneServer()
    ->runInBackground();

// Rappels de renouvellement J-7 — tous les jours à 08h00 (heure Abidjan).
Schedule::command('gm:subscriptions:remind-renewal --days=7')
    ->dailyAt('08:00')
    ->timezone('Africa/Abidjan')
    ->withoutOverlapping()
    ->onOneServer();

// Purge journal d'audit > 12 mois — tous les 1er du mois à 03h00.
Schedule::command('gm:audit:prune --months=12')
    ->monthlyOn(1, '03:00')
    ->timezone('Africa/Abidjan')
    ->onOneServer();

// Nettoyage des uploads Livewire temporaires (> 24 h) — quotidien.
Schedule::command('livewire:configure-s3-upload-cleanup')
    ->daily()
    ->onOneServer();
