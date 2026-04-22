<?php

declare(strict_types=1);

namespace App\Enums;

enum ArticleAccessLevel: string
{
    case Free = 'free';
    case Registered = 'registered';
    case Subscriber = 'subscriber';
    case Premium = 'premium';

    public function label(): string
    {
        return match ($this) {
            self::Free => 'Accès libre',
            self::Registered => 'Réservé aux inscrits (compte gratuit)',
            self::Subscriber => 'Réservé aux abonnés',
            self::Premium => 'Premium (achat à l\'unité ou offre)',
        };
    }

    public function requiresAuth(): bool
    {
        return $this !== self::Free;
    }

    public function requiresSubscription(): bool
    {
        return in_array($this, [self::Subscriber, self::Premium], true);
    }
}
