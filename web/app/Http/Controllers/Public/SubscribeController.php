<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Contracts\View\View;

class SubscribeController extends Controller
{
    public function index(): View
    {
        $plans = SubscriptionPlan::active()
            ->orderBy('position')
            ->get();

        return view('public.subscribe', compact('plans'));
    }
}
