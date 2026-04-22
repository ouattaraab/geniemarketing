<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class NewsletterSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'newsletter_id',
        'user_id',
        'email',
        'confirmation_token',
        'unsubscribe_token',
        'status',
        'confirmed_at',
        'unsubscribed_at',
        'source',
        'ip',
    ];

    protected function casts(): array
    {
        return [
            'confirmed_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $sub): void {
            $sub->confirmation_token = $sub->confirmation_token ?: Str::random(48);
            $sub->unsubscribe_token = $sub->unsubscribe_token ?: Str::random(48);
        });
    }

    public function newsletter(): BelongsTo
    {
        return $this->belongsTo(Newsletter::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function confirm(): void
    {
        $this->status = 'confirmed';
        $this->confirmed_at = now();
        $this->save();
    }

    public function unsubscribe(): void
    {
        $this->status = 'unsubscribed';
        $this->unsubscribed_at = now();
        $this->save();
    }

    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('status', 'confirmed');
    }
}
