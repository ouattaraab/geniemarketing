<?php

declare(strict_types=1);

namespace App\DataObjects;

use App\Enums\PaymentStatus;

final readonly class PaymentVerification
{
    /**
     * @param  array<string, mixed>  $raw
     */
    public function __construct(
        public string $reference,
        public PaymentStatus $status,
        public int $amountCents,
        public string $currency,
        public ?string $channel,
        public ?string $transactionId,
        public ?string $failureReason,
        public array $raw,
    ) {}
}
