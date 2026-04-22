<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

/**
 * Gestion des comptes backoffice : réservée aux ADM et SUP (backlog §4.3).
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['adm', 'sup']);
    }

    public function view(User $user, User $target): bool
    {
        return $user->hasAnyRole(['adm', 'sup']) || $user->id === $target->id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['adm', 'sup']);
    }

    public function update(User $user, User $target): bool
    {
        if ($user->id === $target->id) {
            return true; // on peut toujours modifier son propre profil via settings
        }

        // Personne ne peut éditer un SUP sauf un autre SUP.
        if ($target->hasRole('sup') && ! $user->hasRole('sup')) {
            return false;
        }

        return $user->hasAnyRole(['adm', 'sup']);
    }

    public function delete(User $user, User $target): bool
    {
        if ($user->id === $target->id) {
            return false; // pas d'auto-désactivation
        }

        if ($target->hasRole('sup')) {
            return $user->hasRole('sup');
        }

        return $user->hasAnyRole(['adm', 'sup']);
    }
}
