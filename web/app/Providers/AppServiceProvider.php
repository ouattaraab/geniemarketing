<?php

declare(strict_types=1);

namespace App\Providers;

use App\Listeners\AuditAuthEvents;
use App\Services\FreemiumCounter;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(FreemiumCounter::class, function ($app): FreemiumCounter {
            return new FreemiumCounter(
                cache: $app->make(Repository::class),
                monthlyLimit: (int) config('gm.freemium_monthly_limit', 3),
            );
        });
    }

    public function boot(): void
    {
        Event::subscribe(AuditAuthEvents::class);

        // Force HTTPS en production — nécessaire derrière un proxy terminé en
        // TLS (Cloudflare/Nginx) pour que les URLs générées par route() /
        // url() utilisent bien `https://`, et que les cookies secure tiennent.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
