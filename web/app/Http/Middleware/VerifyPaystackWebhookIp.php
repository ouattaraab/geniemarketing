<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Defense-in-depth pour le webhook Paystack : vérifie que l'IP appelante
 * figure dans la whitelist `services.paystack.webhook_allowed_ips`.
 *
 * Si la whitelist est vide (dev ou config non initialisée), le middleware
 * laisse passer — la signature HMAC SHA512 reste la défense primaire.
 *
 * Paystack publie la liste des IPs sortantes ici :
 *   https://paystack.com/docs/payments/webhooks/#ip-whitelisting
 */
class VerifyPaystackWebhookIp
{
    public function handle(Request $request, Closure $next): Response
    {
        $allowed = config('services.paystack.webhook_allowed_ips', []);
        if (! is_array($allowed) || $allowed === []) {
            return $next($request);
        }

        $ip = (string) $request->ip();
        if (! in_array($ip, $allowed, true)) {
            Log::warning('Paystack webhook: IP non autorisée', [
                'ip' => $ip,
                'ua' => $request->userAgent(),
            ]);

            return response()->json(['ok' => false, 'error' => 'forbidden'], 403);
        }

        return $next($request);
    }
}
