<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\ArticleStatus;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Comment;
use App\Models\Order;
use App\Models\Subscription;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $since = now()->subDays(30);

        $metrics = [
            [
                'label' => 'Abonnés actifs',
                'value' => (string) Subscription::query()->active()->count(),
                'hint' => 'Trialing + Active, end_date future',
            ],
            [
                'label' => 'Revenus 30 jours',
                'value' => number_format(
                    (Order::where('status', OrderStatus::Paid)
                        ->where('paid_at', '>=', $since)
                        ->sum('total_cents')) / 100,
                    0, ',', ' '
                ).' FCFA',
                'hint' => Order::where('status', OrderStatus::Paid)
                    ->where('paid_at', '>=', $since)->count().' commandes payées',
            ],
            [
                'label' => 'Articles publiés 30j',
                'value' => (string) Article::where('status', ArticleStatus::Published)
                    ->where('published_at', '>=', $since)
                    ->count(),
                'hint' => 'Cadence visée : 8 à 12/semaine',
            ],
            [
                'label' => 'Vues articles 30j',
                'value' => number_format(
                    Article::where('published_at', '>=', $since)->sum('views_count'),
                    0, ',', ' '
                ),
                'hint' => 'Cumul des vues dédoublonnées',
            ],
        ];

        $topArticles = Article::query()
            ->where('status', ArticleStatus::Published)
            ->where('published_at', '>=', $since)
            ->with('category')
            ->orderByDesc('views_count')
            ->limit(5)
            ->get();

        $recentOrders = Order::with(['user', 'plan'])
            ->latest()
            ->limit(5)
            ->get();

        $pendingComments = Comment::where('status', 'pending')->count();

        // Série revenus quotidiens des 30 derniers jours (en FCFA, pour sparkline)
        $dailyRevenueRaw = Order::query()
            ->select(DB::raw('DATE(paid_at) as d'), DB::raw('SUM(total_cents) / 100 as total'))
            ->where('status', OrderStatus::Paid)
            ->where('paid_at', '>=', $since)
            ->groupBy('d')
            ->orderBy('d')
            ->pluck('total', 'd');

        $revenueSeries = [];
        foreach (range(29, 0) as $i) {
            $date = now()->subDays($i)->toDateString();
            $revenueSeries[] = [
                'date' => $date,
                'value' => (int) ($dailyRevenueRaw[$date] ?? 0),
            ];
        }

        return view('admin.dashboard', compact(
            'metrics',
            'topArticles',
            'recentOrders',
            'pendingComments',
            'revenueSeries',
        ));
    }
}
