<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Garde le backoffice : l'utilisateur doit être authentifié, de type "backoffice",
 * actif, et posséder au moins un rôle BO (red, chef, edit, com, adm, sup).
 */
class EnsureBackofficeUser
{
    private const BACKOFFICE_ROLES = ['red', 'chef', 'edit', 'com', 'adm', 'sup'];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login');
        }

        if ($user->type !== 'backoffice' || $user->status !== 'active') {
            abort(403, 'Accès au backoffice refusé.');
        }

        if (! $user->hasAnyRole(self::BACKOFFICE_ROLES)) {
            abort(403, 'Votre compte n\'a aucun rôle backoffice assigné.');
        }

        return $next($request);
    }
}
