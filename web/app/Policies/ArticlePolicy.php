<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\User;

/**
 * Matrice des droits (cf. backlog §4.3) :
 *   - RED          : crée et édite SES PROPRES articles, non-publiés
 *   - CHEF         : édite tous, valide, publie, planifie
 *   - EDIT         : tout RED + CHEF + supprime
 *   - SUP          : super-admin, tout
 *   - COM/ADM      : lecture seule (pas implémenté ici, on renvoie false)
 */
class ArticlePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['red', 'chef', 'edit', 'com', 'adm', 'sup']);
    }

    public function view(User $user, Article $article): bool
    {
        if ($user->hasAnyRole(['chef', 'edit', 'sup', 'com', 'adm'])) {
            return true;
        }

        // Un rédacteur ne voit que ses articles.
        return $user->hasRole('red') && $article->created_by_user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['red', 'chef', 'edit', 'sup']);
    }

    public function update(User $user, Article $article): bool
    {
        if ($user->hasAnyRole(['chef', 'edit', 'sup'])) {
            return true;
        }

        // RED : uniquement ses propres brouillons / en relecture.
        return $user->hasRole('red')
            && $article->created_by_user_id === $user->id
            && in_array($article->status, [ArticleStatus::Draft, ArticleStatus::Review], true);
    }

    public function publish(User $user, Article $article): bool
    {
        return $user->hasAnyRole(['chef', 'edit', 'sup']);
    }

    public function delete(User $user, Article $article): bool
    {
        return $user->hasAnyRole(['chef', 'edit', 'sup']);
    }

    public function restore(User $user, Article $article): bool
    {
        return $user->hasAnyRole(['edit', 'sup']);
    }

    public function forceDelete(User $user, Article $article): bool
    {
        return $user->hasRole('sup');
    }
}
