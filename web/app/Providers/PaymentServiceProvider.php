<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\PaymentGateway;
use App\Services\Payment\FakePaymentGateway;
use App\Services\Payment\PaystackGateway;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PaymentGateway::class, function (Application $app): PaymentGateway {
            $default = config('services.payment.default', 'paystack');
            $secret = (string) config('services.paystack.secret');

            // Bascule automatique vers le gateway factice quand la clé est un placeholder
            // ou quand PAYMENT_GATEWAY=fake. Jamais en production.
            $useFake = $default === 'fake'
                || ! $app->environment('production') && (
                    $secret === ''
                    || Str::startsWith($secret, 'sk_test_placeholder')
                );

            if ($useFake) {
                return new FakePaymentGateway;
            }

            return match ($default) {
                'paystack' => new PaystackGateway(
                    secretKey: $secret,
                    publicKey: (string) config('services.paystack.public'),
                    baseUrl: (string) config('services.paystack.base_url', 'https://api.paystack.co'),
                ),
                default => throw new \RuntimeException("Gateway de paiement inconnu : $default"),
            };
        });
    }
}
