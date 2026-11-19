<?php

declare(strict_types=1);

namespace Modules\Core\Livewire;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Core\Services\UserService;
use Spatie\Permission\Models\Role;

final class UsersTable extends Component
{
    use WithPagination;

    public string $search = '';

    public string $role = '';

    public string $status = '';

    public string $sort = 'name';

    public string $direction = 'asc';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingRole(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $column): void
    {
        $this->direction = $this->sort === $column && $this->direction === 'asc' ? 'desc' : 'asc';
        $this->sort = $column;
        $this->resetPage();
    }

    public function remove(string $uuid, UserService $users): void
    {
        $user = User::query()->inCurrentCompany()->where('uuid', $uuid)->firstOrFail();
        Gate::authorize('delete', $user);
        $users->removeFromCompany($user);
        $this->dispatch('toast', message: __('User removed from the company.'), type: 'success');
    }

    public function render(): View
    {
        $query = User::query()
            ->inCurrentCompany()
            ->with('roles')
            ->when($this->search !== '', fn ($q) => $q->where(fn ($q) => $q
                ->whereLike('name', "%{$this->search}%")
                ->orWhereLike('email', "%{$this->search}%")
                ->orWhereLike('phone', "%{$this->search}%")))
            ->when($this->role !== '', fn ($q) => $q->whereHas('roles', fn ($q) => $q->where('name', $this->role)))
            ->when($this->status !== '', fn ($q) => $q->where('is_active', $this->status === 'active'))
            ->orderBy($this->sort, $this->direction);

        return view('core::livewire.users-table', [
            'users' => $query->paginate(15),
            'roles' => Role::query()->orderBy('name')->pluck('name', 'name'),
        ]);
    }
}
