<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Utilisateur — GM Admin')]
class UserEditor extends Component
{
    public ?User $user = null;

    public string $firstName = '';
    public string $lastName = '';
    public string $email = '';
    public string $phone = '';
    public string $role = 'red';
    public string $status = 'pending';

    public function mount(?User $user = null): void
    {
        if ($user && $user->exists) {
            abort_unless(auth()->user()?->can('update', $user), 403);
            $this->user = $user;
            $this->firstName = $user->first_name;
            $this->lastName = $user->last_name;
            $this->email = $user->email;
            $this->phone = $user->phone ?? '';
            $this->role = $user->getRoleNames()->first() ?? 'red';
            $this->status = $user->status;

            return;
        }

        abort_unless(auth()->user()?->can('create', User::class), 403);
    }

    public function save(): void
    {
        $validated = $this->validate([
            'firstName' => ['required', 'string', 'max:120'],
            'lastName' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255',
                'unique:users,email'.($this->user?->id ? ','.$this->user->id : ''),
            ],
            'phone' => ['nullable', 'string', 'max:40'],
            'role' => ['required', 'in:red,chef,edit,com,adm,sup'],
            'status' => ['required', 'in:active,inactive,pending'],
        ]);

        $data = [
            'first_name' => $validated['firstName'],
            'last_name' => $validated['lastName'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?: null,
            'type' => 'backoffice',
            'status' => $validated['status'],
        ];

        if ($this->user?->exists) {
            abort_unless(auth()->user()->can('update', $this->user), 403);
            $this->user->fill($data)->save();
            $this->user->syncRoles([$validated['role']]);

            session()->flash('status', 'Compte utilisateur mis à jour.');
        } else {
            $data['password'] = Str::random(24); // provisoire, sera reset
            $this->user = User::create($data);
            $this->user->assignRole($validated['role']);

            // Envoi d'un lien de définition du mot de passe — ne doit pas
            // casser la création si le transport mail est indispo.
            $mailSent = false;
            try {
                Password::sendResetLink(['email' => $this->user->email]);
                $mailSent = true;
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('UserEditor: activation mail failed', [
                    'user_id' => $this->user->id,
                    'email' => $this->user->email,
                    'error' => $e->getMessage(),
                ]);
            }

            session()->flash('status', $mailSent
                ? sprintf('Compte créé. Un email d\'activation a été envoyé à %s.', $this->user->email)
                : sprintf('Compte créé — l\'envoi du mail d\'activation a échoué (vérifiez la config SMTP). Renvoyez-le manuellement à %s.', $this->user->email),
            );
        }

        $this->redirectRoute('admin.users.edit', ['user' => $this->user], navigate: true);
    }

    public function render(): View
    {
        return view('livewire.admin.users.user-editor');
    }
}
