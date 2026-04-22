<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Articles;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin', ['title' => 'Articles'])]
#[Title('Articles — GM Admin')]
class ArticleList extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $status = 'all';

    #[Url]
    public ?int $categoryId = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingCategoryId(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function categories(): Collection
    {
        return Category::roots()->orderBy('position')->get();
    }

    #[Computed]
    public function statusCounts(): array
    {
        $counts = Article::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $counts['all'] = array_sum($counts);

        return $counts;
    }

    public function articles(): LengthAwarePaginator
    {
        $user = auth()->user();

        $query = Article::query()
            ->with(['category', 'authors', 'createdBy'])
            ->latest('updated_at');

        // Un rédacteur ne voit que ses articles.
        if ($user && $user->hasRole('red') && ! $user->hasAnyRole(['chef', 'edit', 'sup'])) {
            $query->where('created_by_user_id', $user->id);
        }

        if ($this->search !== '') {
            $query->where(function ($q): void {
                $q->where('title', 'like', '%'.$this->search.'%')
                    ->orWhere('lede', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->status !== 'all') {
            $query->where('status', $this->status);
        }

        if ($this->categoryId) {
            $query->where('category_id', $this->categoryId);
        }

        return $query->paginate(15);
    }

    public function render(): View
    {
        return view('livewire.admin.articles.article-list', [
            'articles' => $this->articles(),
            'statuses' => ArticleStatus::cases(),
        ]);
    }
}
