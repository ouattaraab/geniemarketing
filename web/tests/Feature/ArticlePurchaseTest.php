<?php

declare(strict_types=1);

use App\Enums\ArticleAccessLevel;
use App\Enums\ArticleStatus;
use App\Enums\OrderStatus;
use App\Models\AccessRight;
use App\Models\Article;
use App\Models\Category;
use App\Models\Order;
use App\Models\User;
use App\Services\Commerce\CheckoutService;
use App\Services\FreemiumCounter;
use Database\Seeders\RoleSeeder;

/**
 * Achat à l'unité d'un article premium : création d'order, redirection login
 * pour invité avec intent, finalisation qui crée l'AccessRight.
 */
beforeEach(function (): void {
    $this->seed(RoleSeeder::class);

    $this->category = Category::create([
        'name' => 'Test',
        'slug' => 'test-cat-'.uniqid(),
        'position' => 0,
    ]);
    $this->article = Article::create([
        'category_id' => $this->category->id,
        'title' => 'Enquête exclusive marché mobile CI',
        'slug' => 'enquete-exclusive-'.uniqid(),
        'lede' => 'Analyse approfondie du marché mobile money.',
        'body' => ['type' => 'doc', 'content' => []],
        'status' => ArticleStatus::Published,
        'access_level' => ArticleAccessLevel::Premium,
        'price_cents' => 100_000,              // 1 000 XOF = 100 000 cents
        'price_currency' => 'XOF',
        'published_at' => now()->subHour(),
    ]);
    $this->service = app(CheckoutService::class);
});

it('marks an article as purchasable when premium + price > 0', function (): void {
    expect($this->article->isPurchasable())->toBeTrue();

    $this->article->price_cents = 0;
    expect($this->article->isPurchasable())->toBeFalse();

    $this->article->price_cents = 100_000;
    $this->article->access_level = ArticleAccessLevel::Subscriber;
    expect($this->article->isPurchasable())->toBeFalse();
});

it('redirects a guest to login and stores the buy intent in session', function (): void {
    $response = $this->post(route('article.buy', $this->article));

    $response->assertRedirect(route('login', ['intent' => 'buy_article']));
    expect(session('gm_intent'))->toBe(['type' => 'buy_article', 'slug' => $this->article->slug]);
});

it('creates an order with type=article for a logged-in user', function (): void {
    $user = User::factory()->create(['type' => 'subscriber', 'status' => 'active']);

    $order = $this->service->createOrderForArticle($user, $this->article, 'wave');

    expect($order->type)->toBe('article');
    expect($order->total_cents)->toBe(100_000);
    expect($order->currency)->toBe('XOF');
    expect($order->status)->toBe(OrderStatus::Pending);
    expect($order->items[0]['article_id'])->toBe($this->article->id);
    expect($order->latestPayment->provider)->toBe('wave');
});

it('refuses createOrderForArticle on non-purchasable articles', function (): void {
    $this->article->update(['price_cents' => 0]);
    $user = User::factory()->create();

    expect(fn () => $this->service->createOrderForArticle($user, $this->article))
        ->toThrow(RuntimeException::class, 'pas disponible à l\'achat');
});

it('finalizeOrder on an article order creates an AccessRight (not Subscription)', function (): void {
    $user = User::factory()->create();
    $order = $this->service->createOrderForArticle($user, $this->article, 'wave');
    $payment = $order->latestPayment;
    $payment->provider_transaction_id = 'cos-TEST';
    $payment->save();

    $accessRight = $this->service->finalizeOrder($order, [
        'id' => 'cos-TEST',
        'reference' => $order->reference,
        'amount' => $order->total_cents,
        'currency' => $order->currency,
    ], 'wave');

    expect($accessRight)->toBeInstanceOf(AccessRight::class);
    expect($accessRight->user_id)->toBe($user->id);
    expect($accessRight->article_id)->toBe($this->article->id);
    expect($accessRight->source)->toBe('purchase');
    expect($accessRight->expires_at)->toBeNull();          // accès permanent

    // L'order est bien marquée payée + facture émise
    expect($order->fresh()->status)->toBe(OrderStatus::Paid);
    expect($order->fresh()->invoice)->not->toBeNull();

    // L'article est désormais accessible par l'utilisateur
    expect($this->article->fresh()->isAccessibleBy($user->fresh()))->toBeTrue();
});

it('finalizeOrder is idempotent on article purchase (double webhook)', function (): void {
    $user = User::factory()->create();
    $order = $this->service->createOrderForArticle($user, $this->article, 'wave');
    $payload = [
        'id' => 'cos-IDEMP',
        'reference' => $order->reference,
        'amount' => $order->total_cents,
        'currency' => $order->currency,
    ];

    $ar1 = $this->service->finalizeOrder($order, $payload, 'wave');
    $ar2 = $this->service->finalizeOrder($order->fresh(), $payload, 'wave');

    expect($ar1->id)->toBe($ar2->id);
    expect(AccessRight::where('user_id', $user->id)->where('article_id', $this->article->id)->count())->toBe(1);
});

it('continueIntent after login completes the flow automatically', function (): void {
    $user = User::factory()->create(['type' => 'subscriber', 'status' => 'active']);

    // Simule l'intent posé avant login (via guest buy)
    $this->actingAs($user)
        ->withSession(['gm_intent' => ['type' => 'buy_article', 'slug' => $this->article->slug]])
        ->get('/compte/continuer-achat')
        ->assertRedirect(); // 302 vers soit le simulateur fake (en dev) soit Wave

    // L'order a bien été créée
    expect(Order::where('user_id', $user->id)->where('type', 'article')->exists())->toBeTrue();
});

it('displays a dedicated paywall with price on premium purchasable articles', function (): void {
    // Désactive le quota freemium pour ce test : on veut voir le paywall plein
    // pour un guest, pas la vue "article offert par le quota".
    config(['gm.freemium_monthly_limit' => 0]);
    app()->forgetInstance(FreemiumCounter::class);

    $response = $this->get(route('article.show', $this->article));

    $response->assertStatus(200);
    $response->assertSeeText('Débloquer cet article');
    $response->assertSeeText('1 000 XOF');           // prix formaté
    $response->assertSeeText('Créer un compte et acheter');  // CTA guest
});

it('hides the purchase CTA for users who already have access via AccessRight', function (): void {
    $user = User::factory()->create();
    AccessRight::create([
        'user_id' => $user->id,
        'article_id' => $this->article->id,
        'source' => 'purchase',
        'granted_at' => now(),
    ]);

    $response = $this->actingAs($user)->get(route('article.show', $this->article));

    $response->assertStatus(200);
    $response->assertDontSeeText('Débloquer cet article');
});
