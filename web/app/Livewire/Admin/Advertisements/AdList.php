<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Advertisements;

use App\Models\Advertisement;
use App\Services\Audit;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin', ['title' => 'Bannières publicitaires'])]
#[Title('Bannières — GM Admin')]
class AdList extends Component
{
    use WithPagination;

    #[Url(as: 'p')]
    public string $placement = '';

    #[Url]
    public string $status = 'all';  // all | active | inactive | scheduled | expired

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

        $ad = Advertisement::findOrFail($id);
        $ad->is_active = ! $ad->is_active;
        $ad->save();

        app(Audit::class)->log('ad.toggle_active', $ad, [
            'is_active' => $ad->is_active,
        ]);

        session()->flash('status', sprintf(
            'Bannière « %s » %s.',
            $ad->title,
            $ad->is_active ? 'activée' : 'désactivée',
        ));
    }

    public function delete(int $id): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['adm', 'sup']), 403);

        $ad = Advertisement::findOrFail($id);
        $title = $ad->title;
        $ad->delete(); // SoftDelete

        app(Audit::class)->log('ad.deleted', null, ['title' => $title]);

        session()->flash('status', "Bannière « $title » supprimée.");
    }

    private function ads(): LengthAwarePaginator
    {
        $query = Advertisement::query()
            ->with('media')
            ->latest('id');

        if ($this->placement !== '') {
            $query->where('placement', $this->placement);
        }

        $now = now();
        match ($this->status) {
            'active' => $query->where('is_active', true)
                ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
                ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now)),
            'inactive' => $query->where('is_active', false),
            'scheduled' => $query->where('is_active', true)->where('starts_at', '>', $now),
            'expired' => $query->where('ends_at', '<', $now),
            default => null,
        };

        return $query->paginate(20);
    }

    public function render(): View
    {
        return view('livewire.admin.advertisements.ad-list', [
            'ads' => $this->ads(),
            'placements' => [
                '' => 'Tous les placements',
                Advertisement::PLACEMENT_ARTICLE_TOP => 'Article — haut',
                Advertisement::PLACEMENT_ARTICLE_BOTTOM => 'Article — bas',
                Advertisement::PLACEMENT_ARTICLE_SIDEBAR => 'Article — colonne',
                Advertisement::PLACEMENT_HOME_LEADERBOARD => 'Accueil — bandeau',
                Advertisement::PLACEMENT_HOME_SIDEBAR => 'Accueil — colonne',
            ],
        ]);
    }
}
