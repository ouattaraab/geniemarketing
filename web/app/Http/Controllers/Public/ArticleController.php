<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Enums\ArticleAccessLevel;
use App\Enums\ArticleStatus;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\ArticleViewTracker;
use App\Services\FreemiumCounter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function __construct(
        private readonly FreemiumCounter $freemium,
        private readonly ArticleViewTracker $tracker,
    ) {}

    public function show(Request $request, Article $article): View
    {
        abort_unless(
            $article->status === ArticleStatus::Published && $article->published_at <= now(),
            404,
        );

        $this->tracker->track($article, $request);

        $article->load(['category', 'editorialCategory', 'authors', 'cover', 'tags']);

        $user = $request->user();
        $hasAccess = $article->isAccessibleBy($user);

        // Si le contenu n'est pas Free et que l'utilisateur n'a pas accès,
        // on applique le quota freemium : N articles premium par mois en accès complet.
        $freemiumBonus = false;
        if (! $hasAccess && $article->access_level !== ArticleAccessLevel::Free) {
            if ($this->freemium->hasRemainingQuota($request)) {
                $freemiumBonus = true;
                $hasAccess = true;
                $this->freemium->recordView($request, $article->id);
            }
        }

        $related = Article::query()
            ->published()
            ->where('category_id', $article->category_id)
            ->where('id', '!=', $article->id)
            ->with(['category', 'authors', 'cover'])
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('public.article', [
            'article' => $article,
            'hasAccess' => $hasAccess,
            'freemiumBonus' => $freemiumBonus,
            'freemiumRemaining' => $this->freemium->remaining($request),
            'freemiumLimit' => $this->freemium->limit(),
            'related' => $related,
        ]);
    }
}
