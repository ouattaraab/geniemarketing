<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Commerce;

use App\Models\SubscriptionPlan;
use App\Services\Audit;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin', ['title' => 'Formule d\'abonnement'])]
#[Title('Formule — GM Admin')]
class PlanEditor extends Component
{
    public ?int $planId = null;

    public string $code = '';
    public string $name = '';
    public string $description = '';
    public int $price = 0;              // en unité principale (ex. 24000 XOF)
    public string $currency = 'XOF';
    public int $duration_months = 12;
    public int $trial_days = 0;
    public int $licenses_included = 1;
    public string $featuresText = '';   // une feature par ligne
    public bool $is_active = true;
    public bool $is_featured = false;
    public int $position = 0;

    public function mount(?SubscriptionPlan $plan = null): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['com', 'adm', 'sup']), 403);

        if ($plan && $plan->exists) {
            $this->planId = $plan->id;
            $this->code = (string) $plan->code;
            $this->name = (string) $plan->name;
            $this->description = (string) $plan->description;
            $this->price = (int) round($plan->price_cents / 100);
            $this->currency = (string) $plan->currency;
            $this->duration_months = (int) $plan->duration_months;
            $this->trial_days = (int) $plan->trial_days;
            $this->licenses_included = (int) $plan->licenses_included;
            $this->featuresText = is_array($plan->features) ? implode("\n", $plan->features) : '';
            $this->is_active = (bool) $plan->is_active;
            $this->is_featured = (bool) $plan->is_featured;
            $this->position = (int) $plan->position;
        } else {
            $this->position = (int) (SubscriptionPlan::max('position') ?? 0) + 1;
        }
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    protected function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:40',
                'regex:/^[a-z0-9\-_]+$/',
                Rule::unique('subscription_plans', 'code')->ignore($this->planId)->whereNull('deleted_at'),
            ],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'integer', 'min:0', 'max:10000000'],
            'currency' => ['required', 'string', 'size:3'],
            'duration_months' => ['required', 'integer', 'min:1', 'max:60'],
            'trial_days' => ['required', 'integer', 'min:0', 'max:365'],
            'licenses_included' => ['required', 'integer', 'min:1', 'max:10000'],
            'featuresText' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
            'position' => ['required', 'integer', 'min:0', 'max:999'],
        ];
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['com', 'adm', 'sup']), 403);

        $data = $this->validate();

        $features = collect(preg_split('/\r\n|\r|\n/', $data['featuresText'] ?? ''))
            ->map(fn ($line) => trim((string) $line))
            ->filter()
            ->values()
            ->all();

        $payload = [
            'code' => strtolower($data['code']),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price_cents' => $data['price'] * 100,
            'currency' => strtoupper($data['currency']),
            'duration_months' => $data['duration_months'],
            'trial_days' => $data['trial_days'],
            'licenses_included' => $data['licenses_included'],
            'features' => $features,
            'is_active' => $data['is_active'] ?? false,
            'is_featured' => $data['is_featured'] ?? false,
            'position' => $data['position'],
        ];

        if ($this->planId) {
            $plan = SubscriptionPlan::findOrFail($this->planId);
            // M3 — code plan immuable après création, même si un rôle `com`
            // tente de le modifier via un POST Livewire forgé. Le readonly
            // UI est insuffisant — on ré-affirme côté serveur.
            $payload['code'] = $plan->code;
            $plan->update($payload);
            app(Audit::class)->log('plan.updated', $plan, ['code' => $plan->code]);
        } else {
            $plan = SubscriptionPlan::create($payload);
            $this->planId = $plan->id;
            app(Audit::class)->log('plan.created', $plan, ['code' => $plan->code]);
        }

        session()->flash('status', 'Formule enregistrée.');
        $this->redirect(route('admin.plans.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.admin.commerce.plan-editor');
    }
}
