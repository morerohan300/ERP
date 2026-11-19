<?php

declare(strict_types=1);

namespace Modules\Core\Livewire;

use App\Support\Tenancy\CurrentCompany;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

final class AuditLogTable extends Component
{
    use WithPagination;

    public string $search = '';

    public string $event = '';

    public string $from = '';

    public string $to = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingEvent(): void
    {
        $this->resetPage();
    }

    public function updatingFrom(): void
    {
        $this->resetPage();
    }

    public function updatingTo(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $query = DB::table('activity_log')
            ->when(app(CurrentCompany::class)->id(), fn ($q, $id) => $q->where('company_id', $id))
            ->when($this->search !== '', fn ($q) => $q->where(fn ($q) => $q
                ->whereLike('description', "%{$this->search}%")
                ->orWhereLike('event', "%{$this->search}%")
                ->orWhereLike('log_name', "%{$this->search}%")))
            ->when($this->event !== '', fn ($q) => $q->where('event', $this->event))
            ->when($this->from !== '', fn ($q) => $q->whereDate('created_at', '>=', $this->from))
            ->when($this->to !== '', fn ($q) => $q->whereDate('created_at', '<=', $this->to))
            ->latest('created_at')
            ->orderByDesc('id');

        /** @var LengthAwarePaginator $logs */
        $logs = $query->paginate(20);

        return view('core::livewire.audit-log-table', ['logs' => $logs]);
    }
}
