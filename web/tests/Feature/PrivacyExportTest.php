<?php

declare(strict_types=1);

use App\Models\Consent;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Database\Seeders\RoleSeeder;

/**
 * Couvre les droits RGPD côté utilisateur (portabilité + effacement).
 */

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
});

it('refuses the export for a guest (401)', function (): void {
    $this->get('/compte/mes-donnees/export')->assertRedirect('/login');
});

it('exports all user data as JSON for an authenticated user', function (): void {
    $user = User::factory()->create([
        'first_name' => 'Jean',
        'last_name' => 'Dupont',
        'email' => 'jean@example.test',
        'phone' => '+2250102030405',
    ]);

    Consent::record($user->id, Consent::DOC_TERMS, '2026-04-23', Consent::ACTION_GRANTED, 'checkout', '127.0.0.1', 'UA');
    Consent::record($user->id, Consent::DOC_PRIVACY, '2026-04-23', Consent::ACTION_GRANTED, 'checkout', '127.0.0.1', 'UA');

    $response = $this->actingAs($user)->get('/compte/mes-donnees/export');

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'application/json; charset=utf-8');
    $response->assertHeader('Content-Disposition', 'attachment; filename="gm-donnees-'.$user->id.'-'.now()->format('Y-m-d-His').'.json"');

    $json = $response->json();

    // Structure attendue
    expect($json)->toHaveKeys(['export_metadata', 'account', 'subscriptions', 'orders', 'consents']);
    expect($json['account']['email'])->toBe('jean@example.test');
    expect($json['account']['first_name'])->toBe('Jean');
    expect($json['consents'])->toHaveCount(2);
    expect($json['export_metadata']['schema'])->toBe('gm-user-export/v1');
});

it('does not expose the password or 2FA secret in the export', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/compte/mes-donnees/export');

    $payload = $response->getContent();
    expect($payload)->not->toContain('password');
    expect($payload)->not->toContain('2fa_secret');
    expect($payload)->not->toContain('remember_token');
});

it('erases the account: anonymises PII but keeps orders for accounting (10y obligation)', function (): void {
    $plan = SubscriptionPlan::create([
        'code' => 'digital',
        'name' => 'Digital',
        'description' => '',
        'price_cents' => 2_400_000,
        'currency' => 'XOF',
        'duration_months' => 12,
        'trial_days' => 0,
        'licenses_included' => 1,
        'features' => [],
        'is_active' => true,
    ]);
    $user = User::factory()->create([
        'email' => 'erase-me@example.test',
        'first_name' => 'Alice',
        'last_name' => 'Martin',
        'phone' => '+2250987654321',
        'password' => \Illuminate\Support\Facades\Hash::make('SecretPassword2026!'),
    ]);

    // Simule une commande passée (preuve comptable).
    $order = \App\Models\Order::create([
        'reference' => 'GM-TEST-ERASE-1',
        'user_id' => $user->id,
        'subscription_plan_id' => $plan->id,
        'type' => 'subscription',
        'status' => \App\Enums\OrderStatus::Paid,
        'subtotal_cents' => $plan->price_cents,
        'discount_cents' => 0,
        'tax_cents' => 0,
        'total_cents' => $plan->price_cents,
        'currency' => 'XOF',
        'items' => [],
        'billing_address' => ['email' => $user->email, 'name' => 'Alice Martin'],
        'paid_at' => now(),
    ]);

    $response = $this->actingAs($user)
        ->delete('/profile', ['password' => 'SecretPassword2026!']);

    $response->assertRedirect('/');

    // User soft-deleted et PII anonymisées
    $raw = \DB::table('users')->where('id', $user->id)->first();
    expect($raw->deleted_at)->not->toBeNull();
    expect($raw->first_name)->toBe('Utilisateur');
    expect($raw->last_name)->toBe('supprimé');
    expect($raw->email)->not->toBe('erase-me@example.test');
    expect($raw->phone)->toBeNull();
    expect($raw->{'2fa_secret'})->toBeNull();

    // Order conservée pour obligation comptable, mais billing anonymisée.
    $orderReloaded = \DB::table('orders')->where('reference', 'GM-TEST-ERASE-1')->first();
    expect($orderReloaded)->not->toBeNull();
    $billing = json_decode($orderReloaded->billing_address, true);
    expect($billing['name'])->toBe('Compte supprimé');
    expect($billing['email'])->not->toBe('erase-me@example.test');

    // Preuve de la demande d'effacement enregistrée.
    expect(Consent::where('user_id', $user->id)
        ->where('action', Consent::ACTION_REVOKED)
        ->where('source', 'account_deletion')
        ->exists())->toBeTrue();
});
