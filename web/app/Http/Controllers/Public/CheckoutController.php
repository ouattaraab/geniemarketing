<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Contracts\PaymentGateway;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Consent;
use App\Models\Newsletter;
use App\Models\NewsletterSubscription;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PromoCode;
use App\Models\Setting;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\Commerce\CheckoutService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly PaymentGateway $gateway,
        private readonly CheckoutService $checkout,
    ) {}

    /**
     * GET /abonnement/{plan:code}/inscription
     * Étape 1 du tunnel : formulaire complet avant paiement.
     */
    public function form(Request $request, SubscriptionPlan $plan): View
    {
        abort_unless($plan->is_active, 404);

        return view('public.checkout-form', [
            'plan' => $plan,
            'user' => $request->user(),
            'isCombo' => $plan->code === 'combo',
        ]);
    }

    /**
     * POST /abonnement/{plan:code}/inscription
     * Étape 2 : valide tout, crée/log le compte, prépare l'order, lance Paystack.
     */
    public function process(Request $request, SubscriptionPlan $plan): RedirectResponse
    {
        abort_unless($plan->is_active, 404);

        $user = $request->user();
        $rules = [
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:40'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:80'],
            'country' => ['nullable', 'string', 'max:80'],
            'promo_code' => ['nullable', 'string', 'max:50'],
            'accept_terms' => ['accepted'],
            'newsletter_opt_in' => ['nullable', 'boolean'],
        ];

        // Champs adresse de livraison obligatoires pour le Combo papier
        if ($plan->code === 'combo') {
            $rules['address'][0] = 'required';
            $rules['city'][0] = 'required';
            $rules['country'][0] = 'required';
        }

        // Création de compte si guest
        if ($user === null) {
            $rules['email'][] = 'unique:users,email';
            $rules['password'] = ['required', 'confirmed', Password::defaults()];
        }

        $data = $request->validate($rules);

        if ($user === null) {
            $user = User::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => strtolower($data['email']),
                'phone' => $data['phone'],
                'password' => Hash::make($data['password']),
                'type' => 'subscriber',
                'status' => 'active',
                'email_verified_at' => now(),
            ]);
            Auth::login($user, remember: true);
            // Ceinture contre la session fixation : un attaquant ayant
            // pré-positionné un cookie de session perdrait la main ici.
            $request->session()->regenerate();
        }

        // Traçabilité opposable du consentement (RGPD art. 7.1) : horodatage
        // + IP + UA + version du document au moment exact de l'acceptation,
        // pour tout utilisateur (nouveau ou déjà connecté).
        $this->recordCheckoutConsents($user, $request, (bool) ($data['newsletter_opt_in'] ?? false));

        if ($user->wasRecentlyCreated === false && $user->exists) {
            // Mise à jour des coordonnées si fournies
            $user->fill([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'phone' => $data['phone'],
            ])->save();
        }

        // Code promo (facultatif)
        $promo = null;
        if (! empty($data['promo_code'])) {
            $promo = PromoCode::where('code', $data['promo_code'])->first();
            if ($promo !== null && ! $promo->isUsable($plan->code)) {
                $promo = null;

                return back()
                    ->withInput()
                    ->withErrors(['promo_code' => 'Ce code promo n\'est pas valide pour cette formule.']);
            }
        }

        $order = $this->checkout->createOrderForPlan($user, $plan, $this->gateway->providerCode());

        // Snapshot facturation + livraison + promo sur l'order
        $order->update([
            'billing_address' => array_filter([
                'name' => $user->fullName(),
                'email' => $user->email,
                'phone' => $user->phone,
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'country' => $data['country'] ?? null,
            ]),
            'shipping_address' => $plan->code === 'combo' ? [
                'name' => $user->fullName(),
                'phone' => $user->phone,
                'address' => $data['address'],
                'city' => $data['city'],
                'country' => $data['country'],
            ] : null,
            'promo_code_id' => $promo?->id,
            'discount_cents' => $promo ? $promo->discountOn($order->subtotal_cents) : 0,
            'total_cents' => $promo
                ? $order->subtotal_cents + $order->tax_cents - $promo->discountOn($order->subtotal_cents)
                : $order->total_cents,
            'notes' => $data['newsletter_opt_in'] ?? false ? 'newsletter_opt_in' : null,
        ]);

        // Opt-in newsletter (hebdo-public par défaut)
        if ($data['newsletter_opt_in'] ?? false) {
            $this->subscribeToDefaultNewsletter($user, $request->ip());
        }

        try {
            $init = $this->gateway->initialize(
                order: $order->fresh(),
                callbackUrl: route('checkout.callback'),
            );
        } catch (\Throwable $e) {
            Log::error('Checkout initialize failed', [
                'order' => $order->reference,
                'error' => $e->getMessage(),
            ]);
            $this->checkout->markFailed($order, [], $e->getMessage(), $this->gateway->providerCode());

            return redirect()->route('checkout.form', $plan)->withErrors([
                'payment' => 'Impossible de lancer le paiement pour le moment. Merci de réessayer dans quelques minutes.',
            ]);
        }

        // Persiste le session id gateway (cos-xxx pour Wave) sur le Payment
        // pour pouvoir le réutiliser au GET /checkout/sessions/:id lors du
        // callback verify. Sans lien session↔reference, un retry utilisateur
        // bloquerait la vérification Wave.
        if ($init->accessCode !== null && $init->accessCode !== '') {
            Payment::where('order_id', $order->id)
                ->where('provider_reference', $order->reference)
                ->where('provider', $this->gateway->providerCode())
                ->latest('id')
                ->first()
                ?->update(['provider_transaction_id' => $init->accessCode]);
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

        if ($order->status === OrderStatus::Paid) {
            return redirect()->route('account')->with('status', $this->paymentSuccessMessage($order));
        }

        try {
            $verification = $this->gateway->verify($reference);
        } catch (\Throwable $e) {
            Log::error('Checkout verify failed', ['reference' => $reference, 'error' => $e->getMessage()]);

            return redirect()->route('subscribe')->withErrors(['payment' => 'Vérification impossible — nous reviendrons vers vous sous 24h.']);
        }

        if ($verification->status->value === 'success') {
            $this->checkout->finalizeOrder($order, $verification->raw, $this->gateway->providerCode());

            return redirect()->route('account')->with('status', $this->paymentSuccessMessage($order->fresh()));
        }

        if (in_array($verification->status->value, ['failed', 'abandoned', 'reversed'], true)) {
            $this->checkout->markFailed($order, $verification->raw, $verification->failureReason, $this->gateway->providerCode());

            return redirect()->route('subscribe')->withErrors([
                'payment' => 'Paiement non finalisé : '.($verification->failureReason ?? 'échec transaction.'),
            ]);
        }

        return redirect()->route('subscribe')->with('status', 'Votre paiement est en cours de traitement. Vous recevrez un email à confirmation.');
    }

    /**
     * Message flash à afficher après un paiement validé. L'achat article à
     * l'unité ne donne accès qu'à *cet* article — il ne faut pas faire croire
     * au client qu'il a débloqué tous les contenus comme un abonné.
     */
    private function paymentSuccessMessage(Order $order): string
    {
        if ($order->type === 'article') {
            $title = (string) ($order->items[0]['article_title'] ?? 'l\'article');

            return sprintf(
                'Paiement confirmé — « %s » est désormais accessible dans « Mes articles achetés » (accès permanent).',
                $title,
            );
        }

        return 'Paiement confirmé — votre abonnement est actif !';
    }

    /**
     * Enregistre une preuve opposable d'acceptation des CGU + politique de
     * confidentialité au moment du checkout, et du consentement marketing
     * si l'utilisateur a coché la case newsletter (art. 7 RGPD / loi 2013-450).
     */
    private function recordCheckoutConsents(User $user, Request $request, bool $marketingOptIn): void
    {
        $ip = $request->ip();
        $ua = (string) $request->userAgent();
        $termsVersion = (string) Setting::get('legal.terms_updated_at', now()->toDateString());
        $privacyVersion = (string) Setting::get('legal.privacy_updated_at', now()->toDateString());

        Consent::record($user->id, Consent::DOC_TERMS, $termsVersion, Consent::ACTION_GRANTED, 'checkout', $ip, $ua);
        Consent::record($user->id, Consent::DOC_PRIVACY, $privacyVersion, Consent::ACTION_GRANTED, 'checkout', $ip, $ua);

        if ($marketingOptIn) {
            Consent::record($user->id, Consent::DOC_MARKETING, now()->toDateString(), Consent::ACTION_GRANTED, 'checkout', $ip, $ua);
        }
    }

    private function subscribeToDefaultNewsletter(User $user, ?string $ip): void
    {
        $list = Newsletter::active()->where('is_default', true)->first();
        if ($list === null) {
            return;
        }

        NewsletterSubscription::firstOrCreate(
            ['newsletter_id' => $list->id, 'email' => $user->email],
            [
                'user_id' => $user->id,
                'status' => 'confirmed',  // on a déjà validé l'email via le checkout
                'confirmed_at' => now(),
                'source' => 'checkout',
                'ip' => $ip,
            ],
        );
    }
}
