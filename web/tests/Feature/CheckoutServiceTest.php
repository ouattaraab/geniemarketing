<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Mail\SubscriptionConfirmed;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\Commerce\CheckoutService;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Mail;

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);

    $this->user = User::factory()->create([
        'type' => 'subscriber',
        'status' => 'pending',
    ]);

    $this->plan = SubscriptionPlan::create([
        'code' => 'digital',
        'name' => 'Digital',
        'description' => 'Test',
        'price_cents' => 24_000 * 100,
        'currency' => 'XOF',
        'duration_months' => 12,
        'trial_days' => 14,
        'licenses_included' => 1,
        'features' => [],
        'is_active' => true,
    ]);

    $this->service = app(CheckoutService::class);
});

it('crée une Order en pending + un Payment en pending', function (): void {
    $order = $this->service->createOrderForPlan($this->user, $this->plan);

    expect($order->status)->toBe(OrderStatus::Pending);
    expect($order->total_cents)->toBe(24_000 * 100);
    expect($order->currency)->toBe('XOF');
    expect($order->latestPayment->status)->toBe(PaymentStatus::Pending);
    expect($order->latestPayment->provider)->toBe('wave');
    expect($order->reference)->toStartWith('GM-');
});

it('finalise : marque paid, active user, crée Subscription en trial, facture et rôle', function (): void {
    Mail::fake();

    $order = $this->service->createOrderForPlan($this->user, $this->plan);

    $sub = $this->service->finalizeOrder($order, [
        'id' => 'psk_txn_1',
        'channel' => 'card',
        'reference' => $order->reference,
        'amount' => $order->total_cents,
        'currency' => $order->currency,
        'customer' => ['customer_code' => 'CUS_x'],
    ]);

    expect($sub->status->value)->toBe('trialing');
    expect($sub->trial_ends_at)->not->toBeNull();
    expect($order->fresh()->status)->toBe(OrderStatus::Paid);
    expect($order->fresh()->invoice)->not->toBeNull();
    expect($order->fresh()->invoice->number)->toStartWith('GM-FAC-');

    $user = $this->user->fresh();
    expect($user->status)->toBe('active');
    expect($user->hasRole('ab-d'))->toBeTrue();

    Mail::assertSent(SubscriptionConfirmed::class);
});

it('est idempotent : double appel renvoie la même Subscription', function (): void {
    Mail::fake();

    $order = $this->service->createOrderForPlan($this->user, $this->plan);
    $ok = ['reference' => $order->reference, 'amount' => $order->total_cents, 'currency' => $order->currency];

    $sub1 = $this->service->finalizeOrder($order, $ok + ['id' => '1']);
    $sub2 = $this->service->finalizeOrder($order, $ok + ['id' => '2']);

    expect($sub1->id)->toBe($sub2->id);
    expect($this->user->fresh()->subscriptions()->count())->toBe(1);
});

it('markFailed ne downgrade jamais une Order payée', function (): void {
    Mail::fake();

    $order = $this->service->createOrderForPlan($this->user, $this->plan);
    $this->service->finalizeOrder($order, [
        'id' => '1',
        'reference' => $order->reference,
        'amount' => $order->total_cents,
        'currency' => $order->currency,
    ]);

    $this->service->markFailed($order, ['error' => 'reversed'], 'test');

    expect($order->fresh()->status)->toBe(OrderStatus::Paid);
});

it('refuse finalizeOrder si le montant retourné ne matche pas la commande (C3 audit)', function (): void {
    $order = $this->service->createOrderForPlan($this->user, $this->plan);

    $this->service->finalizeOrder($order, [
        'id' => 'psk_tamper',
        'reference' => $order->reference,
        'amount' => 1, // tampering — 1 centime au lieu du prix réel
        'currency' => $order->currency,
    ]);
})->throws(RuntimeException::class, 'Montant ou devise');

it('refuse finalizeOrder si la devise ne matche pas (C3 audit)', function (): void {
    $order = $this->service->createOrderForPlan($this->user, $this->plan);

    $this->service->finalizeOrder($order, [
        'id' => 'psk_cur',
        'reference' => $order->reference,
        'amount' => $order->total_cents,
        'currency' => 'USD', // tampering de devise
    ]);
})->throws(RuntimeException::class, 'Montant ou devise');

it('refuse finalizeOrder si le session id gateway ne matche pas celui persisté à l\'init (H1 audit)', function (): void {
    $order = $this->service->createOrderForPlan($this->user, $this->plan, 'wave');
    // Simule la persistance du session id à l'init (comme le fait CheckoutController::process)
    $payment = $order->latestPayment;
    $payment->provider_transaction_id = 'cos-REAL-SESSION';
    $payment->save();

    // Le webhook/callback arrive avec un session id DIFFÉRENT (rejeu / confusion) → refusé
    $this->service->finalizeOrder($order, [
        'id' => 'cos-OTHER-SESSION',     // session id différent
        'reference' => $order->reference,
        'amount' => $order->total_cents,
        'currency' => $order->currency,
    ], 'wave');
})->throws(RuntimeException::class, 'Session id du gateway incohérent');

it('markFailed bascule Order + Payment en failed sur tentative non payée', function (): void {
    $order = $this->service->createOrderForPlan($this->user, $this->plan);

    $this->service->markFailed($order, ['gateway_response' => 'declined'], 'carte refusée');

    expect($order->fresh()->status)->toBe(OrderStatus::Failed);
    expect($order->fresh()->latestPayment->status)->toBe(PaymentStatus::Failed);
    expect($order->fresh()->latestPayment->failure_reason)->toBe('carte refusée');
});
