<?php

declare(strict_types=1);

namespace App\DataObjects;

final readonly class PaymentInitialization
{
    public function __construct(
        public string $reference,
        public string $authorizationUrl,
        public ?string $accessCode = null,
    ) {}
}
