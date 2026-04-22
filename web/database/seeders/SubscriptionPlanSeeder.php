<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

/**
 * Formules du backlog §3.3 :
 * - Digital    : 24 000 FCFA/an, 1 licence
 * - Combo      : 48 000 FCFA/an, 1 licence + papier
 * - Entreprise : 320 000 FCFA/an, 10 licences
 */
class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'code' => 'digital',
                'name' => 'Digital',
                'description' => 'Accès illimité aux articles premium et aux numéros PDF.',
                'price_cents' => 24_000 * 100,
                'currency' => 'XOF',
                'duration_months' => 12,
                'trial_days' => 14,
                'licenses_included' => 1,
                'features' => [
                    'Articles premium en illimité',
                    'Téléchargement des numéros PDF',
                    'Newsletter premium hebdomadaire',
                    'Espace abonné complet',
                ],
                'is_featured' => false,
                'position' => 1,
            ],
            [
                'code' => 'combo',
                'name' => 'Combo papier + digital',
                'description' => 'Le meilleur des deux mondes : digital + magazine papier livré chez vous.',
                'price_cents' => 48_000 * 100,
                'currency' => 'XOF',
                'duration_months' => 12,
                'trial_days' => 14,
                'licenses_included' => 1,
                'features' => [
                    'Tout le Digital',
                    'Magazine papier livré à domicile',
                    '2 invitations /an aux événements GM Days',
                    'Priorité sur les hors-séries',
                ],
                'is_featured' => true,
                'position' => 2,
            ],
            [
                'code' => 'entreprise',
                'name' => 'Entreprise',
                'description' => '10 licences nominatives et reporting d\'usage pour votre équipe.',
                'price_cents' => 320_000 * 100,
                'currency' => 'XOF',
                'duration_months' => 12,
                'trial_days' => 0,
                'licenses_included' => 10,
                'features' => [
                    '10 licences nominatives',
                    'Reporting d\'usage mensuel',
                    'Account manager dédié',
                    'Études sectorielles complètes',
                ],
                'is_featured' => false,
                'position' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['code' => $plan['code']],
                array_merge($plan, ['is_active' => true]),
            );
        }
    }
}
