<?php

declare(strict_types=1);

namespace Modules\Core\Livewire;

use App\Support\Tenancy\CurrentCompany;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

final class NotificationsDropdown extends Component
{
    public function markRead(string $id): void
    {
        $this->notificationQuery()->where('id', $id)->update(['read_at' => now()]);
    }

    public function markAllRead(): void
    {
        $this->notificationQuery()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function render(): View
    {
        $companyId = app(CurrentCompany::class)->id();
        $rows = $this->notificationQuery()
            ->latest()
            ->limit(50)
            ->get(['id', 'data', 'read_at', 'created_at'])
            ->map(function ($row) {
                $data = json_decode((string) $row->data, true) ?: [];

                return [
                    'id' => $row->id,
                    'data' => $data,
                    'read' => $row->read_at !== null,
                    'created_at' => $row->created_at,
                ];
            })
            ->filter(fn (array $row) => $companyId === null
                || ! array_key_exists('company_id', $row['data'])
                || (int) $row['data']['company_id'] === $companyId);

        $notifications = $rows->take(8)->map(fn (array $row) => [
            'id' => $row['id'],
            'title' => $row['data']['title'] ?? __('Notification'),
            'body' => $row['data']['body'] ?? '',
            'url' => $row['data']['url'] ?? null,
            'icon' => $row['data']['icon'] ?? null,
            'read' => $row['read'],
            'created_at' => $row['created_at'],
        ])->values();

        return view('core::livewire.notifications-dropdown', [
            'notifications' => $notifications,
            'unread' => $rows->filter(fn (array $row) => ! $row['read'])->count(),
        ]);
    }

    private function notificationQuery(): Builder
    {
        $user = auth()->user();

        return DB::table('notifications')
            ->where('notifiable_type', $user->getMorphClass())
            ->where('notifiable_id', (string) $user->getKey());
    }
}
