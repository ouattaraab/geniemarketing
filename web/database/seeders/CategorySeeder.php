<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

/**
 * Rubriques du magazine selon la navigation du template v2 :
 * La Une · Analyses · Succès · Interviews · Tribunes · Boutique.
 */
class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $roots = [
            ['slug' => 'la-une',      'name' => 'La Une',      'position' => 1, 'color_hex' => '#B40F1E', 'description' => 'L\'actualité du marketing ivoirien en direct.'],
            ['slug' => 'analyses',    'name' => 'Analyses',    'position' => 2, 'color_hex' => '#2D2D2D', 'description' => 'Décryptages de fond, études sectorielles, dossiers.'],
            ['slug' => 'succes',      'name' => 'Succès',      'position' => 3, 'color_hex' => '#8A0A15', 'description' => 'Les stratégies qui gagnent, racontées par ceux qui les font.'],
            ['slug' => 'interviews',  'name' => 'Interviews',  'position' => 4, 'color_hex' => '#4B4B4B', 'description' => 'Paroles de décideurs et de talents.'],
            ['slug' => 'tribunes',    'name' => 'Tribunes',    'position' => 5, 'color_hex' => '#7A7A7A', 'description' => 'Les voix qui comptent, libres.'],
            ['slug' => 'boutique',    'name' => 'Boutique',    'position' => 6, 'color_hex' => '#1A1A1A', 'description' => 'Magazine papier, PDF, hors-séries.'],
        ];

        foreach ($roots as $data) {
            Category::firstOrCreate(
                ['slug' => $data['slug']],
                array_merge($data, ['is_active' => true]),
            );
        }

        // Exemples de sous-rubriques pour "Analyses"
        $analyses = Category::where('slug', 'analyses')->first();
        $subs = [
            ['slug' => 'analyses-mobile-money', 'name' => 'Mobile Money', 'position' => 1],
            ['slug' => 'analyses-retail',       'name' => 'Retail',       'position' => 2],
            ['slug' => 'analyses-digital',      'name' => 'Digital',      'position' => 3],
            ['slug' => 'analyses-brand',        'name' => 'Branding',     'position' => 4],
        ];
        foreach ($subs as $data) {
            Category::firstOrCreate(
                ['slug' => $data['slug']],
                array_merge($data, ['parent_id' => $analyses->id, 'is_active' => true]),
            );
        }
    }
}
