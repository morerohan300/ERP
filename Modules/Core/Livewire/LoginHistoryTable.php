<?php

declare(strict_types=1);

namespace Modules\Core\Livewire;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Core\Models\LoginHistory;

final class LoginHistoryTable extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status = '';

    public string $event = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingEvent(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $memberIds = User::query()->inCurrentCompany()->pluck('id');

        $query = LoginHistory::query()
            ->whereIn('user_id', $memberIds)
            ->with('user')
            ->when($this->search !== '', fn ($q) => $q->where(fn ($q) => $q
                ->whereLike('email', "%{$this->search}%")
                ->orWhereLike('ip_address', "%{$this->search}%")
                ->orWhereHas('user', fn ($q) => $q->whereLike('name', "%{$this->search}%"))))
            ->when($this->status !== '', fn ($q) => $q->where('successful', $this->status === 'successful'))
            ->when($this->event !== '', fn ($q) => $q->where('event', $this->event))
            ->latest('occurred_at');

        return view('core::livewire.login-history-table', [
            'logins' => $query->paginate(20),
        ]);
    }
}
