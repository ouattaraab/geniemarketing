<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Contracts\PaymentGateway;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SubscriptionPlan;
use App\Services\Commerce\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly PaymentGateway $gateway,
        private readonly CheckoutService $checkout,
    ) {}

    /**
     * POST /abonnement/{plan:code}/checkout
     * Crée une Order + redirige vers le hosted checkout du gateway.
     */
    public function start(Request $request, SubscriptionPlan $plan): RedirectResponse
    {
        abort_unless($plan->is_active, 404);

        $user = $request->user();
        if ($user === null) {
            // On mémorise l'intention de checkout pour rediriger après inscription/connexion.
            session(['checkout.plan_code' => $plan->code]);

            return redirect()->route('login')->with('status', 'Connectez-vous ou créez un compte pour finaliser votre abonnement.');
        }

        $order = $this->checkout->createOrderForPlan($user, $plan, $this->gateway->providerCode());

        try {
            $init = $this->gateway->initialize(
                order: $order,
                callbackUrl: route('checkout.callback'),
            );
        } catch (\Throwable $e) {
            Log::error('Checkout initialize failed', [
                'order' => $order->reference,
                'error' => $e->getMessage(),
            ]);
            $this->checkout->markFailed($order, [], $e->getMessage(), $this->gateway->providerCode());

            return redirect()->route('subscribe')->withErrors([
                'payment' => 'Impossible de lancer le paiement pour le moment. Merci de réessayer.',
            ]);
        }

        return redirect()->away($init->authorizationUrl);
    }

    /**
     * GET /paiement/callback?reference=...
     * Redirection post-paiement — vérifie et finalise si succès.
     */
    public function callback(Request $request): RedirectResponse
    {
        $reference = (string) $request->query('reference', '');
        if ($reference === '') {
            return redirect()->route('subscribe')->withErrors(['payment' => 'Référence de paiement manquante.']);
        }

        $order = Order::where('reference', $reference)->first();
        if ($order === null) {
            return redirect()->route('subscribe')->withErrors(['payment' => 'Commande introuvable.']);
        }

        // Si déjà finalisée par webhook, on redirige directement
        if ($order->status === OrderStatus::Paid) {
            return redirect()->route('account')->with('status', 'Votre abonnement est actif. Bienvenue !');
        }

        try {
            $verification = $this->gateway->verify($reference);
        } catch (\Throwable $e) {
            Log::error('Checkout verify failed', ['reference' => $reference, 'error' => $e->getMessage()]);

            return redirect()->route('subscribe')->withErrors(['payment' => 'Vérification impossible — nous reviendrons vers vous sous 24h.']);
        }

        if ($verification->status->value === 'success') {
            $this->checkout->finalizeOrder($order, $verification->raw, $this->gateway->providerCode());

            return redirect()->route('account')->with('status', 'Paiement confirmé — votre abonnement est actif !');
        }

        if (in_array($verification->status->value, ['failed', 'abandoned', 'reversed'], true)) {
            $this->checkout->markFailed($order, $verification->raw, $verification->failureReason, $this->gateway->providerCode());

            return redirect()->route('subscribe')->withErrors([
                'payment' => 'Paiement non finalisé : '.($verification->failureReason ?? 'échec transaction.'),
            ]);
        }

        // Statut non encore résolu (ongoing) → inviter à patienter
        return redirect()->route('subscribe')->with('status', 'Votre paiement est en cours de traitement. Vous recevrez un email à confirmation.');
    }
}
