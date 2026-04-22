<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MagazineIssue extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $fillable = [
        'number',
        'title',
        'theme',
        'slug',
        'cover_media_id',
        'pdf_disk',
        'pdf_path',
        'pdf_size_bytes',
        'pages_count',
        'publication_date',
        'price_paper_cents',
        'price_pdf_cents',
        'currency',
        'stock_paper',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'publication_date' => 'date',
            'price_paper_cents' => 'integer',
            'price_pdf_cents' => 'integer',
            'pdf_size_bytes' => 'integer',
            'pages_count' => 'integer',
            'stock_paper' => 'integer',
        ];
    }

    public function cover(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cover_media_id');
    }

    public function summaryEntries(): HasMany
    {
        return $this->hasMany(IssueSummaryEntry::class)->orderBy('position');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->where('publication_date', '<=', now());
    }

    public function hasPdf(): bool
    {
        return $this->pdf_path !== null && $this->pdf_disk !== null;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
