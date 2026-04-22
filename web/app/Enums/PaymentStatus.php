<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Success = 'success';
    case Failed = 'failed';
    case Abandoned = 'abandoned';
    case Reversed = 'reversed';

    public function isFinal(): bool
    {
        return in_array($this, [self::Success, self::Failed, self::Abandoned, self::Reversed], true);
    }
}
