<?php

declare(strict_types=1);

use App\Services\Payment\PaystackGateway;

it('rejette un webhook sans signature', function (): void {
    $gw = new PaystackGateway(secretKey: 'sk_test_secret', publicKey: 'pk_test');
    $body = json_encode(['event' => 'charge.success', 'data' => ['reference' => 'GM-1']]);

    expect($gw->parseWebhook($body, ''))->toBeNull();
});

it('rejette un webhook à signature invalide', function (): void {
    $gw = new PaystackGateway(secretKey: 'sk_test_secret', publicKey: 'pk_test');
    $body = json_encode(['event' => 'charge.success', 'data' => ['reference' => 'GM-1']]);

    expect($gw->parseWebhook($body, 'deadbeef'))->toBeNull();
});

it('accepte un webhook avec signature HMAC SHA512 correcte', function (): void {
    $secret = 'sk_test_secret';
    $gw = new PaystackGateway(secretKey: $secret, publicKey: 'pk_test');

    $body = json_encode([
        'event' => 'charge.success',
        'data' => ['reference' => 'GM-2026-000042', 'amount' => 2_400_000, 'status' => 'success'],
    ]);
    $signature = hash_hmac('sha512', $body, $secret);

    $payload = $gw->parseWebhook($body, $signature);

    expect($payload)->not->toBeNull();
    expect($payload->event)->toBe('charge.success');
    expect($payload->reference)->toBe('GM-2026-000042');
});

it('est résilient à un webhook au JSON sans reference', function (): void {
    $secret = 'sk_test_secret';
    $gw = new PaystackGateway(secretKey: $secret, publicKey: 'pk_test');

    $body = json_encode(['event' => 'charge.success', 'data' => []]);
    $signature = hash_hmac('sha512', $body, $secret);

    expect($gw->parseWebhook($body, $signature))->toBeNull();
});

it('expose le provider code "paystack"', function (): void {
    $gw = new PaystackGateway(secretKey: 's', publicKey: 'p');
    expect($gw->providerCode())->toBe('paystack');
});
