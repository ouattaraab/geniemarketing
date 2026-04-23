<?php

declare(strict_types=1);

use App\Enums\ArticleAccessLevel;
use App\Enums\ArticleStatus;
use App\Enums\SubscriptionStatus;
use App\Models\Article;
use App\Models\Category;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);

    $this->category = Category::create([
        'name' => 'La Une',
        'slug' => 'la-une-test',
        'is_active' => true,
    ]);

    $this->plan = SubscriptionPlan::create([
        'code' => 'digital-test',
        'name' => 'Digital Test',
        'price_cents' => 24_000 * 100,
        'currency' => 'XOF',
        'duration_months' => 12,
        'trial_days' => 0,
        'licenses_included' => 1,
        'features' => [],
        'is_active' => true,
    ]);
});

function makeArticle(ArticleAccessLevel $level, Category $category): Article
{
    return Article::create([
        'category_id' => $category->id,
        'title' => 'Titre',
        'slug' => 'slug-'.uniqid(),
        'status' => ArticleStatus::Published,
        'access_level' => $level,
        'published_at' => now(),
    ]);
}

it('autorise tout le monde sur un article libre', function (): void {
    $article = makeArticle(ArticleAccessLevel::Free, $this->category);

    expect($article->isAccessibleBy(null))->toBeTrue();
    expect($article->isAccessibleBy(User::factory()->create()))->toBeTrue();
});

it('refuse un article abonné à un visiteur non authentifié', function (): void {
    $article = makeArticle(ArticleAccessLevel::Subscriber, $this->category);

    expect($article->isAccessibleBy(null))->toBeFalse();
});

it('refuse un article abonné à un utilisateur sans abonnement actif', function (): void {
    $article = makeArticle(ArticleAccessLevel::Subscriber, $this->category);
    $user = User::factory()->create(['type' => 'subscriber', 'status' => 'active']);

    expect($article->isAccessibleBy($user))->toBeFalse();
});

it('autorise un utilisateur avec abonnement actif', function (): void {
    $article = makeArticle(ArticleAccessLevel::Subscriber, $this->category);
    $user = User::factory()->create(['type' => 'subscriber', 'status' => 'active']);

    Subscription::create([
        'user_id' => $user->id,
        'subscription_plan_id' => $this->plan->id,
        'status' => SubscriptionStatus::Active,
        'start_date' => now()->subDay(),
        'end_date' => now()->addMonths(6),
        'auto_renewal' => true,
    ]);

    expect($article->fresh()->isAccessibleBy($user->fresh()))->toBeTrue();
});

it('autorise un utilisateur avec AccessRight ponctuel non expiré', function (): void {
    $article = makeArticle(ArticleAccessLevel::Premium, $this->category);
    $user = User::factory()->create(['type' => 'subscriber', 'status' => 'active']);

    $user->accessRights()->create([
        'article_id' => $article->id,
        'source' => 'purchase',
        'granted_at' => now()->subDay(),
        'expires_at' => now()->addYear(),
    ]);

    expect($article->fresh()->isAccessibleBy($user->fresh()))->toBeTrue();
});

it('refuse un AccessRight expiré', function (): void {
    $article = makeArticle(ArticleAccessLevel::Premium, $this->category);
    $user = User::factory()->create(['type' => 'subscriber', 'status' => 'active']);

    $user->accessRights()->create([
        'article_id' => $article->id,
        'source' => 'purchase',
        'granted_at' => now()->subYear(),
        'expires_at' => now()->subDay(),
    ]);

    expect($article->fresh()->isAccessibleBy($user->fresh()))->toBeFalse();
});

it('autorise toujours l\'équipe backoffice', function (): void {
    $article = makeArticle(ArticleAccessLevel::Premium, $this->category);

    foreach (['red', 'chef', 'edit', 'com', 'adm', 'sup'] as $role) {
        $user = User::factory()->create(['type' => 'backoffice', 'status' => 'active']);
        $user->assignRole($role);
        expect($article->fresh()->isAccessibleBy($user))->toBeTrue();
    }
});

it('autorise registered access_level à tout utilisateur connecté', function (): void {
    $article = makeArticle(ArticleAccessLevel::Registered, $this->category);
    $user = User::factory()->create(['type' => 'subscriber', 'status' => 'active']);

    expect($article->isAccessibleBy(null))->toBeFalse();
    expect($article->isAccessibleBy($user))->toBeTrue();
});

it('un AccessRight sur un article ne débloque pas les autres articles', function (): void {
    // Règle produit : l'achat à l'unité donne un accès permanent MAIS
    // **uniquement** à l'article acheté. Aucun débordement vers les autres
    // articles (ni subscriber, ni premium payants), sauf si l'utilisateur
    // prend un abonnement en plus.
    $bought = makeArticle(ArticleAccessLevel::Premium, $this->category);
    $otherPremium = makeArticle(ArticleAccessLevel::Premium, $this->category);
    $otherSubscriber = makeArticle(ArticleAccessLevel::Subscriber, $this->category);

    $user = User::factory()->create(['type' => 'subscriber', 'status' => 'active']);
    $user->accessRights()->create([
        'article_id' => $bought->id,
        'source' => 'purchase',
        'granted_at' => now(),
        'expires_at' => null,
    ]);

    expect($bought->fresh()->isAccessibleBy($user->fresh()))->toBeTrue();
    expect($otherPremium->fresh()->isAccessibleBy($user->fresh()))->toBeFalse();
    expect($otherSubscriber->fresh()->isAccessibleBy($user->fresh()))->toBeFalse();
});
