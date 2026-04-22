<?php

declare(strict_types=1);

namespace App\Enums;

enum ArticleStatus: string
{
    case Draft = 'draft';
    case Review = 'review';
    case Scheduled = 'scheduled';
    case Published = 'published';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Brouillon',
            self::Review => 'En relecture',
            self::Scheduled => 'Planifié',
            self::Published => 'Publié',
            self::Archived => 'Archivé',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Draft => 'gm-gray',
            self::Review => 'gm-red',
            self::Scheduled => 'gm-charcoal-2',
            self::Published => 'gm-red',
            self::Archived => 'gm-gray',
        };
    }
}
