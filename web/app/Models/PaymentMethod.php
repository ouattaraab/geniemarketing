<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'is_enabled',
        'position',
        'env_var',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'position' => 'integer',
        ];
    }

    /**
     * Retourne true si le provider est activé par l'admin. Résultat caché
     * 5 minutes pour éviter un SELECT à chaque appel du PaymentGateway.
     */
    public static function isEnabled(string $code): bool
    {
        return (bool) Cache::remember(
            "payment_method:enabled:$code",
            300,
            fn (): bool => static::where('code', $code)->where('is_enabled', true)->exists(),
        );
    }

    /**
     * Nombre de providers actifs (pour savoir si /abonnement doit masquer la souscription).
     */
    public static function enabledCount(): int
    {
        return (int) Cache::remember(
            'payment_method:enabled_count',
            300,
            fn (): int => static::where('is_enabled', true)->count(),
        );
    }

    protected static function booted(): void
    {
        static::saved(function (PaymentMethod $m): void {
            Cache::forget("payment_method:enabled:{$m->code}");
            Cache::forget('payment_method:enabled_count');
        });
        static::deleted(function (PaymentMethod $m): void {
            Cache::forget("payment_method:enabled:{$m->code}");
            Cache::forget('payment_method:enabled_count');
        });
    }
}
