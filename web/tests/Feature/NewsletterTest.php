<?php

declare(strict_types=1);

use App\Mail\NewsletterConfirmation;
use App\Models\Newsletter;
use App\Models\NewsletterSubscription;
use Illuminate\Support\Facades\Mail;

beforeEach(function (): void {
    $this->newsletter = Newsletter::create([
        'code' => 'hebdo-public',
        'name' => 'Hebdo',
        'type' => 'editorial',
        'requires_subscription' => false,
        'is_default' => true,
        'is_active' => true,
    ]);
});

it('inscrit un visiteur en pending et envoie l\'email de confirmation', function (): void {
    Mail::fake();

    $response = $this->post(route('newsletter.subscribe'), [
        'email' => 'lecteur@example.ci',
        'newsletter' => 'hebdo-public',
    ]);

    $response->assertRedirect();
    expect(NewsletterSubscription::where('email', 'lecteur@example.ci')->first())
        ->not->toBeNull()
        ->status->toBe('pending');

    Mail::assertSent(NewsletterConfirmation::class);
});

it('confirme l\'inscription via le token de confirmation', function (): void {
    $sub = NewsletterSubscription::create([
        'newsletter_id' => $this->newsletter->id,
        'email' => 'test@example.ci',
        'status' => 'pending',
    ]);

    expect($sub->fresh()->status)->toBe('pending');

    $this->get(route('newsletter.confirm', $sub->confirmation_token))
        ->assertRedirect(route('home'));

    expect($sub->fresh()->status)->toBe('confirmed');
    expect($sub->fresh()->confirmed_at)->not->toBeNull();
});

it('désabonne via le token de désinscription', function (): void {
    $sub = NewsletterSubscription::create([
        'newsletter_id' => $this->newsletter->id,
        'email' => 'test2@example.ci',
        'status' => 'confirmed',
        'confirmed_at' => now(),
    ]);

    $this->get(route('newsletter.unsubscribe', $sub->unsubscribe_token))
        ->assertRedirect(route('home'));

    expect($sub->fresh()->status)->toBe('unsubscribed');
    expect($sub->fresh()->unsubscribed_at)->not->toBeNull();
});

it('n\'envoie pas deux emails de confirmation pour une inscription déjà confirmée', function (): void {
    Mail::fake();

    NewsletterSubscription::create([
        'newsletter_id' => $this->newsletter->id,
        'email' => 'deja@example.ci',
        'status' => 'confirmed',
        'confirmed_at' => now(),
    ]);

    $this->post(route('newsletter.subscribe'), [
        'email' => 'deja@example.ci',
        'newsletter' => 'hebdo-public',
    ]);

    Mail::assertNothingSent();
});
