<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $published = Article::query()
            ->published()
            ->with(['category', 'editorialCategory', 'authors', 'cover'])
            ->latest('published_at');

        // Le plus récent devient le hero ; les suivants vont dans le fil.
        $hero = (clone $published)->first();
        $latest = $hero
            ? (clone $published)->where('id', '!=', $hero->id)->limit(6)->get()
            : $published->limit(6)->get();

        return view('public.home', [
            'hero' => $hero,
            'latest' => $latest,
        ]);
    }
}
