<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\PaymentGateway;
use App\Models\Order;
use App\Services\Commerce\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Endpoint webhook Paystack — signé HMAC SHA512 via X-Paystack-Signature.
 * À exclure de la protection CSRF (voir bootstrap/app.php).
 *
 * Paystack envoie plusieurs événements — on se concentre sur charge.success pour le MVP.
 * Les autres (subscription.create, invoice.*) seront traités en V1.
 */
class PaystackWebhookController extends Controller
{
    public function __construct(
        private readonly PaymentGateway $gateway,
        private readonly CheckoutService $checkout,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $signature = (string) $request->header('X-Paystack-Signature', '');
        $rawBody = $request->getContent();

        $payload = $this->gateway->parseWebhook($rawBody, $signature);

        if ($payload === null) {
            Log::warning('Paystack webhook invalid or unsigned', [
                'ip' => $request->ip(),
                'size' => strlen($rawBody),
            ]);

            return response()->json(['ok' => false], 401);
        }

        $order = Order::where('reference', $payload->reference)->first();
        if ($order === null) {
            // Webhook pour une référence inconnue : potentiel réplay, fuite de
            // clé ou bug côté Paystack. On alerte en critical (Sentry doit
            // remonter) mais on répond 202 Accepted pour éviter le retry loop.
            Log::critical('Paystack webhook: order not found', [
                'ref' => $payload->reference,
                'event' => $payload->event,
                'ip' => $request->ip(),
            ]);

            return response()->json(['ok' => true, 'msg' => 'order not found'], 202);
        }

        match ($payload->event) {
            'charge.success' => $this->checkout->finalizeOrder($order, $payload->data, $this->gateway->providerCode()),
            'charge.failed' => $this->checkout->markFailed($order, $payload->data, $payload->data['gateway_response'] ?? null, $this->gateway->providerCode()),
            default => Log::info('Paystack webhook event ignoré', ['event' => $payload->event, 'ref' => $payload->reference]),
        };

        return response()->json(['ok' => true]);
    }
}
