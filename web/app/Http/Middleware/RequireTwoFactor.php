<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Force la 2FA pour les rôles critiques (ADM, SUP) et propose la vérification
 * en cours de session (cookie session "2fa_verified" positionné après succès).
 *
 * - Si rôle sensible sans 2FA activée → redirection /2fa/setup.
 * - Si 2FA activée mais pas encore validée dans la session → redirection /2fa/challenge.
 */
class RequireTwoFactor
{
    private const MANDATORY_ROLES = ['adm', 'sup'];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user === null) {
            return $next($request);
        }

        $sensitive = $user->hasAnyRole(self::MANDATORY_ROLES);

        if ($sensitive && ! $user->{'2fa_enabled'}) {
            return redirect()->route('2fa.setup')->with(
                'status',
                'Votre rôle exige l\'activation de la double authentification avant d\'accéder au backoffice.',
            );
        }

        if ($user->{'2fa_enabled'} && ! $request->session()->get('2fa_verified_at')) {
            return redirect()->route('2fa.challenge');
        }

        return $next($request);
    }
}
