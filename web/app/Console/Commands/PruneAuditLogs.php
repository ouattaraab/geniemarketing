<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Purge les entrées d'audit au-delà de la rétention configurée (12 mois par défaut).
 * Conformité : RGPD art. 5.1.e + Loi 2013-450 CI — les logs personnels ne peuvent
 * être conservés qu'un temps proportionné à leur finalité.
 */
#[Signature('gm:audit:prune {--months=12 : Rétention en mois} {--dry-run : Ne supprime pas, affiche juste le décompte}')]
#[Description('Supprime les entrées d\'audit dépassant la rétention configurée (défaut 12 mois).')]
class PruneAuditLogs extends Command
{
    public function handle(): int
    {
        $months = max(1, (int) $this->option('months'));
        $cutoff = now()->subMonths($months);

        $count = AuditLog::where('created_at', '<', $cutoff)->count();

        if ($count === 0) {
            $this->info('Aucune entrée à purger.');

            return self::SUCCESS;
        }

        $this->info(sprintf('%d entrées créées avant %s à purger.', $count, $cutoff->toDateString()));

        if ($this->option('dry-run')) {
            $this->comment('Mode --dry-run : aucune suppression effectuée.');

            return self::SUCCESS;
        }

        $deleted = AuditLog::where('created_at', '<', $cutoff)->delete();
        $this->info(sprintf('%d entrées supprimées.', $deleted));

        return self::SUCCESS;
    }
}
