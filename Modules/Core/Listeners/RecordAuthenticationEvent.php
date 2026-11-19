<?php

declare(strict_types=1);

namespace Modules\Core\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Http\Request;
use Modules\Core\Models\LoginHistory;

/**
 * Writes login, logout and failed-login events to login_histories and
 * stamps users.last_login_at.
 */
class RecordAuthenticationEvent
{
    public function __construct(private readonly Request $request) {}

    public function handleLogin(Login $event): void
    {
        if ($event->user instanceof User) {
            $event->user->forceFill(['last_login_at' => now()])->saveQuietly();
        }

        $this->record($event->user?->getAuthIdentifier(), $event->user?->email ?? null, true, 'login');
    }

    public function handleLogout(Logout $event): void
    {
        if ($event->user !== null) {
            $this->record($event->user->getAuthIdentifier(), $event->user->email ?? null, true, 'logout');
        }
    }

    public function handleFailed(Failed $event): void
    {
        $this->record(
            $event->user?->getAuthIdentifier(),
            $event->credentials['email'] ?? null,
            false,
            'failed',
        );
    }

    /** @return array<class-string, string> */
    public function subscribe(): array
    {
        return [
            Login::class => 'handleLogin',
            Logout::class => 'handleLogout',
            Failed::class => 'handleFailed',
        ];
    }

    private function record(int|string|null $userId, ?string $email, bool $successful, string $event): void
    {
        LoginHistory::query()->create([
            'user_id' => $userId,
            'email' => $email,
            'ip_address' => $this->request->ip(),
            'user_agent' => mb_substr((string) $this->request->userAgent(), 0, 512),
            'successful' => $successful,
            'event' => $event,
            'occurred_at' => now(),
        ]);
    }
}
