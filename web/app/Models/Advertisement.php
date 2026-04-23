<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Advertisement extends Model
{
    use HasFactory, SoftDeletes;

    public const PLACEMENT_ARTICLE_TOP = 'article_top';

    public const PLACEMENT_ARTICLE_BOTTOM = 'article_bottom';

    public const PLACEMENT_ARTICLE_SIDEBAR = 'article_sidebar';

    public const PLACEMENT_HOME_LEADERBOARD = 'home_leaderboard';

    public const PLACEMENT_HOME_SIDEBAR = 'home_sidebar';

    protected $fillable = [
        'title',
        'placement',
        'media_id',
        'image_url',
        'alt_text',
        'link_url',
        'link_nofollow',
        'link_new_tab',
        'priority',
        'starts_at',
        'ends_at',
        'is_active',
        'sponsor_name',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'link_nofollow' => 'boolean',
            'link_new_tab' => 'boolean',
            'priority' => 'integer',
            'impressions' => 'integer',
            'clicks' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        $now = now();

        return $query
            ->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now));
    }

    public function scopeForPlacement(Builder $query, string $placement): Builder
    {
        return $query->where('placement', $placement);
    }

    /**
     * Retourne l'URL de l'image à afficher (média uploadé en priorité, sinon URL saisie).
     */
    public function imageUrl(): ?string
    {
        return $this->media?->url() ?? $this->image_url;
    }

    /**
     * Sélectionne une bannière active pour un placement donné, rotation
     * aléatoire pondérée par `priority` (plus de priorité = plus de chances).
     * Retourne null si aucune bannière active.
     */
    public static function pickForPlacement(string $placement): ?self
    {
        $candidates = static::query()
            ->active()
            ->forPlacement($placement)
            ->get(['id', 'priority']);

        if ($candidates->isEmpty()) {
            return null;
        }

        $total = $candidates->sum(fn ($c) => max(1, (int) $c->priority));
        $roll = random_int(1, (int) $total);
        $cursor = 0;

        foreach ($candidates as $c) {
            $cursor += max(1, (int) $c->priority);
            if ($roll <= $cursor) {
                return static::with('media')->find($c->id);
            }
        }

        return static::with('media')->find($candidates->first()->id);
    }

    /**
     * Incrémente le compteur d'impressions sans déclencher les events /
     * cast Eloquent (ultra-léger, non-bloquant pour le rendu page).
     */
    public function trackImpression(): void
    {
        DB::table($this->getTable())
            ->where('id', $this->id)
            ->increment('impressions');
    }

    public function trackClick(): void
    {
        DB::table($this->getTable())
            ->where('id', $this->id)
            ->increment('clicks');
    }

    public function ctrPercent(): float
    {
        return $this->impressions > 0
            ? round(($this->clicks / $this->impressions) * 100, 2)
            : 0.0;
    }
}
