<?php

declare(strict_types=1);

use App\Models\Advertisement;

it('active scope respects is_active and time window', function (): void {
    $now = now();

    Advertisement::create([
        'title' => 'A — live',
        'placement' => 'article_top',
        'link_url' => 'https://example.test',
        'image_url' => 'https://cdn.example.test/1.png',
        'priority' => 10,
        'is_active' => true,
    ]);
    Advertisement::create([
        'title' => 'B — inactive',
        'placement' => 'article_top',
        'link_url' => 'https://example.test',
        'image_url' => 'https://cdn.example.test/2.png',
        'priority' => 10,
        'is_active' => false,
    ]);
    Advertisement::create([
        'title' => 'C — scheduled future',
        'placement' => 'article_top',
        'link_url' => 'https://example.test',
        'image_url' => 'https://cdn.example.test/3.png',
        'priority' => 10,
        'is_active' => true,
        'starts_at' => $now->copy()->addDay(),
    ]);
    Advertisement::create([
        'title' => 'D — expired',
        'placement' => 'article_top',
        'link_url' => 'https://example.test',
        'image_url' => 'https://cdn.example.test/4.png',
        'priority' => 10,
        'is_active' => true,
        'ends_at' => $now->copy()->subDay(),
    ]);

    $titles = Advertisement::query()->active()->forPlacement('article_top')->pluck('title');
    expect($titles->all())->toBe(['A — live']);
});

it('pickForPlacement returns null when no ad active', function (): void {
    expect(Advertisement::pickForPlacement('home_sidebar'))->toBeNull();
});

it('pickForPlacement weighted rotation selects a high-priority ad more often', function (): void {
    Advertisement::create([
        'title' => 'low',
        'placement' => 'article_bottom',
        'link_url' => 'https://example.test',
        'image_url' => 'https://cdn.example.test/lo.png',
        'priority' => 1,
        'is_active' => true,
    ]);
    $high = Advertisement::create([
        'title' => 'high',
        'placement' => 'article_bottom',
        'link_url' => 'https://example.test',
        'image_url' => 'https://cdn.example.test/hi.png',
        'priority' => 99,
        'is_active' => true,
    ]);

    $hits = 0;
    for ($i = 0; $i < 100; $i++) {
        if (Advertisement::pickForPlacement('article_bottom')?->id === $high->id) {
            $hits++;
        }
    }
    // Ratio high/total attendu ~ 99/100 = 99% ; seuil prudent à 80%.
    expect($hits)->toBeGreaterThan(80);
});

it('click redirects to the target URL and increments clicks counter', function (): void {
    $ad = Advertisement::create([
        'title' => 'Click test',
        'placement' => 'article_top',
        'link_url' => 'https://orange.ci/offres',
        'image_url' => 'https://cdn.example.test/o.png',
        'priority' => 10,
        'is_active' => true,
    ]);

    $response = $this->get("/pub/{$ad->id}");
    $response->assertStatus(302);
    $response->assertRedirect('https://orange.ci/offres');

    expect($ad->fresh()->clicks)->toBe(1);
});

it('click returns 404 on inactive ad (protects cold affiliate links)', function (): void {
    $ad = Advertisement::create([
        'title' => 'Inactive',
        'placement' => 'article_top',
        'link_url' => 'https://example.test',
        'image_url' => 'https://cdn.example.test/x.png',
        'priority' => 10,
        'is_active' => false,
    ]);

    $this->get("/pub/{$ad->id}")->assertStatus(404);
});

it('trackImpression increments without triggering model events', function (): void {
    $ad = Advertisement::create([
        'title' => 'Impression',
        'placement' => 'article_top',
        'link_url' => 'https://example.test',
        'image_url' => 'https://cdn.example.test/i.png',
        'priority' => 10,
        'is_active' => true,
    ]);

    $ad->trackImpression();
    $ad->trackImpression();
    $ad->trackImpression();

    expect($ad->fresh()->impressions)->toBe(3);
});
