<?php

declare(strict_types=1);

use App\Http\Middleware\AddPublicCacheHeaders;
use App\Http\Middleware\EnsureBackofficeUser;
use App\Http\Middleware\RequireTwoFactor;
use App\Http\Middleware\VerifyPaystackWebhookIp;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Spatie\Permission\Middleware\RoleMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'api',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Derrière Cloudflare / un reverse proxy, on doit lire X-Forwarded-For
        // pour obtenir la vraie IP cliente. Sans ça, toute défense IP-based
        // (whitelist webhook Paystack, throttles, freemium counter, audit log)
        // s'effondre. TRUSTED_PROXIES peut être '*' (si edge maîtrisé) ou une
        // liste explicite d'IPs.
        $middleware->trustProxies(
            at: env('TRUSTED_PROXIES') ? explode(',', (string) env('TRUSTED_PROXIES')) : null,
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO,
        );

        $middleware->validateCsrfTokens(except: [
            'api/*',
            'webhooks/*',
        ]);

        $middleware->alias([
            'backoffice' => EnsureBackofficeUser::class,
            '2fa' => RequireTwoFactor::class,
            'paystack.ip' => VerifyPaystackWebhookIp::class,
            'role' => RoleMiddleware::class,
        ]);

        // Cache HTTP court sur les GET publics anonymes (CDN-friendly).
        $middleware->appendToGroup('web', AddPublicCacheHeaders::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
