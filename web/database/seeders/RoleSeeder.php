<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

/**
 * Seed des 9 rôles métier définis dans le backlog (section 4).
 * Tous sous le guard `web` (session-based auth) — la distinction backoffice/public
 * se fait via la colonne `users.type` + ces rôles.
 */
class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'ab-d',   // Abonné Digital
            'ab-c',   // Abonné Combo
            'ab-e',   // Abonné Entreprise
            'red',    // Rédacteur
            'chef',   // Rédacteur en chef
            'edit',   // Administrateur éditorial
            'com',    // Gestionnaire commercial
            'adm',    // Administrateur système
            'sup',    // Super administrateur
        ];

        foreach ($roles as $name) {
            Role::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]);
        }
    }
}
