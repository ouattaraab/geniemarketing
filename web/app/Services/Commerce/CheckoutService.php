<?php

declare(strict_types=1);

namespace App\Services\Commerce;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\SubscriptionStatus;
use App\Mail\SubscriptionConfirmed;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Orchestre la création des commandes, paiements, abonnements et factures
 * à partir des événements côté gateway (init, verify, webhook).
 */
class CheckoutService
{
    /** Taux de TVA applicable en Côte d'Ivoire (0% pour les services numériques pour MVP — à affiner avec l'expert-comptable). */
    private const TAX_RATE_CENTS = 0;

    public function createOrderForPlan(User $user, SubscriptionPlan $plan, ?string $provider = 'wave'): Order
    {
        return DB::transaction(function () use ($user, $plan, $provider): Order {
            $reference = $this->uniqueReference();

            $subtotal = $plan->price_cents;
            $tax = (int) round($subtotal * self::TAX_RATE_CENTS / 100);

            $order = Order::create([
                'reference' => $reference,
                'user_id' => $user->id,
                'subscription_plan_id' => $plan->id,
                'type' => 'subscription',
                'status' => OrderStatus::Pending,
                'subtotal_cents' => $subtotal,
                'discount_cents' => 0,
                'tax_cents' => $tax,
                'total_cents' => $subtotal + $tax,
                'currency' => $plan->currency,
                'items' => [[
                    'type' => 'plan',
                    'plan_code' => $plan->code,
                    'plan_name' => $plan->name,
                    'unit_price_cents' => $plan->price_cents,
                    'duration_months' => $plan->duration_months,
                    'quantity' => 1,
                ]],
                'billing_address' => [
                    'name' => $user->fullName(),
                    'email' => $user->email,
                    'phone' => $user->phone,
                ],
            ]);

            // Crée un payment en pending pour tracer la tentative
            Payment::create([
                'order_id' => $order->id,
                'provider' => $provider ?? 'wave',
                'provider_reference' => $reference,
                'status' => PaymentStatus::Pending,
                'amount_cents' => $order->total_cents,
                'currency' => $order->currency,
            ]);

            return $order->fresh();
        });
    }

