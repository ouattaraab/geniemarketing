<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IssueSummaryEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'magazine_issue_id',
        'article_id',
        'position',
        'page',
        'title',
        'excerpt',
        'section',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'page' => 'integer',
        ];
    }

    public function issue(): BelongsTo
    {
        return $this->belongsTo(MagazineIssue::class, 'magazine_issue_id');
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}
