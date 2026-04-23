<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Contracts\PaymentGateway;
use App\DataObjects\PaymentInitialization;
use App\DataObjects\PaymentVerification;
use App\DataObjects\WebhookPayload;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Support\Facades\Cache;

/**
 * Gateway factice pour développement local : simule un hosted checkout en
 * redirigeant vers une page de simulation interne plutôt que vers le provider.
 *
 * Activé automatiquement quand la clé API du provider sélectionné est un
 * placeholder ou quand `PAYMENT_GATEWAY=fake`. Paramètre son `providerCode()`
 * pour que l'historique des paiements reflète le provider réel visé (wave,
 * paystack, etc.).
 *
 * Ne doit JAMAIS être utilisé en production.
 */
class FakePaymentGateway implements PaymentGateway
{
    public function __construct(
        private readonly string $providerCode = 'wave',
    ) {}

    public function providerCode(): string
    {
        return $this->providerCode;
    }

    public function initialize(Order $order, string $callbackUrl): PaymentInitialization
    {
        // La "hosted checkout" URL pointe vers notre propre simulateur.
        // route() urlencode déjà les query params — pas de double encode.
        $simulatorUrl = route('checkout.simulator', [
            'reference' => $order->reference,
            'callback' => $callbackUrl,
        ]);

        return new PaymentInitialization(
            reference: $order->reference,
            authorizationUrl: $simulatorUrl,
            accessCode: 'fake_session_'.substr(md5($order->reference), 0, 12),
        );
    }

    public function verify(string $reference): PaymentVerification
    {
        // On lit le choix du simulateur (mémorisé en cache par la route simulate)
        $outcome = Cache::pull('fake-checkout:'.$reference, 'success');

        $status = match ($outcome) {
            'failed' => PaymentStatus::Failed,
            'abandoned' => PaymentStatus::Abandoned,
            default => PaymentStatus::Success,
        };

        $order = Order::where('reference', $reference)->first();

        return new PaymentVerification(
            reference: $reference,
            status: $status,
            amountCents: $order?->total_cents ?? 0,
            currency: $order?->currency ?? 'XOF',
            channel: 'mobile_money',
            transactionId: 'fake_txn_'.time(),
            failureReason: $status === PaymentStatus::Success ? null : 'Simulé — '.$outcome,
            raw: [
                'status' => $outcome,
                'reference' => $reference,
                'id' => 'fake_txn_'.time(),
                'amount' => $order?->total_cents ?? 0,
                'currency' => $order?->currency ?? 'XOF',
                'channel' => 'mobile_money',
                'customer' => [
                    'customer_code' => 'FAKE_'.substr(md5($reference), 0, 10),
                ],
                'gateway_response' => $outcome === 'success' ? 'Approved (simulated)' : 'Declined (simulated)',
            ],
        );
    }

    public function parseWebhook(string $rawBody, string $signature): ?WebhookPayload
    {
        // Le faux gateway ne reçoit pas de webhooks entrants
        return null;
    }
}
