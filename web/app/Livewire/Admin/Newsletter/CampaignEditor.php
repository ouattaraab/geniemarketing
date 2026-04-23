<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Newsletter;

use App\Jobs\SendCampaignJob;
use App\Models\Campaign;
use App\Models\Newsletter;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Campagne newsletter — GM Admin')]
class CampaignEditor extends Component
{
    public ?Campaign $campaign = null;

    public ?int $newsletterId = null;

    public string $subject = '';

    public string $preheader = '';

    public string $content = '';

    public string $ctaLabel = '';

    public string $ctaUrl = '';

    public function mount(?Campaign $campaign = null): void
    {
        abort_unless(
            auth()->user()?->hasAnyRole(['chef', 'edit', 'com', 'adm', 'sup']),
            403,
        );

        if ($campaign && $campaign->exists) {
            $this->campaign = $campaign;
            $this->newsletterId = $campaign->newsletter_id;
            $this->subject = $campaign->subject;
            $this->preheader = $campaign->preheader ?? '';
            $this->content = $campaign->content;
            $this->ctaLabel = $campaign->cta_label ?? '';
            $this->ctaUrl = $campaign->cta_url ?? '';

            return;
        }

        // Défaut : première liste
        $this->newsletterId = Newsletter::where('is_default', true)->value('id')
            ?? Newsletter::active()->orderBy('id')->value('id');
    }

    #[Computed]
    public function newsletters()
    {
        return Newsletter::active()->orderBy('name')->get();
    }

    public function save(): Campaign
    {
        $validated = $this->validate([
            'newsletterId' => ['required', 'exists:newsletters,id'],
            'subject' => ['required', 'string', 'max:200'],
            'preheader' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string', 'min:20'],
            'ctaLabel' => ['nullable', 'string', 'max:60'],
            'ctaUrl' => ['nullable', 'url:http,https', 'max:500'],
        ]);

        $data = [
            'newsletter_id' => $validated['newsletterId'],
            'subject' => $validated['subject'],
            'preheader' => $validated['preheader'] ?: null,
            'content' => $validated['content'],
            'cta_label' => $validated['ctaLabel'] ?: null,
            'cta_url' => $validated['ctaUrl'] ?: null,
        ];

        if ($this->campaign?->exists) {
            $this->campaign->update($data);
        } else {
            $data['created_by_user_id'] = auth()->id();
            $this->campaign = Campaign::create($data);
        }

        session()->flash('status', 'Campagne enregistrée.');

        return $this->campaign;
    }

    public function saveAndGo(): void
    {
        $this->save();
        $this->redirectRoute('admin.newsletters.edit', ['campaign' => $this->campaign], navigate: true);
    }

    public function sendNow(): void
    {
        $this->save();
        if (! $this->campaign->isDraft()) {
            session()->flash('status', 'Campagne déjà envoyée.');

            return;
        }

        SendCampaignJob::dispatch($this->campaign->id);
        session()->flash('status', 'Envoi lancé en file d\'attente. La page affichera les stats au fur et à mesure.');
    }

    public function render(): View
    {
        return view('livewire.admin.newsletter.campaign-editor');
    }
}
