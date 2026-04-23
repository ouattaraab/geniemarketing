<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Settings;

use App\Models\PaymentMethod;
use App\Services\Audit;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin', ['title' => 'Moyens de paiement'])]
#[Title('Moyens de paiement — GM Admin')]
class PaymentMethodsList extends Component
{
    public function mount(): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['adm', 'sup']), 403);
    }

    public function toggle(int $id): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['adm', 'sup']), 403);

        $method = PaymentMethod::findOrFail($id);
        $method->is_enabled = ! $method->is_enabled;
        $method->save();

        app(Audit::class)->log('payment_method.toggled', $method, [
            'code' => $method->code,
            'is_enabled' => $method->is_enabled,
        ]);

        session()->flash('status', sprintf(
            '%s %s pour les utilisateurs.',
            $method->name,
            $method->is_enabled ? 'activé' : 'désactivé',
        ));
    }

    public function render(): View
    {
        return view('livewire.admin.settings.payment-methods-list', [
            'methods' => PaymentMethod::orderBy('position')->get(),
        ]);
    }
}
