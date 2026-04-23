<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Contracts\PaymentGateway;
use App\DataObjects\PaymentInitialization;
use App\DataObjects\PaymentVerification;
use App\DataObjects\WebhookPayload;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Intégration Wave Business — gateway de paiement Côte d'Ivoire / Sénégal.
 *
 * Endpoints utilisés :
 *   - POST /v1/checkout/sessions         → création d'une session hosted checkout
 *   - GET  /v1/checkout/sessions/:id     → vérification post-callback
 *   - webhook Wave-Signature: t=<ts>,v1=<hmac_sha256({ts}{body}, secret)>
 *
 * Docs : https://docs.wave.com/business
 *
 * Particularité XOF : Wave manipule le montant en unité principale (pas de
 * sous-unité), ex. "24000" pour 24 000 F CFA. En interne, le projet stocke
 * `total_cents` en centimes (compat Paystack/généraliste). On divise donc par
 * 100 au init et on remultiplie par 100 dans verify/webhook pour préserver la
 * défense montant/devise du CheckoutService sans la toucher.
 */
class WaveGateway implements PaymentGateway
{
    /**
     * Tolérance de fenêtre anti-replay sur le timestamp du webhook :
     *  - 5 minutes dans le passé (latence réseau / retry Wave acceptable)
     *  - 30 secondes dans le futur (dérive d'horloge raisonnable)
     */
    private const WEBHOOK_PAST_TOLERANCE = 300;

    private const WEBHOOK_FUTURE_TOLERANCE = 30;

    public function __construct(
        private readonly string $apiKey,
        private readonly string $webhookSecret,
        private readonly string $baseUrl = 'https://api.wave.com',
    ) {}

    public function providerCode(): string
    {
        return 'wave';
    }

    public function initialize(Order $order, string $callbackUrl): PaymentInitialization
    {
        $amountUnits = $this->centsToUnits($order->total_cents, $order->currency);

        $payload = [
            'amount' => $amountUnits,
            'currency' => strtoupper($order->currency),
            'client_reference' => $order->reference,
            'success_url' => $this->appendReference($callbackUrl, $order->reference, 'success'),
            'error_url' => $this->appendReference($callbackUrl, $order->reference, 'failed'),
        ];

        $response = $this->client()->post('/v1/checkout/sessions', $payload);

        if ($response->failed()) {
            Log::error('Wave initialize failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'order' => $order->reference,
            ]);

            throw new RuntimeException(sprintf(
                'Wave initialize failed (HTTP %d) : %s',
                $response->status(),
                $response->json('message') ?? $response->json('code') ?? 'erreur inconnue',
            ));
        }

        $data = $response->json() ?? [];

        $launchUrl = (string) ($data['wave_launch_url'] ?? $data['launch_url'] ?? '');
        if ($launchUrl === '') {
            throw new RuntimeException('Wave initialize: launch_url manquant dans la réponse.');
        }

