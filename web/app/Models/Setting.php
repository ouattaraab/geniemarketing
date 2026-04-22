<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value', 'type', 'group', 'label', 'description', 'position'];

    public static function get(string $key, mixed $default = null): mixed
    {
        // On ne cache que la forme sérialisable [value, type] pour éviter les soucis
        // d'unserialize d'objets Eloquent entre contextes (opcache, queues, etc.).
        $payload = Cache::rememberForever("setting:$key", function () use ($key): ?array {
            $row = static::where('key', $key)->first();

            return $row ? ['value' => $row->value, 'type' => $row->type] : null;
        });

        if ($payload === null) {
            return $default;
        }

        return static::castValue($payload['value'], $payload['type']);
    }

    public static function put(string $key, mixed $value): void
    {
        $row = static::where('key', $key)->first();
        if ($row === null) {
            return; // les paramètres sont seedés — on ne crée pas à la volée
        }
        $row->value = is_scalar($value) || $value === null ? (string) $value : json_encode($value);
        $row->save();
        Cache::forget("setting:$key");
    }

    public static function castValue(?string $raw, string $type): mixed
    {
        if ($raw === null) {
            return null;
        }

        return match ($type) {
            'boolean' => filter_var($raw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            'integer' => (int) $raw,
            'json' => json_decode($raw, true),
            default => $raw,
        };
    }

    protected static function booted(): void
    {
        static::saved(fn (Setting $s) => Cache::forget("setting:{$s->key}"));
        static::deleted(fn (Setting $s) => Cache::forget("setting:{$s->key}"));
    }
}
