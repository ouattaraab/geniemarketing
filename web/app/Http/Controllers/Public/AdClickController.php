<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Endpoint de tracking des clics publicitaires.
 * Incrémente le compteur `clicks` puis redirige vers l'URL cible.
 *
 * Le redirect est 302 (et non 301) pour permettre la rotation de l'URL
 * côté admin sans casser les caches navigateur anciens. La cible est
 * validée par l'absence de manipulation côté client (URL stockée en BDD).
 */
class AdClickController extends Controller
{
    public function __invoke(Request $request, Advertisement $advertisement): RedirectResponse
    {
        // Refuse de rediriger vers une bannière désactivée / expirée / inexistante.
        if (! $advertisement->is_active) {
            abort(404);
        }

        $advertisement->trackClick();

        // Ne loggue pas l'IP individuelle — respect vie privée. La métrique
        // est agrégée (compteur clicks uniquement).

        return redirect()->away($advertisement->link_url, 302);
    }
}
