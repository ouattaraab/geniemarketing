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
 * Endpoint webhook Wave — signé HMAC SHA-256 via `Wave-Signature: t=,v1=`.
 * Exclu de la protection CSRF (cf. bootstrap/app.php).
 *
 * Événements traités :
 *   - checkout.session.completed       → paiement confirmé
 *   - checkout.session.payment_failed  → paiement échoué
 *
 * Les autres événements sont loggés en info pour audit mais n'affectent pas
 * la commande.
 */
class WaveWebhookController extends Controller
{
    public function __construct(
        private readonly PaymentGateway $gateway,
        private readonly CheckoutService $checkout,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $signature = (string) $request->header('Wave-Signature', '');
        $rawBody = $request->getContent();

        $payload = $this->gateway->parseWebhook($rawBody, $signature);

        if ($payload === null) {
            Log::warning('Wave webhook invalid or unsigned', [
                'ip' => $request->ip(),
                'size' => strlen($rawBody),
            ]);

            return response()->json(['ok' => false], 401);
        }

        $order = Order::where('reference', $payload->reference)->first();
        if ($order === null) {
            // L4 — Référence inconnue : souvent un retry Wave sur une order
            // déjà supprimée par un admin, ou une mauvaise redirection de
            // webhook entre environnements. On log en warning (pas critical)
            // pour éviter de flooder Sentry et on répond 202 Accepted —
            // Wave n'active pas de retry sur cette réponse.
            Log::warning('Wave webhook: order not found', [
                'ref' => $payload->reference,
                'event' => $payload->event,
                'ip' => $request->ip(),
            ]);

            return response()->json(['ok' => true, 'msg' => 'order not found'], 202);
        }

        match ($payload->event) {
            'checkout.session.completed' => $this->checkout->finalizeOrder($order, $payload->data, $this->gateway->providerCode()),
            'checkout.session.payment_failed' => $this->checkout->markFailed($order, $payload->data, $payload->data['gateway_response'] ?? null, $this->gateway->providerCode()),
            default => Log::info('Wave webhook event ignoré', ['event' => $payload->event, 'ref' => $payload->reference]),
        };

        return response()->json(['ok' => true]);
    }
}
