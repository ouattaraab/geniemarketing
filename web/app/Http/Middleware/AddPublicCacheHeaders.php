<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ajoute un Cache-Control court sur les GET publics sans cookie d'auth,
 * pour permettre au CDN (Cloudflare) d'accélérer les pages accueil/rubriques.
 * Les pages authentifiées restent private par défaut.
 */
class AddPublicCacheHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $isGet = $request->isMethod('GET');
        $isAnonymous = ! $request->user();
        $isHtml = str_starts_with((string) $response->headers->get('Content-Type', 'text/html'), 'text/html');

        if ($isGet && $isAnonymous && $isHtml && $response->isSuccessful()) {
            $response->headers->set('Cache-Control', 'public, max-age=300, s-maxage=900');
            $response->headers->set('Vary', 'Cookie, Accept-Encoding');
        }

        return $response;
    }
}
