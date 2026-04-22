<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\PaymentGateway;
use App\Services\Payment\PaystackGateway;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PaymentGateway::class, function (Application $app): PaymentGateway {
            $default = config('services.payment.default', 'paystack');

            return match ($default) {
                'paystack' => new PaystackGateway(
                    secretKey: (string) config('services.paystack.secret'),
                    publicKey: (string) config('services.paystack.public'),
                    baseUrl: (string) config('services.paystack.base_url', 'https://api.paystack.co'),
                ),
                default => throw new \RuntimeException("Gateway de paiement inconnu : $default"),
            };
        });
    }
}
