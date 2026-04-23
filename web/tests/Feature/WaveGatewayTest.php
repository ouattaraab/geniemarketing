<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\Payment\WaveGateway;
use Illuminate\Support\Facades\Http;

/**
 * Tests Feature couvrant initialize (call HTTP Wave) et verify (call HTTP
 * + résolution du session id depuis la BDD).
 */

beforeEach(function (): void {
    $this->plan = SubscriptionPlan::create([
        'code' => 'digital-wave-it',
        'name' => 'Digital Wave',
        'description' => '',
        'price_cents' => 24_000 * 100,
        'currency' => 'XOF',
        'duration_months' => 12,
        'trial_days' => 0,
        'licenses_included' => 1,
        'features' => [],
        'is_active' => true,
    ]);
    $this->user = User::factory()->create();
    $this->gw = new WaveGateway(apiKey: 'wave_ci_test', webhookSecret: 'whsec_test');
});

function makeOrder($user, $plan, string $ref): Order
{
    return Order::create([
        'reference' => $ref,
        'user_id' => $user->id,
        'subscription_plan_id' => $plan->id,
        'type' => 'subscription',
        'status' => OrderStatus::Pending,
        'subtotal_cents' => $plan->price_cents,
        'discount_cents' => 0,
        'tax_cents' => 0,
        'total_cents' => $plan->price_cents,
        'currency' => 'XOF',
        'items' => [],
        'billing_address' => ['email' => $user->email],
    ]);
}

it('initialize envoie le montant en unités XOF (pas en centimes) et retourne le launch_url', function (): void {
    Http::fake([
        'api.wave.com/v1/checkout/sessions' => Http::response([
            'id' => 'cos-ABC123',
            'wave_launch_url' => 'https://pay.wave.com/c/cos-ABC123',
            'amount' => '24000',
            'currency' => 'XOF',
            'payment_status' => 'processing',
            'checkout_status' => 'open',
        ], 201),
    ]);

    $order = makeOrder($this->user, $this->plan, 'GM-WAVE-INIT-1');

    $init = $this->gw->initialize($order, 'https://example.test/paiement/callback');

    expect($init->authorizationUrl)->toBe('https://pay.wave.com/c/cos-ABC123');
    expect($init->accessCode)->toBe('cos-ABC123');

    Http::assertSent(function ($request) {
        $body = $request->data();

        return $body['amount'] === '24000'
            && $body['currency'] === 'XOF'
            && $body['client_reference'] === 'GM-WAVE-INIT-1'
            && str_contains($body['success_url'], 'reference=GM-WAVE-INIT-1')
            && str_contains($body['error_url'], 'status=failed');
    });
});

it('verify résout le session id depuis le Payment et renvoie Success normalisé', function (): void {
    Http::fake([
        'api.wave.com/v1/checkout/sessions/cos-ABC123' => Http::response([
            'id' => 'cos-ABC123',
            'client_reference' => 'GM-WAVE-VERIFY-1',
            'amount' => '24000',
            'currency' => 'XOF',
            'payment_status' => 'succeeded',
            'checkout_status' => 'complete',
            'payer_mobile' => '+2250102030405',
        ], 200),
    ]);

    $order = makeOrder($this->user, $this->plan, 'GM-WAVE-VERIFY-1');
    Payment::create([
        'order_id' => $order->id,
        'provider' => 'wave',
        'provider_reference' => $order->reference,
        'provider_transaction_id' => 'cos-ABC123',
        'status' => PaymentStatus::Pending,
        'amount_cents' => $order->total_cents,
        'currency' => 'XOF',
    ]);

    $verification = $this->gw->verify($order->reference);

    expect($verification->status)->toBe(PaymentStatus::Success);
    expect($verification->amountCents)->toBe(24_000 * 100);
    expect($verification->currency)->toBe('XOF');
    expect($verification->transactionId)->toBe('cos-ABC123');
    // Champ `raw.amount` doit aussi être en centimes pour passer le check
    // montant/devise du CheckoutService::finalizeOrder.
    expect($verification->raw['amount'])->toBe(24_000 * 100);
});

it('verify mappe un paiement failed Wave vers PaymentStatus::Failed', function (): void {
    Http::fake([
        'api.wave.com/v1/checkout/sessions/cos-FAIL' => Http::response([
            'id' => 'cos-FAIL',
            'client_reference' => 'GM-WAVE-FAIL-1',
            'amount' => '24000',
            'currency' => 'XOF',
            'payment_status' => 'failed',
            'last_payment_error' => ['message' => 'Insufficient funds'],
        ], 200),
    ]);

    $order = makeOrder($this->user, $this->plan, 'GM-WAVE-FAIL-1');
    Payment::create([
        'order_id' => $order->id,
        'provider' => 'wave',
        'provider_reference' => $order->reference,
        'provider_transaction_id' => 'cos-FAIL',
        'status' => PaymentStatus::Pending,
        'amount_cents' => $order->total_cents,
        'currency' => 'XOF',
    ]);

    $verification = $this->gw->verify($order->reference);

    expect($verification->status)->toBe(PaymentStatus::Failed);
    expect($verification->failureReason)->toBe('Insufficient funds');
});
