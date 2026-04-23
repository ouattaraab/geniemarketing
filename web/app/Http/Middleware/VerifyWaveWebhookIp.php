<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Defense-in-depth pour le webhook Wave : restreint l'accès aux IPs listées
 * dans `services.wave.webhook_allowed_ips`.
 *
 * Wave ne publie pas officiellement une liste d'IPs sortantes stable à ce
 * jour (avril 2026) — la whitelist reste donc VIDE par défaut et la
 * signature HMAC SHA-256 sur `Wave-Signature` est la défense primaire.
 * Activez-la (WAVE_WEBHOOK_IPS=...) si votre contact Wave vous fournit la
 * plage d'IPs de production.
 */
class VerifyWaveWebhookIp
{
    public function handle(Request $request, Closure $next): Response
    {
        $allowed = config('services.wave.webhook_allowed_ips', []);
        if (! is_array($allowed) || $allowed === []) {
            return $next($request);
        }

        $ip = (string) $request->ip();
        if (! in_array($ip, $allowed, true)) {
            Log::warning('Wave webhook: IP non autorisée', [
                'ip' => $ip,
                'ua' => $request->userAgent(),
            ]);

            return response()->json(['ok' => false, 'error' => 'forbidden'], 403);
        }

        return $next($request);
    }
}
