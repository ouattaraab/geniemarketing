<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\SubscriptionPlan;
use Illuminate\Contracts\View\View;

class SubscribeController extends Controller
{
    public function index(): View
    {
        $plans = SubscriptionPlan::active()
            ->orderBy('position')
            ->get();

        // Masque le tunnel si aucun moyen de paiement activé côté admin.
        $paymentEnabled = PaymentMethod::enabledCount() > 0;

        return view('public.subscribe', compact('plans', 'paymentEnabled'));
    }
}
