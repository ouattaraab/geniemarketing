<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Moderation;

use App\Models\Comment;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin', ['title' => 'Modération des commentaires'])]
#[Title('Commentaires — GM Admin')]
class CommentList extends Component
{
    use WithPagination;

    #[Url]
    public string $status = 'pending';

    #[Url(as: 'q')]
    public string $search = '';

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

    public function approve(int $commentId): void
    {
        $comment = Comment::findOrFail($commentId);
        $comment->update([
            'status' => 'approved',
            'moderated_by_user_id' => auth()->id(),
            'moderated_at' => now(),
        ]);
        session()->flash('status', 'Commentaire approuvé.');
    }

    public function reject(int $commentId): void
    {
        $comment = Comment::findOrFail($commentId);
        $comment->update([
            'status' => 'rejected',
            'moderated_by_user_id' => auth()->id(),
            'moderated_at' => now(),
        ]);
        session()->flash('status', 'Commentaire rejeté.');
    }

    #[Computed]
    public function counts(): array
    {
        return [
            'pending' => Comment::where('status', 'pending')->count(),
            'approved' => Comment::where('status', 'approved')->count(),
            'rejected' => Comment::where('status', 'rejected')->count(),
        ];
    }

    public function comments(): LengthAwarePaginator
    {
        $query = Comment::query()
            ->with(['user', 'article'])
            ->latest();

        if ($this->status !== 'all') {
            $query->where('status', $this->status);
        }

        if ($this->search !== '') {
            $query->where(function ($q): void {
                $q->where('content', 'like', '%'.$this->search.'%')
                    ->orWhereHas('user', fn ($u) => $u->where('email', 'like', '%'.$this->search.'%'));
            });
        }

        return $query->paginate(20);
    }

    public function render(): View
    {
        return view('livewire.admin.moderation.comment-list', [
            'comments' => $this->comments(),
        ]);
    }
}
