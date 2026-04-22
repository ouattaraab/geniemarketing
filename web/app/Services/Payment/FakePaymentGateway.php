<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Contracts\PaymentGateway;
use App\DataObjects\PaymentInitialization;
use App\DataObjects\PaymentVerification;
use App\DataObjects\WebhookPayload;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Cache;

/**
 * Gateway factice pour développement local : simule Paystack en redirigeant
 * vers une page de simulation interne plutôt que vers le hosted checkout.
 *
 * Activé automatiquement quand `PAYSTACK_SECRET_KEY` commence par
 * `sk_test_placeholder` ou quand `PAYMENT_GATEWAY=fake`.
 *
 * Ne doit JAMAIS être utilisé en production.
 */
class FakePaymentGateway implements PaymentGateway
{
    public function providerCode(): string
    {
        return 'fake';
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
            accessCode: 'fake_access_'.substr(md5($order->reference), 0, 10),
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
            channel: 'card',
            transactionId: 'fake_txn_'.time(),
            failureReason: $status === PaymentStatus::Success ? null : 'Simulé — '.$outcome,
            raw: [
                'status' => $outcome,
                'reference' => $reference,
                'id' => 'fake_txn_'.time(),
                'channel' => 'card',
                'customer' => [
                    'customer_code' => 'CUS_fake_'.substr(md5($reference), 0, 10),
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
