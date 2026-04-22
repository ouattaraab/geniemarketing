<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\EditorialCategory;
use Illuminate\Database\Seeder;

class EditorialCategorySeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['code' => 'analyse',   'name' => 'Analyse',            'position' => 1, 'badge_color_hex' => '#2D2D2D', 'description' => 'Décryptage argumenté d\'un phénomène ou d\'une tendance.'],
            ['code' => 'succes',    'name' => 'Succès',             'position' => 2, 'badge_color_hex' => '#B40F1E', 'description' => 'Histoire d\'une stratégie qui gagne.'],
            ['code' => 'echec',     'name' => 'Échec constructif',  'position' => 3, 'badge_color_hex' => '#8A0A15', 'description' => 'Leçons tirées d\'un échec, pour apprendre et rebondir.'],
            ['code' => 'interview', 'name' => 'Interview',          'position' => 4, 'badge_color_hex' => '#4B4B4B', 'description' => 'Conversation approfondie avec un acteur clé.'],
            ['code' => 'portrait',  'name' => 'Portrait',           'position' => 5, 'badge_color_hex' => '#7A7A7A', 'description' => 'Itinéraire d\'une personnalité qui compte.'],
            ['code' => 'tribune',   'name' => 'Tribune',            'position' => 6, 'badge_color_hex' => '#1A1A1A', 'description' => 'Opinion signée, engagée.'],
        ];

        foreach ($items as $item) {
            EditorialCategory::firstOrCreate(['code' => $item['code']], $item);
        }
    }
}
