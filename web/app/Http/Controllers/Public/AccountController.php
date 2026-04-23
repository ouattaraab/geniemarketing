<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $activeSubscription = $user->subscriptions()
            ->with('plan')
            ->active()
            ->latest('end_date')
            ->first();

        $orders = $user->orders()
            ->with(['plan', 'invoice', 'latestPayment'])
            ->latest()
            ->limit(20)
            ->get();

        // Articles achetés à l'unité (AccessRight source=purchase, non expiré).
        $purchasedArticles = $user->accessRights()
            ->with(['article.category'])
            ->where('source', 'purchase')
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->latest('granted_at')
            ->get();

        return view('public.account', [
            'user' => $user,
            'activeSubscription' => $activeSubscription,
            'orders' => $orders,
            'purchasedArticles' => $purchasedArticles,
        ]);
    }
}
