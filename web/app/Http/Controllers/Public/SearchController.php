<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));
        $articles = collect();

        if ($query !== '' && mb_strlen($query) >= 2) {
            // Utilise Scout (MeiliSearch en prod, collection en local sans daemon).
            $articles = Article::search($query)
                ->take(30)
                ->get()
                ->load(['category', 'cover', 'authors']);
        }

        return view('public.search', [
            'query' => $query,
            'articles' => $articles,
        ]);
    }
}
