<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DataObjects\PaymentInitialization;
use App\DataObjects\PaymentVerification;
use App\DataObjects\WebhookPayload;
use App\Models\Order;

/**
 * Contrat d'abstraction pour les gateways de paiement (R3 — pas de lock-in Paystack).
 * Implémenté par PaystackGateway · adaptable à CinetPay, Flutterwave, PayDunya…
 */
interface PaymentGateway
{
    /**
     * Initialise une transaction : crée l'intention de paiement et renvoie
     * l'URL de checkout hosted vers laquelle rediriger l'utilisateur.
     */
    public function initialize(Order $order, string $callbackUrl): PaymentInitialization;

    /**
     * Vérifie l'état d'une transaction côté gateway à partir de sa référence.
     * Idempotent.
     */
    public function verify(string $reference): PaymentVerification;

    /**
     * Valide l'authenticité d'un webhook (signature) et retourne
     * le payload normalisé si OK, null sinon.
     */
    public function parseWebhook(string $rawBody, string $signature): ?WebhookPayload;

    /**
     * Code d'identification du provider (ex: "paystack", "cinetpay").
     */
    public function providerCode(): string;
}
