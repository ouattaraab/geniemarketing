<?php

declare(strict_types=1);

use App\Enums\ArticleAccessLevel;
use App\Enums\ArticleStatus;
use App\Enums\SubscriptionStatus;
use App\Livewire\Public\CommentSection;
use App\Models\Article;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);

    $category = Category::create(['name' => 'Test', 'slug' => 'test-cat', 'is_active' => true]);

    $this->article = Article::create([
        'category_id' => $category->id,
        'title' => 'Test',
        'slug' => 'test-cs-'.uniqid(),
        'status' => ArticleStatus::Published,
        'access_level' => ArticleAccessLevel::Free,
        'published_at' => now(),
    ]);

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
});

it('refuse un post de commentaire à un visiteur non-abonné', function (): void {
    $user = User::factory()->create(['type' => 'subscriber', 'status' => 'active']);
    $this->actingAs($user);

    Livewire::test(CommentSection::class, ['article' => $this->article])
        ->set('newComment', 'Un commentaire sympa de quelqu\'un sans abonnement')
        ->call('submit')
        ->assertHasErrors('newComment');

    expect(Comment::count())->toBe(0);
});

it('autorise un abonné actif à poster en pending', function (): void {
    $user = User::factory()->create(['type' => 'subscriber', 'status' => 'active']);
    Subscription::create([
        'user_id' => $user->id,
        'subscription_plan_id' => $this->plan->id,
        'status' => SubscriptionStatus::Active,
        'start_date' => now()->subDay(),
        'end_date' => now()->addMonths(6),
        'auto_renewal' => true,
    ]);
    $this->actingAs($user->fresh());

    Livewire::test(CommentSection::class, ['article' => $this->article])
        ->set('newComment', 'Analyse pertinente, merci pour ce dossier.')
        ->call('submit')
        ->assertHasNoErrors();

    $comment = Comment::where('article_id', $this->article->id)->first();
    expect($comment)->not->toBeNull()->status->toBe('pending')->user_id->toBe($user->id);
});

it('valide la longueur minimum du commentaire', function (): void {
    $user = User::factory()->create(['type' => 'subscriber', 'status' => 'active']);
    Subscription::create([
        'user_id' => $user->id,
        'subscription_plan_id' => $this->plan->id,
        'status' => SubscriptionStatus::Active,
        'start_date' => now()->subDay(),
        'end_date' => now()->addMonths(6),
        'auto_renewal' => true,
    ]);
    $this->actingAs($user->fresh());

    Livewire::test(CommentSection::class, ['article' => $this->article])
        ->set('newComment', 'ok')       // < 3 caractères
        ->call('submit')
        ->assertHasErrors('newComment');
});
