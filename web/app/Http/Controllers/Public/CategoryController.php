<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Contracts\View\View;

class CategoryController extends Controller
{
    public function show(Category $category): View
    {
        abort_unless($category->is_active, 404);

        $articles = Article::query()
            ->published()
            ->forCategory($category)
            ->with(['editorialCategory', 'authors', 'cover', 'category'])
            ->latest('published_at')
            ->paginate(12);

        $taglines = [
            'la-une' => 'L\'actualité du marketing ivoirien en direct.',
            'analyses' => 'Décrypter, comprendre, anticiper.',
            'succes' => 'Les stratégies qui font la différence.',
            'interviews' => 'Paroles de décideurs.',
            'tribunes' => 'Les voix qui comptent.',
            'boutique' => 'Numéros papier, PDF, hors-séries.',
        ];

        return view('public.category', [
            'category' => $category,
            'categoryName' => $category->name,
            'tagline' => $taglines[$category->slug] ?? ($category->description ?? ''),
            'articles' => $articles,
        ]);
    }
}
