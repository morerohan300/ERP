<?php

declare(strict_types=1);

namespace Modules\Core\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Core\Models\Branch;
use Modules\Core\Repositories\BranchRepository;

final class BranchesTable extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status = '';

    public string $sort = 'name';

    public string $direction = 'asc';

    public function updatingSearch(): void
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

    public function remove(string $uuid, BranchRepository $branches): void
    {
        $branch = Branch::query()->where('uuid', $uuid)->firstOrFail();
        Gate::authorize('delete', $branch);
        $branches->delete($branch);
        $this->dispatch('toast', message: __('Branch deleted.'), type: 'success');
    }

    public function render(BranchRepository $branches): View
    {
        $filters = ['search' => $this->search, 'sort' => ($this->direction === 'desc' ? '-' : '').$this->sort];

        if ($this->status !== '') {
            $filters['is_active'] = $this->status === 'active';
        }

        return view('core::livewire.branches-table', [
            'branches' => $branches->paginate($filters, 15),
        ]);
    }
}
