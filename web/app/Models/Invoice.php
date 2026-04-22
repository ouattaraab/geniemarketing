<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'subscription_id',
        'number',
        'pdf_path',
        'pdf_disk',
        'amount_ht_cents',
        'tax_cents',
        'amount_ttc_cents',
        'currency',
        'billing_snapshot',
        'issued_at',
    ];

    protected function casts(): array
    {
        return [
            'amount_ht_cents' => 'integer',
            'tax_cents' => 'integer',
            'amount_ttc_cents' => 'integer',
            'billing_snapshot' => 'array',
            'issued_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public static function generateNumber(): string
    {
        $year = now()->format('Y');
        $last = self::whereYear('issued_at', $year)->orderByDesc('id')->first();
        $next = $last
            ? ((int) substr((string) $last->number, -6)) + 1
            : 1;

        return sprintf('GM-FAC-%s-%06d', $year, $next);
    }
}
