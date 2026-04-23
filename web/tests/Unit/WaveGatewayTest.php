<?php

declare(strict_types=1);

use App\Services\Payment\WaveGateway;

/**
 * Tests unitaires du contrat PaymentGateway côté Wave :
 * signature HMAC SHA-256, anti-replay sur t=, parsing de header.
 * Les cas qui touchent la BDD (init HTTP + Payment, verify) sont dans
 * tests/Feature/WaveGatewayTest.php.
 */

function makeWaveGateway(string $secret = 'whsec_test_wave', string $api = 'wave_ci_test'): WaveGateway
{
    return new WaveGateway(apiKey: $api, webhookSecret: $secret);
}

function signWaveWebhook(string $body, string $secret, ?int $timestamp = null): string
{
    $ts = $timestamp ?? time();
    $v1 = hash_hmac('sha256', $ts.$body, $secret);

    return "t={$ts},v1={$v1}";
}

it('expose le provider code "wave"', function (): void {
    expect(makeWaveGateway()->providerCode())->toBe('wave');
});

it('rejette un webhook sans signature', function (): void {
    $gw = makeWaveGateway();
    $body = json_encode(['type' => 'checkout.session.completed', 'data' => ['client_reference' => 'GM-1']]);

    expect($gw->parseWebhook($body, ''))->toBeNull();
});

it('rejette un webhook avec signature mal formée (pas de t= ou v1=)', function (): void {
    $gw = makeWaveGateway();
    $body = json_encode(['type' => 'checkout.session.completed', 'data' => ['client_reference' => 'GM-1']]);

    expect($gw->parseWebhook($body, 'deadbeef'))->toBeNull();
    expect($gw->parseWebhook($body, 'v1=abc'))->toBeNull();
    expect($gw->parseWebhook($body, 't=notanumber,v1=abc'))->toBeNull();
});

it('rejette un webhook avec HMAC invalide', function (): void {
    $gw = makeWaveGateway();
    $body = json_encode(['type' => 'checkout.session.completed', 'data' => ['client_reference' => 'GM-1']]);
    $badSig = 't='.time().',v1=deadbeefdeadbeef';

    expect($gw->parseWebhook($body, $badSig))->toBeNull();
});

it('rejette un webhook avec timestamp trop ancien (anti-replay)', function (): void {
    $secret = 'whsec_test_wave';
    $gw = makeWaveGateway($secret);
    $body = json_encode(['type' => 'checkout.session.completed', 'data' => ['client_reference' => 'GM-1']]);

    // t = now - 10 minutes (hors fenêtre de 5 min tolérée)
    $sig = signWaveWebhook($body, $secret, time() - 600);

    expect($gw->parseWebhook($body, $sig))->toBeNull();
});

it('accepte un webhook checkout.session.completed signé correctement', function (): void {
    $secret = 'whsec_test_wave';
    $gw = makeWaveGateway($secret);

    $body = json_encode([
        'type' => 'checkout.session.completed',
        'data' => [
            'id' => 'cos-18qq25rgr100a',
            'client_reference' => 'GM-2026-000042',
            'amount' => '24000',
            'currency' => 'XOF',
            'payment_status' => 'succeeded',
            'checkout_status' => 'complete',
            'payer_mobile' => '+2250102030405',
        ],
    ]);
    $sig = signWaveWebhook($body, $secret);

    $payload = $gw->parseWebhook($body, $sig);

    expect($payload)->not->toBeNull();
    expect($payload->event)->toBe('checkout.session.completed');
    expect($payload->reference)->toBe('GM-2026-000042');
    // Montant normalisé en centimes pour compat CheckoutService
    expect($payload->data['amount'])->toBe(24_000 * 100);
    expect($payload->data['currency'])->toBe('XOF');
});

it('normalise un webhook payment_failed', function (): void {
    $secret = 'whsec_test_wave';
    $gw = makeWaveGateway($secret);

    $body = json_encode([
        'type' => 'checkout.session.payment_failed',
        'data' => [
            'id' => 'cos-xxx',
            'client_reference' => 'GM-2026-000043',
            'amount' => '24000',
            'currency' => 'XOF',
            'payment_status' => 'failed',
            'last_payment_error' => ['message' => 'Insufficient funds'],
        ],
    ]);
    $sig = signWaveWebhook($body, $secret);

    $payload = $gw->parseWebhook($body, $sig);

    expect($payload)->not->toBeNull();
    expect($payload->event)->toBe('checkout.session.payment_failed');
    expect($payload->data['gateway_response'])->toBe('Insufficient funds');
});
