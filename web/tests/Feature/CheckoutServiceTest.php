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
    expect($order->latestPayment->provider)->toBe('paystack');
    expect($order->reference)->toStartWith('GM-');
});

it('finalise : marque paid, active user, crée Subscription en trial, facture et rôle', function (): void {
    Mail::fake();

    $order = $this->service->createOrderForPlan($this->user, $this->plan);

    $sub = $this->service->finalizeOrder($order, [
        'id' => 'psk_txn_1',
        'channel' => 'card',
        'reference' => $order->reference,
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

    $sub1 = $this->service->finalizeOrder($order, ['id' => '1', 'reference' => $order->reference]);
    $sub2 = $this->service->finalizeOrder($order, ['id' => '2', 'reference' => $order->reference]);

    expect($sub1->id)->toBe($sub2->id);
    expect($this->user->fresh()->subscriptions()->count())->toBe(1);
});

it('markFailed ne downgrade jamais une Order payée', function (): void {
    Mail::fake();

    $order = $this->service->createOrderForPlan($this->user, $this->plan);
    $this->service->finalizeOrder($order, ['id' => '1', 'reference' => $order->reference]);

    $this->service->markFailed($order, ['error' => 'reversed'], 'test');

    expect($order->fresh()->status)->toBe(OrderStatus::Paid);
});

it('markFailed bascule Order + Payment en failed sur tentative non payée', function (): void {
    $order = $this->service->createOrderForPlan($this->user, $this->plan);

    $this->service->markFailed($order, ['gateway_response' => 'declined'], 'carte refusée');

    expect($order->fresh()->status)->toBe(OrderStatus::Failed);
    expect($order->fresh()->latestPayment->status)->toBe(PaymentStatus::Failed);
    expect($order->fresh()->latestPayment->failure_reason)->toBe('carte refusée');
});
