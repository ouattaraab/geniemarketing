<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Settings;

use App\Models\Setting;
use App\Services\Audit;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin', ['title' => 'Paramètres de la plateforme'])]
#[Title('Paramètres — GM Admin')]
class SettingsEditor extends Component
{
    /** @var array<string, mixed> */
    public array $values = [];

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['adm', 'sup']), 403);

        foreach (Setting::all() as $setting) {
            $this->values[$setting->key] = Setting::castValue($setting->value, $setting->type);
        }
    }

    #[Computed]
    public function grouped(): Collection
    {
        return Setting::orderBy('group')->orderBy('position')->get()->groupBy('group');
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['adm', 'sup']), 403);

        foreach (Setting::all() as $setting) {
            if (! array_key_exists($setting->key, $this->values)) {
                continue;
            }

            $value = $this->values[$setting->key];

            // Validation contextuelle
            if ($setting->type === 'email' && $value && ! filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $this->addError('values.'.$setting->key, 'Email invalide.');

                return;
            }
            if ($setting->type === 'url' && $value && ! preg_match('~^(https?:|/)~', (string) $value)) {
                $this->addError('values.'.$setting->key, 'URL invalide.');

                return;
            }

            Setting::put($setting->key, $value);
        }

        app(Audit::class)->log('settings.updated', null, ['keys' => array_keys($this->values)]);

        session()->flash('status', 'Paramètres enregistrés.');
    }

    public function render(): View
    {
        return view('livewire.admin.settings.settings-editor');
    }
}
