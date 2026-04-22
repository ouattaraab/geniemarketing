<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ArticleAccessLevel;
use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use App\Models\EditorialCategory;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Articles de démo pour valider l'affichage public + admin.
 * À remplacer par les vrais articles saisis via le backoffice.
 */
class DemoArticleSeeder extends Seeder
{
    public function run(): void
    {
        $editor = User::firstWhere('email', 'admin@geniemag.ci');

        // Auteur de démo lié au compte admin
        $author = Author::firstOrCreate(
            ['slug' => 'la-redaction'],
            [
                'user_id' => $editor?->id,
                'name' => 'La rédaction',
                'headline' => 'L\'équipe éditoriale de GÉNIE MARKETING Mag',
                'bio' => 'La rédaction de GÉNIE MARKETING Mag couvre l\'actualité du marketing ivoirien et panafricain depuis Abidjan.',
                'is_active' => true,
            ],
        );

        $tagNames = ['Mobile Money', 'Social commerce', 'IA', 'Retail', 'Orange CI', 'CinetPay'];
        $tags = collect($tagNames)->map(fn (string $name) => Tag::firstOrCreate(
            ['slug' => Str::slug($name)],
            ['name' => $name],
        ));

        $laUne = Category::where('slug', 'la-une')->first();
        $analyses = Category::where('slug', 'analyses')->first();
        $succes = Category::where('slug', 'succes')->first();

        $analyseCat = EditorialCategory::where('code', 'analyse')->first();
        $succesCat = EditorialCategory::where('code', 'succes')->first();

        $demo = [
            [
                'category_id' => $laUne->id,
                'editorial_category_id' => $analyseCat->id,
                'title' => 'Le marketing digital ivoirien à la croisée des chemins',
                'lede' => 'Décryptage des stratégies qui font bouger l\'écosystème marketing en Côte d\'Ivoire en 2026, entre mobile money, social commerce et intelligence artificielle.',
                'access_level' => ArticleAccessLevel::Subscriber,
                'status' => ArticleStatus::Published,
                'reading_time_minutes' => 8,
            ],
            [
                'category_id' => $succes->id,
                'editorial_category_id' => $succesCat->id,
                'title' => 'Comment Orange CI a transformé son parcours client mobile',
                'lede' => 'Retour sur 18 mois de refonte CRM et les apprentissages clés pour le secteur.',
                'access_level' => ArticleAccessLevel::Subscriber,
                'status' => ArticleStatus::Published,
                'reading_time_minutes' => 6,
            ],
            [
                'category_id' => $analyses->id,
                'editorial_category_id' => $analyseCat->id,
                'title' => 'Social commerce : la révolution WhatsApp à Abidjan',
                'lede' => 'Comment les marques locales utilisent WhatsApp Business pour multiplier leurs conversions.',
                'access_level' => ArticleAccessLevel::Free,
                'status' => ArticleStatus::Published,
                'reading_time_minutes' => 5,
            ],
            [
                'category_id' => $analyses->id,
                'editorial_category_id' => $analyseCat->id,
                'title' => 'IA générative : 5 usages pertinents pour les équipes marketing ivoiriennes',
                'lede' => 'Au-delà du hype, des cas d\'usage concrets déjà en production.',
                'access_level' => ArticleAccessLevel::Subscriber,
                'status' => ArticleStatus::Draft,
                'reading_time_minutes' => 7,
            ],
        ];

        foreach ($demo as $data) {
            $slug = Str::slug($data['title']);
            $article = Article::firstOrCreate(
                ['slug' => $slug],
                array_merge($data, [
                    'slug' => $slug,
                    'created_by_user_id' => $editor?->id,
                    'published_at' => $data['status'] === ArticleStatus::Published ? now()->subDays(random_int(0, 10)) : null,
                    'meta_description' => $data['lede'],
                    'body' => [
                        'blocks' => [
                            ['type' => 'paragraph', 'content' => 'Contenu de démonstration — à remplacer par un vrai article rédigé depuis le backoffice.'],
                        ],
                    ],
                ]),
            );

            $article->authors()->syncWithoutDetaching([
                $author->id => ['position' => 0, 'role' => 'auteur'],
            ]);

            $article->tags()->syncWithoutDetaching(
                $tags->random(min(3, $tags->count()))->pluck('id')->toArray(),
            );
        }
    }
}
