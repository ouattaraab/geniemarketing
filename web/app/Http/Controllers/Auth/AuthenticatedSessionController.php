<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
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

        return redirect()->intended($this->landingPathFor($request->user()));
    }

    /**
     * Route par défaut après login, selon le profil utilisateur :
     *   - rôles backoffice       → /admin
     *   - abonné (type subscriber) → /compte
     *   - sinon                   → /
     */
    private function landingPathFor(?\App\Models\User $user): string
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
