<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Services\Audit;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Bascule les abonnements arrivés à échéance en `expired`.
 * Le renouvellement automatique (via tokens Paystack) est géré séparément
 * — cette commande traite les abonnements dont `auto_renewal = false` ou le
 * renouvellement a échoué et dont `end_date` est passée.
 */
#[Signature('gm:subscriptions:expire {--dry-run : Affiche sans modifier}')]
#[Description('Bascule en "expired" les abonnements dont end_date est passée.')]
class ExpireSubscriptions extends Command
{
    public function handle(Audit $audit): int
    {
        $query = Subscription::query()
            ->whereIn('status', [SubscriptionStatus::Active, SubscriptionStatus::Trialing])
            ->where('end_date', '<', now());

        $count = $query->count();
        if ($count === 0) {
            $this->info('Aucun abonnement à expirer.');

            return self::SUCCESS;
        }

        $this->info(sprintf('%d abonnement(s) à expirer.', $count));

        if ($this->option('dry-run')) {
            $query->with('user')->chunk(50, function ($chunk): void {
                foreach ($chunk as $sub) {
                    $this->line(sprintf(' - #%d · %s · expire %s',
                        $sub->id,
                        $sub->user?->email ?? '—',
                        $sub->end_date?->toDateString(),
                    ));
                }
            });
            $this->comment('Mode --dry-run : aucune modification effectuée.');

            return self::SUCCESS;
        }

        $updated = 0;
        $query->chunkById(100, function ($subs) use (&$updated, $audit): void {
            foreach ($subs as $sub) {
                $sub->update(['status' => SubscriptionStatus::Expired]);
                $audit->log('subscription.expired', $sub, ['end_date' => $sub->end_date?->toIso8601String()]);
                $updated++;
            }
        });

        $this->info(sprintf('%d abonnement(s) marqué(s) expired.', $updated));

        return self::SUCCESS;
    }
}
