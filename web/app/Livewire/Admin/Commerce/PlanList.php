<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Commerce;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Services\Audit;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin', ['title' => 'Formules d\'abonnement'])]
#[Title('Formules — GM Admin')]
class PlanList extends Component
{
    public function mount(): void
    {
        abort_unless(
            auth()->user()?->hasAnyRole(['com', 'adm', 'sup']),
            403,
        );
    }

    public function toggleActive(int $id): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['com', 'adm', 'sup']), 403);

        $plan = SubscriptionPlan::findOrFail($id);
        $plan->is_active = ! $plan->is_active;
        $plan->save();

        app(Audit::class)->log('plan.toggle_active', $plan, [
            'code' => $plan->code,
            'is_active' => $plan->is_active,
        ]);

        session()->flash('status', sprintf(
            'Formule « %s » %s.',
            $plan->name,
            $plan->is_active ? 'activée' : 'désactivée',
        ));
    }

    public function delete(int $id): void
    {
        // Seul sup peut supprimer (action destructive, irréversible par UI).
        abort_unless(auth()->user()?->hasRole('sup'), 403);

        $plan = SubscriptionPlan::findOrFail($id);

        // Refuse la suppression si le plan a des abonnements attachés.
        $hasSubscriptions = Subscription::where('subscription_plan_id', $plan->id)->exists();
        if ($hasSubscriptions) {
            session()->flash('status', 'Impossible de supprimer : cette formule est liée à des abonnements. Désactivez-la plutôt.');

            return;
        }

        $code = $plan->code;
        $plan->delete(); // SoftDelete

        app(Audit::class)->log('plan.deleted', null, ['code' => $code]);

        session()->flash('status', "Formule « $code » supprimée.");
    }

    public function render(): View
    {
        return view('livewire.admin.commerce.plan-list', [
            'plans' => SubscriptionPlan::withCount(['subscriptions'])->orderBy('position')->get(),
        ]);
    }
}
