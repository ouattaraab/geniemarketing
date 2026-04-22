<?php

declare(strict_types=1);

use App\Enums\SubscriptionStatus;
use App\Models\MagazineIssue;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;

beforeEach(function (): void {
    $this->plan = SubscriptionPlan::create([
        'code' => 'digital',
        'name' => 'Digital',
        'price_cents' => 24_000 * 100,
        'currency' => 'XOF',
        'duration_months' => 12,
        'trial_days' => 0,
        'licenses_included' => 1,
        'features' => [],
        'is_active' => true,
    ]);

    $this->issue = MagazineIssue::create([
        'number' => 99,
        'title' => 'Numéro test',
        'slug' => 'numero-test',
        'publication_date' => now()->subDay(),
        'status' => 'published',
        'pdf_disk' => 'local',
        'pdf_path' => 'issues/2026/test.pdf',
    ]);
});

it('refuse la lecture à un visiteur non authentifié', function (): void {
    $this->get(route('magazine.reader', $this->issue))
        ->assertRedirect(route('login'));
});

it('refuse la lecture à un utilisateur sans abonnement actif', function (): void {
    $user = User::factory()->create(['type' => 'subscriber', 'status' => 'active']);

    $this->actingAs($user)
        ->get(route('magazine.reader', $this->issue))
        ->assertForbidden();
});

it('autorise la lecture à un abonné actif', function (): void {
    $user = User::factory()->create(['type' => 'subscriber', 'status' => 'active']);
    Subscription::create([
        'user_id' => $user->id,
        'subscription_plan_id' => $this->plan->id,
        'status' => SubscriptionStatus::Active,
        'start_date' => now()->subDay(),
        'end_date' => now()->addMonths(6),
        'auto_renewal' => true,
    ]);

    $this->actingAs($user->fresh())
        ->get(route('magazine.reader', $this->issue))
        ->assertOk();
});

it('renvoie 404 pour un numéro non publié', function (): void {
    $user = User::factory()->create(['type' => 'subscriber', 'status' => 'active']);
    Subscription::create([
        'user_id' => $user->id,
        'subscription_plan_id' => $this->plan->id,
        'status' => SubscriptionStatus::Active,
        'start_date' => now()->subDay(),
        'end_date' => now()->addMonths(6),
        'auto_renewal' => true,
    ]);

    $draft = MagazineIssue::create([
        'number' => 100,
        'title' => 'Brouillon',
        'slug' => 'brouillon',
        'publication_date' => now(),
        'status' => 'draft',
        'pdf_disk' => 'local',
        'pdf_path' => 'issues/2026/draft.pdf',
    ]);

    $this->actingAs($user->fresh())
        ->get(route('magazine.reader', $draft))
        ->assertNotFound();
});
