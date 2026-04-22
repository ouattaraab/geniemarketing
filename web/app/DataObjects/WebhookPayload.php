<?php

declare(strict_types=1);

namespace App\DataObjects;

final readonly class WebhookPayload
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        public string $event,           // charge.success, subscription.create, invoice.create...
        public string $reference,
        public array $data,
    ) {}
}
