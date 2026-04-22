<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'price_cents',
        'currency',
        'duration_months',
        'trial_days',
        'licenses_included',
        'features',
        'paystack_plan_code',
        'is_active',
        'is_featured',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'price_cents' => 'integer',
            'duration_months' => 'integer',
            'trial_days' => 'integer',
            'licenses_included' => 'integer',
            'features' => 'array',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function priceFormatted(): string
    {
        return number_format($this->price_cents / 100, 0, ',', ' ').' '.$this->currency;
    }

    public function getRouteKeyName(): string
    {
        return 'code';
    }
}
