<?php

declare(strict_types=1);

use App\Enums\ArticleAccessLevel;
use App\Enums\ArticleStatus;
use App\Livewire\Admin\Articles\ArticleEditor;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);

    $this->category = Category::create([
        'name' => 'Analyses',
        'slug' => 'analyses-test',
        'is_active' => true,
    ]);

    $this->chef = User::factory()->create(['type' => 'backoffice', 'status' => 'active']);
    $this->chef->assignRole('chef');
});

it('refuse l\'accès à un visiteur non authentifié', function (): void {
    // auth()->user() est null → can('create', Article) renvoie false → abort 403.
    Livewire::test(ArticleEditor::class)->assertStatus(403);
});

it('refuse un rédacteur sans droit d\'update sur l\'article', function (): void {
    $redacteur1 = User::factory()->create(['type' => 'backoffice', 'status' => 'active']);
    $redacteur1->assignRole('red');

    $redacteur2 = User::factory()->create(['type' => 'backoffice', 'status' => 'active']);
    $redacteur2->assignRole('red');

    $article = Article::create([
        'category_id' => $this->category->id,
        'title' => 'Article de red2',
        'slug' => 'article-red2-'.uniqid(),
        'status' => ArticleStatus::Draft,
        'access_level' => ArticleAccessLevel::Free,
        'created_by_user_id' => $redacteur2->id,
    ]);

    $this->actingAs($redacteur1);

    Livewire::test(ArticleEditor::class, ['article' => $article])->assertStatus(403);
});

it('valide les champs requis avant de sauver', function (): void {
    $this->actingAs($this->chef);

    Livewire::test(ArticleEditor::class)
        ->set('title', '')
        ->set('slug', '')
        ->call('save')
        ->assertHasErrors(['title', 'slug', 'categoryId']);
});

it('crée un article en brouillon avec un auteur', function (): void {
    $this->actingAs($this->chef);

    Livewire::test(ArticleEditor::class)
        ->set('title', 'Mon premier article')
        ->set('slug', 'mon-premier-article')
        ->set('lede', 'Chapô de test')
        ->set('categoryId', $this->category->id)
        ->set('status', 'draft')
        ->set('accessLevel', 'free')
        ->call('save')
        ->assertHasNoErrors();

    expect(Article::where('slug', 'mon-premier-article')->first())
        ->not->toBeNull()
        ->title->toBe('Mon premier article')
        ->status->toBe(ArticleStatus::Draft)
        ->created_by_user_id->toBe($this->chef->id);
});

it('applique la transition draft → review', function (): void {
    $this->actingAs($this->chef);

    $article = Article::create([
        'category_id' => $this->category->id,
        'title' => 'Article à relire',
        'slug' => 'article-a-relire',
        'status' => ArticleStatus::Draft,
        'access_level' => ArticleAccessLevel::Free,
        'created_by_user_id' => $this->chef->id,
    ]);

    Livewire::test(ArticleEditor::class, ['article' => $article])
        ->call('applyTransition', 'review');

    expect($article->fresh()->status)->toBe(ArticleStatus::Review);
});

it('bloque la publication si pas de cover', function (): void {
    $this->actingAs($this->chef);

    $article = Article::create([
        'category_id' => $this->category->id,
        'title' => 'Sans cover',
        'slug' => 'sans-cover',
        'status' => ArticleStatus::Review,
        'access_level' => ArticleAccessLevel::Free,
        'created_by_user_id' => $this->chef->id,
    ]);

    Livewire::test(ArticleEditor::class, ['article' => $article])
        ->call('applyTransition', 'published');

    expect($article->fresh()->status)->toBe(ArticleStatus::Review);
});