        return new PaymentInitialization(
            reference: $order->reference,
            authorizationUrl: $launchUrl,
            accessCode: isset($data['id']) ? (string) $data['id'] : null,
        );
    }

    public function verify(string $reference): PaymentVerification
    {
        // Wave retourne la session par son id interne (cos-…). On le retrouve
        // via le `accessCode` stocké sur le Payment lors de l'init.
        $sessionId = $this->resolveSessionId($reference);

        $response = $this->client()->get('/v1/checkout/sessions/'.urlencode($sessionId));

        if ($response->failed()) {
            Log::error('Wave verify failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'reference' => $reference,
                'session_id' => $sessionId,
            ]);

            throw new RuntimeException(sprintf(
                'Wave verify failed (HTTP %d) : %s',
                $response->status(),
                $response->json('message') ?? 'erreur inconnue',
            ));
        }

        return $this->normaliseSessionToVerification($reference, $response->json() ?? []);
    }

    public function parseWebhook(string $rawBody, string $signature): ?WebhookPayload
    {
        $parsed = $this->parseSignatureHeader($signature);
        if ($parsed === null) {
            Log::warning('Wave webhook: header de signature invalide', ['header' => $signature]);

            return null;
        }

        [$timestamp, $v1] = [$parsed['t'], $parsed['v1']];

        $now = time();
        if ($timestamp < $now - self::WEBHOOK_PAST_TOLERANCE || $timestamp > $now + self::WEBHOOK_FUTURE_TOLERANCE) {
            Log::warning('Wave webhook: timestamp hors tolérance (rejeu probable)', [
                'signed_at' => $timestamp,
                'now' => $now,
            ]);

            return null;
        }

        $expected = hash_hmac('sha256', $timestamp.$rawBody, $this->webhookSecret);
        if (! hash_equals($expected, $v1)) {
            Log::warning('Wave webhook: signature HMAC invalide');

            return null;
        }

        try {
            $decoded = json_decode($rawBody, true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            Log::warning('Wave webhook: JSON invalide', ['error' => $e->getMessage()]);

            return null;
        }

        $event = (string) ($decoded['type'] ?? '');
        $data = (array) ($decoded['data'] ?? []);
        $reference = (string) ($data['client_reference'] ?? '');

        if ($event === '' || $reference === '') {
            return null;
        }

        return new WebhookPayload(
            event: $event,
            reference: $reference,
            data: $this->normaliseSessionData($data),
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function normaliseSessionToVerification(string $reference, array $data): PaymentVerification
    {
        $status = $this->mapStatus((string) ($data['payment_status'] ?? $data['checkout_status'] ?? ''));
        $normalised = $this->normaliseSessionData($data);

        return new PaymentVerification(
            reference: $reference,
            status: $status,
            amountCents: (int) $normalised['amount'],
            currency: (string) $normalised['currency'],
            channel: 'mobile_money',
            transactionId: isset($data['id']) ? (string) $data['id'] : null,
            failureReason: $data['last_payment_error']['message'] ?? $data['last_payment_error']['reason'] ?? null,
            raw: $normalised,
        );
    }

    /**
     * Normalise la réponse/payload Wave vers la structure attendue par
     * CheckoutService (montant déjà recalculé en centimes, customer_code, etc.).
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normaliseSessionData(array $data): array
    {
        $currency = strtoupper((string) ($data['currency'] ?? 'XOF'));
        $amountUnits = (string) ($data['amount'] ?? '0');

        return [
            'id' => $data['id'] ?? null,
            'reference' => $data['client_reference'] ?? null,
            'amount' => $this->unitsToCents($amountUnits, $currency),
            'currency' => $currency,
            'channel' => 'mobile_money',
            'status' => $data['payment_status'] ?? $data['checkout_status'] ?? null,
            'customer' => [
                'customer_code' => $data['payer_mobile'] ?? null,
            ],
            'gateway_response' => $data['last_payment_error']['message'] ?? null,
            'raw' => $data,
        ];
    }

    private function mapStatus(string $waveStatus): PaymentStatus
    {
        return match ($waveStatus) {
            'succeeded', 'complete' => PaymentStatus::Success,
            'failed' => PaymentStatus::Failed,
            'cancelled', 'expired' => PaymentStatus::Abandoned,
            'processing' => PaymentStatus::Processing,
            default => PaymentStatus::Pending,
        };
    }

    /**
     * @return array{t: int, v1: string}|null
     */
    private function parseSignatureHeader(string $header): ?array
    {
        if ($header === '') {
            return null;
        }

        $parts = [];
        foreach (explode(',', $header) as $segment) {
            $chunks = explode('=', trim($segment), 2);
            if (count($chunks) === 2) {
                $parts[trim($chunks[0])] = trim($chunks[1]);
            }
        }

        if (! isset($parts['t'], $parts['v1']) || ! ctype_digit($parts['t'])) {
            return null;
        }

        return ['t' => (int) $parts['t'], 'v1' => (string) $parts['v1']];
    }

    private function centsToUnits(int $cents, string $currency): string
    {
        $currency = strtoupper($currency);
        if (in_array($currency, ['XOF', 'XAF', 'GNF', 'DJF', 'RWF'], true)) {
            // Devises sans sous-unité : 24 000 XOF stocké comme 2 400 000 cents
            return (string) intdiv($cents, 100);
        }

        // Fallback générique : 2 décimales
        return number_format($cents / 100, 2, '.', '');
    }

    private function unitsToCents(string $amount, string $currency): int
    {
        $currency = strtoupper($currency);
        if (in_array($currency, ['XOF', 'XAF', 'GNF', 'DJF', 'RWF'], true)) {
            return ((int) $amount) * 100;
        }

        return (int) round(((float) $amount) * 100);
    }

    /**
     * Wave exige que success_url / error_url soient des URLs complètes. Notre
     * `checkout.callback` lit `reference` dans le query string — on l'ajoute
     * ici pour que le callback fonctionne au retour utilisateur.
     */
    private function appendReference(string $baseUrl, string $reference, string $status): string
    {
        $separator = str_contains($baseUrl, '?') ? '&' : '?';
        $suffix = 'reference='.urlencode($reference).'&status='.urlencode($status);

        return $baseUrl.$separator.$suffix;
    }

    /**
     * Wave exige le session id pour GET /v1/checkout/sessions/:id. On le
     * persiste dans `payments.provider_transaction_id` dès la réussite de
     * l'init (cf. CheckoutController::process). Fallback : la référence
     * interne — inadéquate pour Wave mais permet de produire une erreur
     * API propre plutôt que de planter en amont.
     */
    private function resolveSessionId(string $reference): string
    {
        $payment = Payment::where('provider_reference', $reference)
            ->where('provider', $this->providerCode())
            ->latest('id')
            ->first();

        $sessionId = $payment?->provider_transaction_id;

        return is_string($sessionId) && $sessionId !== '' ? $sessionId : $reference;
    }

    /**
     * @throws ConnectionException
     */
    private function client(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->withToken($this->apiKey)
            ->acceptJson()
            ->asJson()
            ->timeout(15);
    }
}
