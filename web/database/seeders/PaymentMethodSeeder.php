<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            [
                'code' => 'wave',
                'name' => 'Wave Business',
                'description' => 'Mobile Money Wave — Côte d\'Ivoire, Sénégal, UEMOA (frais marchand 1 %).',
                'is_enabled' => true,
                'position' => 1,
                'env_var' => 'WAVE_API_KEY',
            ],
            [
                'code' => 'paystack',
                'name' => 'Paystack',
                'description' => 'Cartes Visa/Mastercard/Verve + Mobile Money via Paystack (fallback).',
                'is_enabled' => false,
                'position' => 2,
                'env_var' => 'PAYSTACK_SECRET_KEY',
            ],
        ];

        foreach ($methods as $m) {
            PaymentMethod::updateOrCreate(['code' => $m['code']], $m);
        }
    }
}
