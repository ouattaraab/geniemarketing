<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Si un intent a été mémorisé avant login (ex. achat article à l'unité),
        // on reprend le flow correspondant — l'user a cliqué « Acheter », on
        // le redirige automatiquement vers la suite plutôt que vers /compte.
        if ($intent = $request->session()->get('gm_intent')) {
            if (($intent['type'] ?? null) === 'buy_article' && ! empty($intent['slug'])) {
                return redirect()->route('article.intent_continue');
            }
        }

        return redirect()->intended($this->landingPathFor($request->user()));
    }

    /**
     * Route par défaut après login, selon le profil utilisateur :
     *   - rôles backoffice       → /admin
     *   - abonné (type subscriber) → /compte
     *   - sinon                   → /
     */
    private function landingPathFor(?User $user): string
    {
        if ($user === null) {
            return route('home', absolute: false);
        }

        if ($user->hasAnyRole(['red', 'chef', 'edit', 'adm', 'sup'])) {
            return route('admin.dashboard', absolute: false);
        }

        if ($user->type === 'subscriber') {
            return route('account', absolute: false);
        }

        return route('home', absolute: false);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
