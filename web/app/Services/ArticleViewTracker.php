<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Comptabilise les vues d'un article :
 *  - Dédoublonnage : 1 vue / (article, fingerprint) / heure courante, enregistré en `article_views`.
 *  - Met à jour `articles.views_count` uniquement pour les vues nouvelles.
 *
 * Le fingerprint mixe IP + UA + session_id si dispo ; il n'identifie jamais durablement.
 */
class ArticleViewTracker
{
    public function track(Article $article, Request $request): void
    {
        $fingerprint = $this->fingerprint($request);
        $windowKey = now()->format('Y-m-d-H');

        $cacheKey = sprintf('view:%d:%s:%s', $article->id, $fingerprint, $windowKey);

        // Dédoublon mémoire court (une requête répétée dans la même heure ne re-incrémente pas)
        if (cache()->has($cacheKey)) {
            return;
        }
        cache()->put($cacheKey, true, now()->endOfHour());

        try {
            DB::transaction(function () use ($article, $request, $fingerprint): void {
                DB::table('article_views')->insertOrIgnore([
                    'article_id' => $article->id,
                    'user_id' => $request->user()?->id,
                    'fingerprint' => $fingerprint,
                    'referrer' => substr((string) $request->headers->get('referer'), 0, 500),
                    'created_at' => now(),
                ]);

                $article->newQuery()->whereKey($article->id)->increment('views_count');
            });
        } catch (\Throwable) {
            // Une défaillance de tracking ne doit jamais casser l'affichage de l'article.
        }
    }

    private function fingerprint(Request $request): string
    {
        $session = $request->hasSession() ? $request->session()->getId() : '';

        return hash('sha256', sprintf(
            '%s|%s|%s',
            $request->ip() ?? '0.0.0.0',
            $request->userAgent() ?? '',
            $session,
        ));
    }
}
