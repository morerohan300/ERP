<?php

declare(strict_types=1);

namespace Modules\Core\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Core\Models\Tax;
use Modules\Core\Repositories\TaxRepository;

final class TaxesTable extends Component
{
    use WithPagination;

    public string $search = '';

    public string $scope = '';

    public string $status = '';

    public string $sort = 'name';

    public string $direction = 'asc';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingScope(): void
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

    public function remove(string $uuid, TaxRepository $taxes): void
    {
        $tax = Tax::query()->where('uuid', $uuid)->firstOrFail();
        Gate::authorize('delete', $tax);
        $taxes->delete($tax);
        $this->dispatch('toast', message: __('Tax deleted.'), type: 'success');
    }

    public function render(TaxRepository $taxes): View
    {
        $filters = ['search' => $this->search, 'sort' => ($this->direction === 'desc' ? '-' : '').$this->sort];

        if ($this->scope !== '') {
            $filters['scope'] = $this->scope;
        }

        if ($this->status !== '') {
            $filters['is_active'] = $this->status === 'active';
        }

        return view('core::livewire.taxes-table', [
            'taxes' => $taxes->paginate($filters, 15),
        ]);
    }
}
