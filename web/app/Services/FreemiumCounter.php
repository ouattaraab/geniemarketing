<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\Request;

/**
 * Compteur freemium (EP-09) : un visiteur non abonné peut lire N articles
 * premium par mois. Au-delà, paywall plein.
 *
 * Clé de suivi :
 *  - utilisateur connecté : user:{id}
 *  - visiteur anonyme : ip:{hash(ip)}:ua:{hash(ua)} — sans cookie (conformité CNIL/ARTCI)
 * Fenêtre : mois civil (reset le 1er du mois).
 */
class FreemiumCounter
{
    public function __construct(
        private readonly Repository $cache,
        private readonly int $monthlyLimit = 3,
    ) {}

    public function hasRemainingQuota(Request $request): bool
    {
        return $this->viewsThisMonth($request) < $this->monthlyLimit;
    }

    public function remaining(Request $request): int
    {
        return max(0, $this->monthlyLimit - $this->viewsThisMonth($request));
    }

    public function limit(): int
    {
        return $this->monthlyLimit;
    }

    public function recordView(Request $request, int $articleId): void
    {
        $key = $this->cacheKey($request);
        /** @var array<int, int> $viewed */
        $viewed = (array) $this->cache->get($key, []);

        if (in_array($articleId, $viewed, true)) {
            return; // déjà compté pour ce mois
        }

        $viewed[] = $articleId;
        $this->cache->put($key, $viewed, now()->endOfMonth());
    }

    public function viewsThisMonth(Request $request): int
    {
        $key = $this->cacheKey($request);

        return count((array) $this->cache->get($key, []));
    }

    private function cacheKey(Request $request): string
    {
        $user = $request->user();
        $month = now()->format('Y-m');

        if ($user !== null) {
            return sprintf('freemium:%s:user:%d', $month, $user->id);
        }

        $fingerprint = hash('sha256', sprintf(
            '%s|%s',
            $request->ip() ?? '0.0.0.0',
            $request->userAgent() ?? '',
        ));

        return sprintf('freemium:%s:anon:%s', $month, $fingerprint);
    }
}
