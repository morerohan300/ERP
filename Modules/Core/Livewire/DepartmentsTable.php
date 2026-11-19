<?php

declare(strict_types=1);

namespace Modules\Core\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Core\Models\Branch;
use Modules\Core\Models\Department;
use Modules\Core\Repositories\DepartmentRepository;

final class DepartmentsTable extends Component
{
    use WithPagination;

    public string $search = '';

    public string $branchId = '';

    public string $sort = 'name';

    public string $direction = 'asc';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingBranchId(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $column): void
    {
        $this->direction = $this->sort === $column && $this->direction === 'asc' ? 'desc' : 'asc';
        $this->sort = $column;
        $this->resetPage();
    }

    public function remove(string $uuid, DepartmentRepository $departments): void
    {
        $department = Department::query()->where('uuid', $uuid)->firstOrFail();
        Gate::authorize('delete', $department);
        $departments->delete($department);
        $this->dispatch('toast', message: __('Department deleted.'), type: 'success');
    }

    public function render(DepartmentRepository $departments): View
    {
        $filters = ['search' => $this->search, 'sort' => ($this->direction === 'desc' ? '-' : '').$this->sort];

        if ($this->branchId !== '') {
            $filters['branch_id'] = $this->branchId;
        }

        return view('core::livewire.departments-table', [
            'departments' => $departments->paginate($filters, 15),
            'branches' => Branch::query()->orderBy('name')->pluck('name', 'id'),
        ]);
    }
}
