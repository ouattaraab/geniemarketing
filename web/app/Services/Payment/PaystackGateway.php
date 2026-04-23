<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Contracts\PaymentGateway;
use App\DataObjects\PaymentInitialization;
use App\DataObjects\PaymentVerification;
use App\DataObjects\WebhookPayload;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Implémentation Paystack du contrat PaymentGateway.
 *
 * Endpoints Paystack utilisés :
 *   - POST /transaction/initialize        → hosted checkout
 *   - GET  /transaction/verify/:reference → vérif post-callback
 *   - webhook X-Paystack-Signature HMAC SHA512
 *
 * Docs : https://paystack.com/docs/api
 */
class PaystackGateway implements PaymentGateway
{
    public function __construct(
        private readonly string $secretKey,
        private readonly string $publicKey,
        private readonly string $baseUrl = 'https://api.paystack.co',
    ) {}

    public function providerCode(): string
    {
        return 'paystack';
    }

    public function initialize(Order $order, string $callbackUrl): PaymentInitialization
    {
        $payload = [
            'email' => $order->user->email,
            'amount' => $order->total_cents,          // Paystack attend des centimes (kobo)
            'currency' => $order->currency,
            'reference' => $order->reference,
            'callback_url' => $callbackUrl,
            'metadata' => [
                'order_id' => $order->id,
                'order_reference' => $order->reference,
                'user_id' => $order->user_id,
                'custom_fields' => [
                    [
                        'display_name' => 'Plan',
                        'variable_name' => 'plan',
                        'value' => $order->plan?->code ?? '—',
                    ],
                ],
            ],
        ];

        $response = $this->client()
            ->post('/transaction/initialize', $payload);

        if ($response->failed()) {
            Log::error('Paystack initialize failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'order' => $order->reference,
            ]);

            throw new RuntimeException(sprintf(
                'Paystack initialize failed (HTTP %d) : %s',
                $response->status(),
                $response->json('message') ?? 'erreur inconnue',
            ));
        }

        $data = $response->json('data') ?? [];

        return new PaymentInitialization(
            reference: $data['reference'] ?? $order->reference,
            authorizationUrl: $data['authorization_url'] ?? '',
            accessCode: $data['access_code'] ?? null,
        );
    }

    public function verify(string $reference): PaymentVerification
    {
        $response = $this->client()->get('/transaction/verify/'.urlencode($reference));

        if ($response->failed()) {
            Log::error('Paystack verify failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'reference' => $reference,
            ]);

            throw new RuntimeException(sprintf(
                'Paystack verify failed (HTTP %d) : %s',
                $response->status(),
                $response->json('message') ?? 'erreur inconnue',
            ));
        }

        $data = $response->json('data') ?? [];

        return $this->normaliseTransactionToVerification($reference, $data);
    }

    public function parseWebhook(string $rawBody, string $signature): ?WebhookPayload
    {
        $expected = hash_hmac('sha512', $rawBody, $this->secretKey);
        if (! hash_equals($expected, $signature)) {
            Log::warning('Paystack webhook signature mismatch', ['signature' => $signature]);

            return null;
        }

        $decoded = json_decode($rawBody, true, flags: JSON_THROW_ON_ERROR);
        $event = $decoded['event'] ?? null;
        $data = $decoded['data'] ?? [];
        $reference = (string) ($data['reference'] ?? '');

        if (! is_string($event) || $reference === '') {
            return null;
        }

        return new WebhookPayload(event: $event, reference: $reference, data: $data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function normaliseTransactionToVerification(string $reference, array $data): PaymentVerification
    {
        $status = match ($data['status'] ?? null) {
            'success' => PaymentStatus::Success,
            'failed' => PaymentStatus::Failed,
            'abandoned' => PaymentStatus::Abandoned,
            'reversed' => PaymentStatus::Reversed,
            'ongoing', 'pending' => PaymentStatus::Processing,
            default => PaymentStatus::Pending,
        };

        return new PaymentVerification(
            reference: $reference,
            status: $status,
            amountCents: (int) ($data['amount'] ?? 0),
            currency: (string) ($data['currency'] ?? 'XOF'),
            channel: isset($data['channel']) ? (string) $data['channel'] : null,
            transactionId: isset($data['id']) ? (string) $data['id'] : null,
            failureReason: $data['gateway_response'] ?? null,
            raw: $data,
        );
    }

    /**
     * @throws ConnectionException
     */
    private function client(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->withToken($this->secretKey)
            ->acceptJson()
            ->asJson()
            ->timeout(15);
    }
}
