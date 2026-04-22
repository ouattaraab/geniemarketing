<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Simulateur de hosted checkout — stand-in local pour Paystack.
 * Uniquement monté hors production (cf. routes/web.php).
 */
class CheckoutSimulatorController extends Controller
{
    public function show(Request $request, string $reference): View
    {
        abort_if(app()->environment('production'), 404);

        $order = Order::where('reference', $reference)->firstOrFail();

        return view('public.checkout-simulator', [
            'order' => $order,
            'reference' => $reference,
            'callback' => $this->safeCallback($request->query('callback')),
        ]);
    }

    public function simulate(Request $request, string $reference): RedirectResponse
    {
        abort_if(app()->environment('production'), 404);

        $outcome = $request->input('outcome', 'success');
        $outcome = in_array($outcome, ['success', 'failed', 'abandoned'], true) ? $outcome : 'success';

        // Mémorise le choix pour que FakePaymentGateway::verify() puisse le relire
        Cache::put('fake-checkout:'.$reference, $outcome, now()->addMinutes(15));

        $callback = $this->safeCallback($request->input('callback'));

        return redirect()->to($callback.'?reference='.urlencode($reference).'&status='.$outcome);
    }

    /**
     * N'accepte que des callbacks qui pointent vers notre propre domaine — tout
     * autre host retombe sur la route checkout.callback standard.
     */
    private function safeCallback(mixed $raw): string
    {
        $default = route('checkout.callback');
        if (! is_string($raw) || $raw === '') {
            return $default;
        }

        $parsed = parse_url($raw);
        if (! is_array($parsed) || ! isset($parsed['host'])) {
            return $default;
        }

        if ($parsed['host'] !== request()->getHost()) {
            return $default;
        }

        return $raw;
    }
}
