<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Commerce;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin', ['title' => 'Abonnés'])]
#[Title('Abonnés — GM Admin')]
class SubscriptionList extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $status = 'active';

    #[Url]
    public ?int $planId = null;

    public function mount(): void
    {
        abort_unless(
            auth()->user()?->hasAnyRole(['com', 'adm', 'sup']),
            403,
        );
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function plans(): Collection
    {
        return SubscriptionPlan::orderBy('position')->get();
    }

    #[Computed]
    public function kpis(): array
    {
        return [
            'active' => Subscription::query()->active()->count(),
            'trialing' => Subscription::where('status', SubscriptionStatus::Trialing)->count(),
            'expiring_soon' => Subscription::query()
                ->active()
                ->whereBetween('end_date', [now(), now()->addDays(30)])
                ->count(),
            'mrr_cents' => Subscription::query()
                ->active()
                ->join('subscription_plans', 'subscription_plans.id', '=', 'subscriptions.subscription_plan_id')
                ->sum(\DB::raw('subscription_plans.price_cents / subscription_plans.duration_months')),
        ];
    }

    public function subscriptions(): LengthAwarePaginator
    {
        $query = Subscription::query()
            ->with(['user', 'plan'])
            ->latest('start_date');

        if ($this->search !== '') {
            $query->whereHas('user', function ($u): void {
                $u->where('email', 'like', '%'.$this->search.'%')
                    ->orWhere('first_name', 'like', '%'.$this->search.'%')
                    ->orWhere('last_name', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->status === 'active') {
            $query->whereIn('status', [SubscriptionStatus::Active, SubscriptionStatus::Trialing])
                ->where('end_date', '>=', now());
        } elseif ($this->status === 'expired') {
            $query->where(function ($q): void {
                $q->where('status', SubscriptionStatus::Expired)
                    ->orWhere('end_date', '<', now());
            });
        } elseif ($this->status === 'cancelled') {
            $query->where('status', SubscriptionStatus::Cancelled);
        }

        if ($this->planId) {
            $query->where('subscription_plan_id', $this->planId);
        }

        return $query->paginate(20);
    }

    public function render(): View
    {
        return view('livewire.admin.commerce.subscription-list', [
            'subscriptions' => $this->subscriptions(),
        ]);
    }
}
