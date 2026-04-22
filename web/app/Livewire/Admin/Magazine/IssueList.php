<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Magazine;

use App\Models\MagazineIssue;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin', ['title' => 'Numéros magazine'])]
#[Title('Magazines — GM Admin')]
class IssueList extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $status = 'all';

    public function mount(): void
    {
        abort_unless(
            auth()->user()?->hasAnyRole(['chef', 'edit', 'adm', 'sup']),
            403,
        );
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function issues(): LengthAwarePaginator
    {
        $query = MagazineIssue::query()
            ->with('cover')
            ->orderByDesc('number');

        if ($this->search !== '') {
            $query->where(function ($q): void {
                $q->where('title', 'like', '%'.$this->search.'%')
                    ->orWhere('theme', 'like', '%'.$this->search.'%')
                    ->orWhere('number', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->status !== 'all') {
            $query->where('status', $this->status);
        }

        return $query->paginate(12);
    }

    public function render(): View
    {
        return view('livewire.admin.magazine.issue-list', [
            'issues' => $this->issues(),
        ]);
    }
}
