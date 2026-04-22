<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'value',
        'currency',
        'valid_from',
        'valid_until',
        'max_uses',
        'uses_count',
        'plans_eligible',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'integer',
            'max_uses' => 'integer',
            'uses_count' => 'integer',
            'valid_from' => 'datetime',
            'valid_until' => 'datetime',
            'plans_eligible' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function isUsable(?string $planCode = null): bool
    {
        if (! $this->is_active) {
            return false;
        }
        if ($this->valid_from && $this->valid_from->isFuture()) {
            return false;
        }
        if ($this->valid_until && $this->valid_until->isPast()) {
            return false;
        }
        if ($this->max_uses && $this->uses_count >= $this->max_uses) {
            return false;
        }
        if ($planCode && $this->plans_eligible && ! in_array($planCode, $this->plans_eligible, true)) {
            return false;
        }

        return true;
    }

    public function discountOn(int $amountCents): int
    {
        return $this->type === 'percent'
            ? (int) round($amountCents * $this->value / 100)
            : (int) min($amountCents, $this->value);
    }
}
