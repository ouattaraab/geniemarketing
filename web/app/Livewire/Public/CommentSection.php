<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use App\Models\Article;
use App\Models\Comment;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;

class CommentSection extends Component
{
    public Article $article;

    #[Validate('required|string|min:3|max:2000')]
    public string $newComment = '';

    public function mount(Article $article): void
    {
        $this->article = $article;
    }

    #[Computed]
    public function comments(): Collection
    {
        return Comment::query()
            ->where('article_id', $this->article->id)
            ->whereNull('parent_id')
            ->approved()
            ->with(['user', 'replies.user'])
            ->latest()
            ->get();
    }

    public function submit(): void
    {
        $user = auth()->user();
        if ($user === null) {
            $this->redirectRoute('login');

            return;
        }

        // Seuls les abonnés + BO peuvent commenter (US-121)
        if (! $user->hasActiveSubscription() && ! $user->hasAnyRole(['red', 'chef', 'edit', 'adm', 'sup'])) {
            $this->addError('newComment', 'Seuls les abonnés peuvent laisser un commentaire.');

            return;
        }

        $this->validate();

        Comment::create([
            'article_id' => $this->article->id,
            'user_id' => $user->id,
            'content' => $this->newComment,
            'status' => 'pending',       // modération systématique
            'ip' => request()->ip(),
        ]);

        // Rafraîchit le badge sidebar côté BO
        Cache::forget('admin.comments.pending_count');

        $this->newComment = '';
        session()->flash('comment-status', 'Votre commentaire est en attente de modération. Merci de votre contribution.');
    }

    public function render(): View
    {
        return view('livewire.public.comment-section');
    }
}
