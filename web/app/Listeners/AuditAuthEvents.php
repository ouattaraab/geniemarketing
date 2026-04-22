<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Services\Audit;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Events\Dispatcher;

class AuditAuthEvents
{
    public function __construct(private readonly Audit $audit) {}

    public function handleLogin(Login $event): void
    {
        $this->audit->log('auth.login', $event->user);
    }

    public function handleLogout(Logout $event): void
    {
        $this->audit->log('auth.logout', $event->user);
    }

    public function handleFailed(Failed $event): void
    {
        $this->audit->log('auth.failed', null, [
            'email' => $event->credentials['email'] ?? null,
        ]);
    }

    public function handleLockout(Lockout $event): void
    {
        $this->audit->log('auth.lockout', null, [
            'ip' => $event->request->ip(),
        ]);
    }

    public function subscribe(Dispatcher $events): array
    {
        return [
            Login::class => 'handleLogin',
            Logout::class => 'handleLogout',
            Failed::class => 'handleFailed',
            Lockout::class => 'handleLockout',
        ];
    }
}
