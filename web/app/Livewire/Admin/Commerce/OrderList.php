<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Commerce;

use App\Enums\OrderStatus;
use App\Models\Order;
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

#[Layout('components.layouts.admin', ['title' => 'Commandes'])]
#[Title('Commandes — GM Admin')]
class OrderList extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $status = 'all';

    #[Url]
    public ?int $planId = null;

    public function mount(): void
    {
        abort_unless(
            auth()->user()?->hasAnyRole(['com', 'adm', 'sup', 'chef', 'edit']),
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
        $totals = Order::query()
            ->selectRaw("
                SUM(CASE WHEN status = 'paid' THEN total_cents ELSE 0 END) as revenue_cents,
                SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid_count,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_count
            ")
            ->first()
            ?->toArray() ?? [];

        return [
            'revenue' => (int) ($totals['revenue_cents'] ?? 0),
            'paid' => (int) ($totals['paid_count'] ?? 0),
            'pending' => (int) ($totals['pending_count'] ?? 0),
            'failed' => (int) ($totals['failed_count'] ?? 0),
        ];
    }

    public function orders(): LengthAwarePaginator
    {
        $query = Order::query()
            ->with(['user', 'plan', 'latestPayment'])
            ->latest();

        if ($this->search !== '') {
            $query->where(function ($q): void {
                $q->where('reference', 'like', '%'.$this->search.'%')
                    ->orWhereHas('user', function ($u): void {
                        $u->where('email', 'like', '%'.$this->search.'%')
                            ->orWhere('first_name', 'like', '%'.$this->search.'%')
                            ->orWhere('last_name', 'like', '%'.$this->search.'%');
                    });
            });
        }

        if ($this->status !== 'all') {
            $query->where('status', $this->status);
        }

        if ($this->planId) {
            $query->where('subscription_plan_id', $this->planId);
        }

        return $query->paginate(20);
    }

    public function render(): View
    {
        return view('livewire.admin.commerce.order-list', [
            'orders' => $this->orders(),
            'statuses' => OrderStatus::cases(),
        ]);
    }
}
