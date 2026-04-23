<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Newsletter;
use App\Models\Setting;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

/**
 * Check-list automatique avant lancement commercial. Retourne un exit code non-nul
 * si un des points critiques échoue — utilisable dans un pipeline CI/CD.
 */
#[Signature('gm:pre-launch {--json : Sortie JSON pour pipeline}')]
#[Description('Vérifie que la plateforme est prête pour la production.')]
class PreLaunchCheck extends Command
{
    /** @var array<int, array{label: string, status: string, detail: string, critical: bool}> */
    private array $checks = [];

    public function handle(): int
    {
        $this->check('APP_ENV est production', fn () => config('app.env') === 'production',
            detail: config('app.env'), critical: false);

        $this->check('APP_DEBUG est false', fn () => config('app.debug') === false,
            detail: config('app.debug') ? 'ON — RISQUE' : 'off', critical: true);

        $this->check('APP_KEY défini', fn () => ! empty(config('app.key')),
            detail: empty(config('app.key')) ? 'manquant' : 'ok', critical: true);

        $this->check('URL canonique HTTPS', fn () => str_starts_with((string) config('app.url'), 'https://'),
            detail: (string) config('app.url'), critical: true);

        $this->check('Timezone Africa/Abidjan', fn () => config('app.timezone') === 'Africa/Abidjan',
            detail: (string) config('app.timezone'), critical: false);

        $this->check('BD MySQL accessible', function (): bool {
            try {
                DB::connection()->getPdo();
                return true;
            } catch (\Throwable) {
                return false;
            }
        }, detail: config('database.default'), critical: true);

        $this->check('Tables principales migrées',
            fn () => Schema::hasTable('articles')
                && Schema::hasTable('subscriptions')
                && Schema::hasTable('audit_logs'),
            critical: true);

        $this->check('Super admin existe',
            fn () => User::where('type', 'backoffice')->whereHas('roles', fn ($q) => $q->where('name', 'sup'))->exists(),
            critical: true);

        $this->check('Mot de passe super admin changé (pas ChangeMe)',
            function (): bool {
                $sup = User::firstWhere('email', 'admin@geniemag.ci');
                if ($sup === null) {
                    return true;
                }
                return ! \Illuminate\Support\Facades\Hash::check('ChangeMe!2026', $sup->password);
            },
            critical: true);

        $this->check('3 plans d\'abonnement actifs',
            fn () => SubscriptionPlan::active()->count() >= 3,
            detail: SubscriptionPlan::active()->count().' plans', critical: true);

        $this->check('Au moins 1 newsletter active',
            fn () => Newsletter::active()->exists(),
            critical: true);

        $this->check('Paramètres seedés',
            fn () => Setting::count() >= 10,
            detail: Setting::count().' entrées', critical: false);

        $this->check('Gateway de paiement configuré',
            function (): bool {
                $default = (string) config('services.payment.default', 'wave');
                return match ($default) {
                    'wave' => ! empty(config('services.wave.api_key'))
                        && ! str_starts_with((string) config('services.wave.api_key'), 'wave_test_placeholder')
                        && ! empty(config('services.wave.webhook_secret'))
                        && ! str_starts_with((string) config('services.wave.webhook_secret'), 'wave_webhook_placeholder'),
                    'paystack' => ! empty(config('services.paystack.secret'))
                        && ! str_starts_with((string) config('services.paystack.secret'), 'sk_test_placeholder'),
                    default => false,
                };
            },
            detail: (string) config('services.payment.default', 'wave'),
            critical: true);

        $this->check('SMTP configuré (pas log)',
            fn () => config('mail.default') !== 'log',
            detail: (string) config('mail.default'), critical: false);

        $this->check('SESSION_DRIVER = redis ou database',
            fn () => in_array(config('session.driver'), ['redis', 'database'], true),
            detail: (string) config('session.driver'), critical: false);

        $this->check('Queue driver ≠ sync (en prod)',
            fn () => config('app.env') !== 'production' || config('queue.default') !== 'sync',
            detail: (string) config('queue.default'), critical: false);

        $this->check('Route /up accessible',
            fn () => Route::has('health') || in_array('/up', array_map(fn ($r) => $r->uri(), Route::getRoutes()->getRoutes()), true),
            critical: false);

        $this->check('Sitemap + robots.txt présents',
            fn () => file_exists(public_path('robots.txt')) && Route::has('sitemap'),
            critical: false);

        return $this->render();
    }

    private function check(string $label, callable $test, string $detail = '', bool $critical = false): void
    {
        try {
            $ok = (bool) $test();
        } catch (\Throwable $e) {
            $ok = false;
            $detail = $e->getMessage();
        }

        $this->checks[] = [
            'label' => $label,
            'status' => $ok ? 'ok' : ($critical ? 'FAIL' : 'warn'),
            'detail' => $detail,
            'critical' => $critical,
        ];
    }

    private function render(): int
    {
        if ($this->option('json')) {
            $this->line(json_encode($this->checks, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return $this->exitCode();
        }

        $this->info('=== Check-list avant lancement — GÉNIE MARKETING Mag ===');
        $this->newLine();

        foreach ($this->checks as $c) {
            $icon = match ($c['status']) {
                'ok' => '<fg=green>✓</>',
                'warn' => '<fg=yellow>⚠</>',
                default => '<fg=red>✗</>',
            };
            $line = sprintf('  %s %-50s', $icon, $c['label']);
            if ($c['detail']) {
                $line .= sprintf(' <fg=gray>(%s)</>', $c['detail']);
            }
            $this->line($line);
        }

        $this->newLine();
        $fails = array_filter($this->checks, fn ($c) => $c['status'] === 'FAIL');
        $warns = array_filter($this->checks, fn ($c) => $c['status'] === 'warn');

        if (! empty($fails)) {
            $this->error(sprintf('%d check critique(s) en échec — lancement bloqué.', count($fails)));
        } elseif (! empty($warns)) {
            $this->warn(sprintf('%d avertissement(s) non-bloquants.', count($warns)));
        } else {
            $this->info('Tous les checks passent. Prêt pour le lancement ✓');
        }

        return $this->exitCode();
    }

    private function exitCode(): int
    {
        foreach ($this->checks as $c) {
            if ($c['status'] === 'FAIL') {
                return self::FAILURE;
            }
        }

        return self::SUCCESS;
    }
}
