<?php

declare(strict_types=1);

use App\Http\Middleware\AddPublicCacheHeaders;
use App\Http\Middleware\EnsureBackofficeUser;
use App\Http\Middleware\RequireTwoFactor;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'api',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'api/*',
            'webhooks/*',
        ]);

        $middleware->alias([
            'backoffice' => EnsureBackofficeUser::class,
            '2fa' => RequireTwoFactor::class,
        ]);

        // Cache HTTP court sur les GET publics anonymes (CDN-friendly).
        $middleware->appendToGroup('web', AddPublicCacheHeaders::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
