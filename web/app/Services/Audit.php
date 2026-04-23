<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

/**
 * Façade minimaliste pour enregistrer un événement d'audit.
 *
 * Usage : Audit::log('article.published', $article, ['scheduled' => false]);
 */
class Audit
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function log(string $action, ?Model $object = null, array $context = [], array $changes = []): void
    {
        $request = request();

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'object_type' => $object ? $object::class : null,
            'object_id' => $object?->getKey(),
            'ip' => $request?->ip(),
            'user_agent' => substr((string) ($request?->userAgent() ?? ''), 0, 512),
            'changes' => $changes ?: null,
            'context' => $context ?: null,
            'created_at' => now(),
        ]);
    }
}
