<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Mail\NewsletterConfirmation;
use App\Models\Newsletter;
use App\Models\NewsletterSubscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class NewsletterController extends Controller
{
    /**
     * POST /newsletter/inscription
     */
    public function subscribe(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'newsletter' => ['nullable', 'string', Rule::exists('newsletters', 'code')->where('is_active', true)],
            'source' => ['nullable', 'string', 'max:40'],
        ]);

        $list = Newsletter::where('code', $validated['newsletter'] ?? 'hebdo-public')
            ->where('is_active', true)
            ->first();

        if ($list === null) {
            return back()->withErrors(['newsletter' => 'Liste de diffusion inconnue.']);
        }

        $subscription = NewsletterSubscription::firstOrCreate(
            ['newsletter_id' => $list->id, 'email' => $validated['email']],
            [
                'user_id' => $request->user()?->id,
                'source' => $validated['source'] ?? 'home',
                'ip' => $request->ip(),
                'status' => 'pending',
            ],
        );

        // Si déjà confirmé, on évite le spam
        if ($subscription->status === 'confirmed') {
            return back()->with('newsletter-status', 'Vous êtes déjà inscrit à cette lettre. Merci !');
        }

        // Réactivation d'un désinscrit : on reset status
        if ($subscription->status === 'unsubscribed') {
            $subscription->status = 'pending';
            $subscription->unsubscribed_at = null;
            $subscription->save();
        }

        try {
            Mail::to($subscription->email)->send(new NewsletterConfirmation($subscription->fresh('newsletter')));
        } catch (\Throwable $e) {
            Log::warning('Envoi email newsletter confirmation échoué', [
                'sub' => $subscription->id,
                'error' => $e->getMessage(),
            ]);
        }

        return back()->with(
            'newsletter-status',
            'Un email de confirmation vient de vous être envoyé à '.$subscription->email.'.',
        );
    }

    /**
     * GET /newsletter/confirmer/{token}
     */
    public function confirm(string $token): RedirectResponse
    {
        $subscription = NewsletterSubscription::where('confirmation_token', $token)->first();
        if ($subscription === null) {
            return redirect()->route('home')->with('newsletter-status', 'Lien de confirmation invalide ou expiré.');
        }

        if ($subscription->status !== 'confirmed') {
            $subscription->confirm();
        }

        return redirect()->route('home')->with(
            'newsletter-status',
            'Inscription confirmée ! Vous recevrez le prochain numéro de la lettre.',
        );
    }

    /**
     * GET /newsletter/desinscription/{token}
     */
    public function unsubscribe(string $token): RedirectResponse
    {
        $subscription = NewsletterSubscription::where('unsubscribe_token', $token)->first();
        if ($subscription === null) {
            return redirect()->route('home')->with('newsletter-status', 'Lien de désinscription invalide.');
        }

        if ($subscription->status !== 'unsubscribed') {
            $subscription->unsubscribe();
        }

        return redirect()->route('home')->with(
            'newsletter-status',
            'Vous êtes désabonné de la lettre « '.$subscription->newsletter->name.' ». À bientôt !',
        );
    }
}
