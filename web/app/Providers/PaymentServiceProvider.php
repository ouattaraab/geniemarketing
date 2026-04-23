<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\PaymentGateway;
use App\Models\PaymentMethod;
use App\Services\Payment\FakePaymentGateway;
use App\Services\Payment\PaystackGateway;
use App\Services\Payment\WaveGateway;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use RuntimeException;

class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PaymentGateway::class, function (Application $app): PaymentGateway {
            $default = $this->resolveActiveProvider();

            // Force le fake quand demandé explicitement (dev/test) ou si aucun
            // provider n'est actif mais qu'on est hors production (évite de
            // bloquer le dev si l'admin a tout désactivé).
            if ($default === 'fake') {
                return new FakePaymentGateway(providerCode: 'wave');
            }

            $config = $this->resolveProviderConfig($default);

            // Bascule automatique vers le gateway factice si la clé est un
            // placeholder ou vide — hors production uniquement.
            $isPlaceholder = $config['secret'] === ''
                || Str::startsWith($config['secret'], ['placeholder', 'sk_test_placeholder', 'wave_test_placeholder']);

            if (! $app->environment('production') && $isPlaceholder) {
                return new FakePaymentGateway(providerCode: $default);
            }

            return match ($default) {
                'wave' => new WaveGateway(
                    apiKey: $config['secret'],
                    webhookSecret: (string) config('services.wave.webhook_secret', ''),
                    baseUrl: (string) config('services.wave.base_url', 'https://api.wave.com'),
                ),
                'paystack' => new PaystackGateway(
                    secretKey: $config['secret'],
                    publicKey: (string) config('services.paystack.public'),
                    baseUrl: (string) config('services.paystack.base_url', 'https://api.paystack.co'),
                ),
                default => throw new RuntimeException("Gateway de paiement inconnu : $default"),
            };
        });
    }

    /**
     * Sélectionne le provider actif en respectant :
     *   1. PAYMENT_GATEWAY=fake → force le fake.
     *   2. Le provider par défaut (services.payment.default) s'il est activé
     *      côté admin (PaymentMethod.is_enabled).
     *   3. Sinon le premier provider activé par ordre de position.
     *   4. Sinon lève une erreur en prod, fake en dev.
     */
    private function resolveActiveProvider(): string
    {
        $default = (string) config('services.payment.default', 'wave');

        if ($default === 'fake') {
            return 'fake';
        }

        // Pas de table payment_methods (avant migration) → fallback config uniquement.
        try {
            $tableExists = Schema::hasTable('payment_methods');
        } catch (\Throwable) {
            $tableExists = false;
        }

        if (! $tableExists) {
            return $default;
        }

        if (PaymentMethod::isEnabled($default)) {
            return $default;
        }

        $fallback = PaymentMethod::where('is_enabled', true)->orderBy('position')->value('code');
        if (is_string($fallback) && $fallback !== '') {
            return $fallback;
        }

        // Aucun moyen actif : en prod on laisse planter au checkout (avec
        // message UI plus bas), en dev on bascule sur le fake pour ne pas
        // bloquer le développement.
        return $this->app->environment('production') ? $default : 'fake';
    }

    /**
     * @return array{secret: string}
     */
    private function resolveProviderConfig(string $provider): array
    {
        return match ($provider) {
            'wave' => ['secret' => (string) config('services.wave.api_key', '')],
            'paystack' => ['secret' => (string) config('services.paystack.secret', '')],
            default => ['secret' => ''],
        };
    }
}
