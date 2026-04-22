<?php

declare(strict_types=1);

namespace App\Concerns;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

/**
 * Trait à ajouter aux modèles sensibles pour une traçabilité automatique
 * via les observers Eloquent (created / updated / deleted).
 * Chaque modèle peut surcharger $auditIgnore pour ne pas tracer certains attributs.
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function (Model $model): void {
            self::writeAudit('created', $model);
        });

        static::updated(function (Model $model): void {
            $changes = $model->getChanges();
            $ignore = property_exists($model, 'auditIgnore') ? (array) $model->auditIgnore : ['updated_at'];
            $changes = array_diff_key($changes, array_flip($ignore));
            if ($changes === []) {
                return;
            }
            self::writeAudit('updated', $model, $changes);
        });

        static::deleted(function (Model $model): void {
            self::writeAudit('deleted', $model);
        });
    }

    private static function writeAudit(string $action, Model $model, array $changes = []): void
    {
        $request = request();
        $shortName = class_basename($model);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => strtolower($shortName).'.'.$action,
            'object_type' => $model::class,
            'object_id' => $model->getKey(),
            'ip' => $request?->ip(),
            'user_agent' => substr((string) ($request?->userAgent() ?? ''), 0, 512),
            'changes' => $changes ?: null,
            'created_at' => now(),
        ]);
    }
}
