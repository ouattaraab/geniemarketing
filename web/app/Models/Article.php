<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\Auditable;
use App\Enums\ArticleAccessLevel;
use App\Enums\ArticleStatus;
use App\Services\TiptapRenderer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Article extends Model
{
    use Auditable, HasFactory, Searchable, SoftDeletes;

    /** @var array<int, string> Attributs ignorés dans l'audit (bruit). */
    public array $auditIgnore = ['updated_at', 'views_count'];

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        $renderer = app(TiptapRenderer::class);

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'lede' => $this->lede,
            'body_text' => $renderer->toPlainText($this->body, 6000),
            'category' => $this->category?->name,
            'category_slug' => $this->category?->slug,
            'status' => $this->status?->value,
            'access_level' => $this->access_level?->value,
            'published_at' => $this->published_at?->timestamp,
            'is_premium' => $this->access_level !== ArticleAccessLevel::Free,
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return $this->status === ArticleStatus::Published
            && $this->published_at !== null
            && $this->published_at <= now();
    }

    protected $fillable = [
        'category_id',
        'editorial_category_id',
        'cover_media_id',
        'parent_article_id',
        'created_by_user_id',
        'title',
        'slug',
        'lede',
        'body',
        'meta_title',
        'meta_description',
        'canonical_url',
        'status',
        'access_level',
        'price_cents',
        'price_currency',
        'is_sponsored',
        'sponsor_name',
        'scheduled_at',
        'published_at',
        'chapter_position',
        'reading_time_minutes',
    ];

    protected function casts(): array
    {
        return [
            'body' => 'array',
            'status' => ArticleStatus::class,
            'access_level' => ArticleAccessLevel::class,
            'price_cents' => 'integer',
            'is_sponsored' => 'boolean',
            'scheduled_at' => 'datetime',
            'published_at' => 'datetime',
            'chapter_position' => 'integer',
            'reading_time_minutes' => 'integer',
            'views_count' => 'integer',
            'comments_count' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function editorialCategory(): BelongsTo
    {
        return $this->belongsTo(EditorialCategory::class);
    }

    public function cover(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cover_media_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_article_id');
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(self::class, 'parent_article_id')
            ->orderBy('chapter_position');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ArticleVersion::class)->orderByDesc('revision');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'article_tag');
    }

    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class, 'article_author')
            ->withPivot(['position', 'role'])
            ->orderByPivot('position');
    }

    public function accessRights(): HasMany
    {
        return $this->hasMany(AccessRight::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ArticleStatus::Published)
            ->where('published_at', '<=', now());
    }

    public function scopeForCategory(Builder $query, Category $category): Builder
    {
        return $query->where('category_id', $category->id);
    }

    /**
     * Indique si un utilisateur donné peut consulter le contenu complet de l'article.
     * Ne prend PAS en compte le freemium (géré par FreemiumCounter au niveau du controller).
     */
    public function isAccessibleBy(?User $user): bool
    {
        // Libre : tout le monde
        if ($this->access_level === ArticleAccessLevel::Free) {
            return true;
        }

        if ($user === null) {
            return false;
        }

        // Équipe BO voit tout ce qui est publié
        if ($user->hasAnyRole(['red', 'chef', 'edit', 'com', 'adm', 'sup'])) {
            return true;
        }

        // Registered : compte gratuit suffit
        if ($this->access_level === ArticleAccessLevel::Registered) {
            return true;
        }

        // Droit ponctuel à cet article (achat à l'unité, cadeau…)
        $hasAccessRight = $user->accessRights()
            ->where('article_id', $this->id)
            ->where(function ($q): void {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->exists();

        if ($hasAccessRight) {
            return true;
        }

        // Abonné actif
        return $user->hasActiveSubscription();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Article payable à l'unité : access_level = premium ET price_cents > 0.
     * Un article `premium` sans prix reste accessible via abonnement ou
     * AccessRight offert/promo.
     */
    public function isPurchasable(): bool
    {
        return $this->access_level === ArticleAccessLevel::Premium
            && (int) $this->price_cents > 0;
    }
}