    /**
     * Finalise une commande après confirmation du paiement :
     *  - met à jour Order + Payment
     *  - crée Subscription active
     *  - émet une Invoice
     * Idempotent : si déjà traité, retourne la subscription existante.
     */
    public function finalizeOrder(Order $order, array $providerData, string $provider = 'wave'): Subscription
    {
        return DB::transaction(function () use ($order, $providerData, $provider): Subscription {
            // Verrou pessimiste : évite qu'un webhook et le callback
            // user-agent finalisent la même commande en parallèle
            // (double subscription / double invoice).
            $order = Order::where('id', $order->id)->lockForUpdate()->firstOrFail();

            // Idempotence — déjà traité par un autre appel.
            if ($order->status === OrderStatus::Paid && $order->subscription) {
                return $order->subscription;
            }

            // Defense-in-depth : même si la signature HMAC du webhook est
            // valide, on vérifie que le montant et la devise retournés par
            // Paystack correspondent EXACTEMENT à ceux de la commande.
            // Protège contre un réplay sur une autre référence, une
            // confusion test/prod, ou un provider compromis.
            $receivedAmount = (int) ($providerData['amount'] ?? -1);
            $receivedCurrency = strtoupper((string) ($providerData['currency'] ?? ''));
            if ($receivedAmount !== $order->total_cents || $receivedCurrency !== strtoupper($order->currency)) {
                \Illuminate\Support\Facades\Log::critical('Payment amount/currency mismatch — refusé', [
                    'order' => $order->reference,
                    'expected_amount_cents' => $order->total_cents,
                    'received_amount_cents' => $receivedAmount,
                    'expected_currency' => $order->currency,
                    'received_currency' => $receivedCurrency,
                    'provider' => $provider,
                ]);
                throw new \RuntimeException('Montant ou devise de paiement incohérent avec la commande.');
            }

            $payment = Payment::where('order_id', $order->id)
                ->where('provider_reference', $order->reference)
                ->where('provider', $provider)
                ->latest('id')
                ->firstOrFail();

            // H1 — Vérification session id / transaction id : le payload reçu
            // doit correspondre à l'intention persistée lors de l'init (cos-…
            // pour Wave, id transaction pour Paystack). Sans cette vérif, un
            // attaquant ayant obtenu un payload HMAC valide pourrait finaliser
            // une autre order du même user au même montant (retries webhook
            // mal redirigés, collisions pathologiques).
            $receivedSessionId = (string) ($providerData['id'] ?? '');
            $expectedSessionId = (string) ($payment->provider_transaction_id ?? '');
            if ($expectedSessionId !== '' && $receivedSessionId !== ''
                && ! hash_equals($expectedSessionId, $receivedSessionId)) {
                \Illuminate\Support\Facades\Log::critical('Payment session id mismatch — refusé', [
                    'order' => $order->reference,
                    'expected_session_id' => $expectedSessionId,
                    'received_session_id' => $receivedSessionId,
                    'provider' => $provider,
                ]);
                throw new \RuntimeException('Session id du gateway incohérent avec le paiement initial.');
            }

            $payment->status = PaymentStatus::Success;
            $payment->provider_transaction_id = $receivedSessionId !== ''
                ? $receivedSessionId
                : $payment->provider_transaction_id;
            $payment->channel = $this->mapChannel($providerData['channel'] ?? null);
            $payment->raw_response = $providerData;
            $payment->captured_at = now();
            $payment->save();

            $order->status = OrderStatus::Paid;
            $order->paid_at = now();
            $order->save();

            $plan = $order->plan;
            $sub = Subscription::create([
                'user_id' => $order->user_id,
                'subscription_plan_id' => $plan->id,
                'order_id' => $order->id,
                'status' => $plan->trial_days > 0 ? SubscriptionStatus::Trialing : SubscriptionStatus::Active,
                'start_date' => now(),
                'end_date' => now()->addMonths($plan->duration_months),
                'trial_ends_at' => $plan->trial_days > 0 ? now()->addDays($plan->trial_days) : null,
                'auto_renewal' => true,
                'paystack_customer_code' => $providerData['customer']['customer_code'] ?? null,
            ]);

            // Active l'utilisateur s'il était pending
            $user = $order->user;
            if ($user->status === 'pending') {
                $user->status = 'active';
                $user->save();
            }

            // Assigne le rôle abonné selon le plan
            $roleMap = [
                'digital' => 'ab-d',
                'combo' => 'ab-c',
                'entreprise' => 'ab-e',
            ];
            if (isset($roleMap[$plan->code])) {
                $user->syncRoles(array_values(array_unique([
                    ...$user->getRoleNames()->toArray(),
                    $roleMap[$plan->code],
                ])));
            }

            // Facture
            Invoice::create([
                'order_id' => $order->id,
                'subscription_id' => $sub->id,
                'number' => Invoice::generateNumber(),
                'amount_ht_cents' => $order->subtotal_cents - $order->discount_cents,
                'tax_cents' => $order->tax_cents,
                'amount_ttc_cents' => $order->total_cents,
                'currency' => $order->currency,
                'billing_snapshot' => $order->billing_address,
                'issued_at' => now(),
            ]);

            // Mail de bienvenue (non-bloquant : les erreurs SMTP ne cassent pas la transaction)
            try {
                Mail::to($user->email)->send(new SubscriptionConfirmed($sub->fresh(['user', 'plan', 'order.invoice'])));
            } catch (\Throwable $e) {
                Log::warning('Impossible d\'envoyer le mail de confirmation', [
                    'subscription' => $sub->id,
                    'error' => $e->getMessage(),
                ]);
            }

            return $sub;
        });
    }

    public function markFailed(Order $order, array $providerData, ?string $reason = null, string $provider = 'wave'): void
    {
        DB::transaction(function () use ($order, $providerData, $reason, $provider): void {
            $order->refresh();
            if ($order->status === OrderStatus::Paid) {
                return; // ne pas downgrader une commande payée
            }

            $payment = Payment::where('order_id', $order->id)
                ->where('provider_reference', $order->reference)
                ->where('provider', $provider)
                ->latest('id')
                ->first();

            if ($payment) {
                $payment->status = PaymentStatus::Failed;
                $payment->failure_reason = $reason;
                $payment->raw_response = $providerData;
                $payment->save();
            }

            $order->status = OrderStatus::Failed;
            $order->save();
        });
    }

    private function mapChannel(?string $channel): string
    {
        return match ($channel) {
            'card' => 'card',
            'bank', 'bank_transfer' => 'bank',
            'mobile_money' => 'mobile_money',
            'qr' => 'qr',
            default => 'other',
        };
    }

    private function uniqueReference(): string
    {
        do {
            $ref = Order::generateReference();
        } while (Order::where('reference', $ref)->exists());

        return $ref;
    }
}
