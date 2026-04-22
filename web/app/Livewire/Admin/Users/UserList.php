<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin', ['title' => 'Utilisateurs backoffice'])]
#[Title('Utilisateurs BO — GM Admin')]
class UserList extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $role = 'all';

    #[Url]
    public string $status = 'all';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('viewAny', User::class), 403);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function toggleStatus(int $userId): void
    {
        $target = User::findOrFail($userId);
        abort_unless(auth()->user()->can('delete', $target), 403);

        $target->status = $target->status === 'active' ? 'inactive' : 'active';
        $target->save();

        session()->flash('status', sprintf(
            'Compte %s pour %s.',
            $target->status === 'active' ? 'réactivé' : 'désactivé',
            $target->email,
        ));
    }

    public function users(): LengthAwarePaginator
    {
        $query = User::query()
            ->with('roles')
            ->where('type', 'backoffice')
            ->orderBy('last_name');

        if ($this->search !== '') {
            $query->where(function ($q): void {
                $q->where('first_name', 'like', '%'.$this->search.'%')
                    ->orWhere('last_name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->role !== 'all') {
            $query->whereHas('roles', fn ($q) => $q->where('name', $this->role));
        }

        if ($this->status !== 'all') {
            $query->where('status', $this->status);
        }

        return $query->paginate(15);
    }

    public function render(): View
    {
        return view('livewire.admin.users.user-list', [
            'users' => $this->users(),
        ]);
    }
}
