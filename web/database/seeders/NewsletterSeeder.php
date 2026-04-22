<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Newsletter;
use Illuminate\Database\Seeder;

class NewsletterSeeder extends Seeder
{
    public function run(): void
    {
        $lists = [
            [
                'code' => 'hebdo-public',
                'name' => 'Hebdo du mardi',
                'description' => 'La sélection de la rédaction, tous les mardis matin. Gratuit.',
                'type' => 'editorial',
                'requires_subscription' => false,
                'is_default' => true,
                'is_active' => true,
            ],
            [
                'code' => 'hebdo-premium',
                'name' => 'Hebdo Premium',
                'description' => 'Décryptages exclusifs réservés aux abonnés, chaque vendredi.',
                'type' => 'editorial',
                'requires_subscription' => true,
                'is_default' => false,
                'is_active' => true,
            ],
            [
                'code' => 'offres',
                'name' => 'Offres & événements',
                'description' => 'Nos offres commerciales et invitations GM Days (désabonnement à tout moment).',
                'type' => 'promotional',
                'requires_subscription' => false,
                'is_default' => false,
                'is_active' => true,
            ],
        ];

        foreach ($lists as $list) {
            Newsletter::updateOrCreate(['code' => $list['code']], $list);
        }
    }
}
