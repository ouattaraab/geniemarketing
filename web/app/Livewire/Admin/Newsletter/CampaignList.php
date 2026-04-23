<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Newsletter;

use App\Jobs\SendCampaignJob;
use App\Models\Campaign;
use App\Models\Newsletter;
use App\Models\NewsletterSubscription;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin', ['title' => 'Newsletter'])]
#[Title('Newsletter — GM Admin')]
class CampaignList extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $status = 'all';

    public function mount(): void
    {
        abort_unless(
            auth()->user()?->hasAnyRole(['chef', 'edit', 'com', 'adm', 'sup']),
            403,
        );
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function newsletters()
    {
        return Newsletter::active()->withCount(['confirmedSubscriptions'])->orderBy('name')->get();
    }

    #[Computed]
    public function kpis(): array
    {
        return [
            'subscribers' => NewsletterSubscription::query()->confirmed()->count(),
            'campaigns_sent' => Campaign::where('status', 'sent')->count(),
            'drafts' => Campaign::where('status', 'draft')->count(),
        ];
    }

    public function send(int $campaignId): void
    {
        abort_unless(auth()->user()->hasAnyRole(['chef', 'edit', 'com', 'adm', 'sup']), 403);
        $campaign = Campaign::findOrFail($campaignId);
        if (! $campaign->isDraft()) {
            session()->flash('status', 'Campagne déjà envoyée ou en cours.');

            return;
        }

        SendCampaignJob::dispatch($campaign->id);
        session()->flash('status', 'Envoi lancé en file d\'attente. Les statistiques se mettront à jour au fur et à mesure.');
    }

    public function campaigns(): LengthAwarePaginator
    {
        $query = Campaign::query()
            ->with(['newsletter', 'createdBy'])
            ->latest();

        if ($this->search !== '') {
            $query->where('subject', 'like', '%'.$this->search.'%');
        }

        if ($this->status !== 'all') {
            $query->where('status', $this->status);
        }

        return $query->paginate(15);
    }

    public function render(): View
    {
        return view('livewire.admin.newsletter.campaign-list', [
            'campaigns' => $this->campaigns(),
        ]);
    }
}
