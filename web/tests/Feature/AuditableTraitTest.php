<?php

declare(strict_types=1);

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);

    $this->category = Category::create([
        'name' => 'Analyses',
        'slug' => 'analyses-test',
        'is_active' => true,
    ]);
});

it('trace la création d\'un article via le trait Auditable', function (): void {
    $user = User::factory()->backoffice()->create();
    $this->actingAs($user);

    $article = Article::create([
        'category_id' => $this->category->id,
        'title' => 'Test audit',
        'slug' => 'test-audit-creation',
        'status' => ArticleStatus::Draft,
    ]);

    $log = AuditLog::where('action', 'article.created')
        ->where('object_id', $article->id)
        ->first();

    expect($log)->not->toBeNull();
    expect($log->user_id)->toBe($user->id);
    expect($log->object_type)->toBe(Article::class);
});

it('trace la mise à jour d\'un article et enregistre le diff', function (): void {
    $user = User::factory()->backoffice()->create();
    $this->actingAs($user);

    $article = Article::create([
        'category_id' => $this->category->id,
        'title' => 'Avant',
        'slug' => 'test-audit-update',
        'status' => ArticleStatus::Draft,
    ]);

    AuditLog::query()->delete(); // on focus sur la modification

    $article->update(['title' => 'Après']);

    $log = AuditLog::where('action', 'article.updated')->first();
    expect($log)->not->toBeNull();
    expect($log->changes)->toHaveKey('title');
    expect($log->changes['title'])->toBe('Après');
});

it('ignore les attributs bruyants dans auditIgnore', function (): void {
    $user = User::factory()->backoffice()->create();
    $this->actingAs($user);

    $article = Article::create([
        'category_id' => $this->category->id,
        'title' => 'Test ignore',
        'slug' => 'test-audit-ignore',
        'status' => ArticleStatus::Draft,
    ]);

    AuditLog::query()->delete();

    // Ne modifie QUE views_count et updated_at — tous deux dans auditIgnore
    $article->views_count = 10;
    $article->save();

    expect(AuditLog::count())->toBe(0);
});
