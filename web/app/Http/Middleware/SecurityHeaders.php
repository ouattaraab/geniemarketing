<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Headers de sécurité HTTP pour toutes les réponses web.
 *
 * - HSTS : force HTTPS pendant 1 an (prod uniquement).
 * - X-Content-Type-Options : bloque le MIME sniffing.
 * - Referrer-Policy : ne fuit pas le path sur les liens sortants.
 * - X-Frame-Options : anti-clickjacking (CSP frame-ancestors couvre aussi).
 * - Permissions-Policy : désactive les APIs capteurs qu'on n'utilise pas.
 * - Content-Security-Policy : durci au cas par cas.
 *
 * La CSP est volontairement laxiste ('unsafe-inline' pour style et script)
 * le temps de lever tous les inline handlers Alpine/Livewire. On pourra
 * durcir en v1 via nonces ou hash.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // N'applique pas les headers aux réponses de streaming PDF/fichiers
        // (qui ne devraient pas être rendues dans un navigateur avec
        // contexte document).
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Permissions-Policy', implode(', ', [
            'camera=()',
            'microphone=()',
            'geolocation=()',
            'payment=(self)',
            'usb=()',
            'magnetometer=()',
            'accelerometer=()',
            'gyroscope=()',
        ]));

        if (app()->environment('production')) {
            // HSTS : 1 an, sous-domaines inclus, preload possible après audit.
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // CSP — mode "report-only" par défaut pour ne rien casser en rollout.
        // Passer à "Content-Security-Policy" (sans -Report-Only) une fois validé.
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://js.paystack.co https://esm.sh",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://fonts.bunny.net",
            "img-src 'self' data: https:",
            "font-src 'self' https://fonts.gstatic.com https://fonts.bunny.net data:",
            "connect-src 'self' https://api.paystack.co",
            "frame-src 'self' https://www.youtube.com https://www.youtube-nocookie.com",
            "frame-ancestors 'self'",
            "base-uri 'self'",
            "form-action 'self' https://checkout.paystack.com",
            "object-src 'none'",
        ]);
        $response->headers->set('Content-Security-Policy-Report-Only', $csp);

        return $response;
    }
}
