<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Audit;
use App\Services\TwoFactorAuth;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\ValidationException;

class TwoFactorController extends Controller
{
    public function __construct(
        private readonly TwoFactorAuth $service,
        private readonly Audit $audit,
    ) {}

    /**
     * GET /2fa/setup — génère le secret si besoin et affiche le QR + codes de récup.
     */
    public function setup(Request $request): View
    {
        $user = $request->user();
        abort_unless($user !== null, 401);

        if (! $user->{'2fa_secret'}) {
            $secret = $this->service->generateSecret($user);
            $user->refresh();
        } else {
            $secret = Crypt::decryptString($user->{'2fa_secret'});
        }

        return view('auth.2fa.setup', [
            'secret' => $secret,
            'qrSvg' => $this->service->qrCodeSvg($user, $secret),
            'alreadyEnabled' => (bool) $user->{'2fa_enabled'},
            'recoveryCodes' => $user->{'2fa_enabled'} ? $this->service->recoveryCodes($user) : [],
        ]);
    }

    /**
     * POST /2fa/setup — confirmer l'activation avec un code TOTP.
     */
    public function enable(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 401);

        $validated = $request->validate([
            'code' => ['required', 'string', 'regex:/^[0-9]{6}$/'],
        ]);

        if (! $this->service->enable($user, $validated['code'])) {
            throw ValidationException::withMessages([
                'code' => 'Code invalide. Vérifiez que votre téléphone est bien à l\'heure.',
            ]);
        }

        $request->session()->put('2fa_verified_at', now()->toIso8601String());
        $this->audit->log('auth.2fa.enabled', $user);

        return redirect()->route('admin.dashboard')->with(
            'status',
            'Double authentification activée. Conservez précieusement vos codes de récupération.',
        );
    }

    /**
     * GET /2fa/challenge — saisie du code à chaque session.
     */
    public function challenge(): View
    {
        return view('auth.2fa.challenge');
    }

    /**
     * POST /2fa/challenge — vérification à chaque connexion.
     */
    public function verify(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 401);

        $validated = $request->validate([
            'code' => ['required', 'string'],
        ]);

        if (! $this->service->verify($user, $validated['code'])) {
            $this->audit->log('auth.2fa.failed', $user);
            throw ValidationException::withMessages([
                'code' => 'Code incorrect.',
            ]);
        }

        // Régénère l'ID session après élévation de privilège (post-2FA).
        $request->session()->migrate(destroy: true);
        $request->session()->put('2fa_verified_at', now()->toIso8601String());
        $this->audit->log('auth.2fa.verified', $user);

        return redirect()->intended(route('admin.dashboard'));
    }

    /**
     * DELETE /2fa — désactivation (self-service pour les rôles non obligatoires).
     */
    public function disable(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 401);

        if ($user->hasAnyRole(['adm', 'sup'])) {
            return back()->withErrors([
                '2fa' => 'La désactivation n\'est pas autorisée pour votre rôle. Contactez le SUP.',
            ]);
        }

        $this->service->disable($user);
        $this->audit->log('auth.2fa.disabled', $user);

        return redirect()->route('admin.dashboard')->with('status', '2FA désactivée.');
    }
}
