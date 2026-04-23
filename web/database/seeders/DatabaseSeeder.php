<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Rôles & permissions Spatie
        $this->call(RoleSeeder::class);

        // Compte super admin de démarrage (à changer immédiatement en prod)
        $sup = User::firstOrCreate(
            ['email' => 'admin@geniemag.ci'],
            [
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'password' => 'ChangeMe!2026',
                'type' => 'backoffice',
                'status' => 'active',
                'email_verified_at' => now(),
            ],
        );
        $sup->assignRole('sup');

        // Taxonomies & contenu de démo
        $this->call([
            CategorySeeder::class,
            EditorialCategorySeeder::class,
            DemoArticleSeeder::class,
            SubscriptionPlanSeeder::class,
            PaymentMethodSeeder::class,
            NewsletterSeeder::class,
            SettingSeeder::class,
        ]);
    }
}
