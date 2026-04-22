<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Models\MagazineIssue;
use Illuminate\Http\Response;

/**
 * Sitemap XML conforme protocole sitemaps.org.
 * Cache côté HTTP (1h) via les headers, régénéré à la demande.
 */
class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = [];

        // Pages statiques principales
        $urls[] = ['loc' => url('/'), 'changefreq' => 'daily', 'priority' => '1.0'];
        $urls[] = ['loc' => url('/abonnement'), 'changefreq' => 'monthly', 'priority' => '0.9'];
        $urls[] = ['loc' => url('/magazine'), 'changefreq' => 'weekly', 'priority' => '0.8'];

        // Rubriques actives
        foreach (Category::where('is_active', true)->whereNull('parent_id')->get() as $cat) {
            $urls[] = [
                'loc' => route('category.show', $cat),
                'changefreq' => 'daily',
                'priority' => '0.7',
                'lastmod' => $cat->updated_at->toDateString(),
            ];
        }

        // Articles publiés (Free uniquement — évite de révéler du premium aux bots)
        Article::published()
            ->where('access_level', 'free')
            ->orderByDesc('published_at')
            ->select(['slug', 'updated_at', 'published_at'])
            ->chunk(500, function ($articles) use (&$urls): void {
                foreach ($articles as $a) {
                    $urls[] = [
                        'loc' => route('article.show', $a),
                        'changefreq' => 'weekly',
                        'priority' => '0.6',
                        'lastmod' => ($a->updated_at ?? $a->published_at)?->toDateString(),
                    ];
                }
            });

        // Numéros magazine publiés
        foreach (MagazineIssue::published()->get(['slug', 'updated_at', 'publication_date']) as $issue) {
            $urls[] = [
                'loc' => url('/magazine'), // lecteur protégé → on pointe la liste
                'changefreq' => 'monthly',
                'priority' => '0.5',
                'lastmod' => ($issue->updated_at ?? $issue->publication_date)?->toDateString(),
            ];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        foreach ($urls as $u) {
            $xml .= "  <url>\n    <loc>".e($u['loc'])."</loc>\n";
            if (! empty($u['lastmod'])) {
                $xml .= "    <lastmod>{$u['lastmod']}</lastmod>\n";
            }
            $xml .= "    <changefreq>{$u['changefreq']}</changefreq>\n";
            $xml .= "    <priority>{$u['priority']}</priority>\n  </url>\n";
        }
        $xml .= '</urlset>'."\n";

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
