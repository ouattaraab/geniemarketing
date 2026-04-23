<?php

declare(strict_types=1);

use App\Services\FreemiumCounter;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Http\Request;

function makeCounter(int $limit = 3): FreemiumCounter
{
    return new FreemiumCounter(
        cache: new CacheRepository(new ArrayStore),
        monthlyLimit: $limit,
    );
}

function anonRequest(string $ip = '10.0.0.1', string $ua = 'PestBot/1.0'): Request
{
    return Request::create(
        uri: '/articles/foo',
        method: 'GET',
        server: ['REMOTE_ADDR' => $ip, 'HTTP_USER_AGENT' => $ua],
    );
}

it('expose la limite configurée', function (): void {
    expect(makeCounter(5)->limit())->toBe(5);
});

it('démarre à zéro vue et à quota complet', function (): void {
    $c = makeCounter();
    $req = anonRequest();

    expect($c->viewsThisMonth($req))->toBe(0);
    expect($c->remaining($req))->toBe(3);
    expect($c->hasRemainingQuota($req))->toBeTrue();
});

it('incrémente et dédoublonne par article_id', function (): void {
    $c = makeCounter();
    $req = anonRequest();

    $c->recordView($req, 10);
    $c->recordView($req, 20);
    $c->recordView($req, 10); // doublon ignoré

    expect($c->viewsThisMonth($req))->toBe(2);
    expect($c->remaining($req))->toBe(1);
});

it('déclenche le paywall au-delà de la limite', function (): void {
    $c = makeCounter();
    $req = anonRequest();

    foreach (range(1, 3) as $id) {
        $c->recordView($req, $id);
    }

    expect($c->hasRemainingQuota($req))->toBeFalse();
    expect($c->remaining($req))->toBe(0);
});

it('isole les compteurs entre IPs distinctes', function (): void {
    $c = makeCounter();
    $req1 = anonRequest(ip: '1.1.1.1');
    $req2 = anonRequest(ip: '2.2.2.2');

    $c->recordView($req1, 100);
    $c->recordView($req1, 101);

    expect($c->viewsThisMonth($req1))->toBe(2);
    expect($c->viewsThisMonth($req2))->toBe(0);
});
