<?php

declare(strict_types=1);

namespace Modules\Core\Providers;

use App\Models\User;
use App\Support\Modules\ModuleServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Event;
use Modules\Core\Console\BackupCommand;
use Modules\Core\Console\SyncPermissionsCommand;
use Modules\Core\Listeners\RecordAuthenticationEvent;
use Modules\Core\Livewire;
use Modules\Core\Models;
use Modules\Core\Policies;
use Modules\Core\Services\CustomFieldService;
use Modules\Core\Services\GlobalSearch;
use Spatie\Permission\Models\Role;

class CoreServiceProvider extends ModuleServiceProvider
{
    protected string $moduleKey = 'core';

    protected string $moduleName = 'Core';

    protected array $policies = [
        Models\Company::class => Policies\CompanyPolicy::class,
        Models\Branch::class => Policies\BranchPolicy::class,
        Models\Department::class => Policies\DepartmentPolicy::class,
        User::class => Policies\UserPolicy::class,
        Role::class => Policies\RolePolicy::class,
        Models\Tax::class => Policies\TaxPolicy::class,
        Models\ExchangeRate::class => Policies\CurrencyPolicy::class,
        Models\FiscalYear::class => Policies\FiscalYearPolicy::class,
        Models\CustomField::class => Policies\CustomFieldPolicy::class,
        Models\ApprovalWorkflow::class => Policies\ApprovalWorkflowPolicy::class,
        Models\ApprovalRequest::class => Policies\ApprovalRequestPolicy::class,
        Models\Backup::class => Policies\BackupPolicy::class,
    ];

    protected array $livewire = [
        'users-table' => Livewire\UsersTable::class,
        'branches-table' => Livewire\BranchesTable::class,
        'departments-table' => Livewire\DepartmentsTable::class,
        'taxes-table' => Livewire\TaxesTable::class,
        'audit-log-table' => Livewire\AuditLogTable::class,
        'login-history-table' => Livewire\LoginHistoryTable::class,
        'notifications-dropdown' => Livewire\NotificationsDropdown::class,
        'global-search' => Livewire\GlobalSearchBox::class,
    ];

    public function boot(): void
    {
        parent::boot();

        Relation::morphMap([
            'company' => Models\Company::class,
            'branch' => Models\Branch::class,
            'department' => Models\Department::class,
            'tax' => Models\Tax::class,
            'approval_request' => Models\ApprovalRequest::class,
            'approval_workflow' => Models\ApprovalWorkflow::class,
            'custom_field' => Models\CustomField::class,
        ]);

        Event::subscribe(RecordAuthenticationEvent::class);

        CustomFieldService::registerEntity('user', __('Users'));

        $this->registerSearchProviders();

        if ($this->app->runningInConsole()) {
            $this->commands([SyncPermissionsCommand::class, BackupCommand::class]);
        }

        $this->callAfterResolving(Schedule::class, function (Schedule $schedule): void {
            $schedule->command('erp:backup')->cron(config('backup.schedule', '0 2 * * *'))->withoutOverlapping();
            $schedule->command('queue:prune-failed --hours=168')->daily();
            $schedule->command('activitylog:clean')->weekly();
        });
    }

    private function registerSearchProviders(): void
    {
        GlobalSearch::register('core', 'Users', 'core.user.view', fn (string $term, int $limit) => User::query()
            ->inCurrentCompany()
            ->where(fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%"))
            ->limit($limit)
            ->get()
            ->map(fn (User $u) => ['title' => $u->name, 'subtitle' => $u->email, 'url' => route('users.edit', $u)]));

        GlobalSearch::register('core', 'Branches', 'core.branch.view', fn (string $term, int $limit) => Models\Branch::query()
            ->where(fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('code', 'like', "%{$term}%"))
            ->limit($limit)
            ->get()
            ->map(fn (Models\Branch $b) => ['title' => $b->name, 'subtitle' => $b->code, 'url' => route('branches.edit', $b)]));

        GlobalSearch::register('core', 'Departments', 'core.department.view', fn (string $term, int $limit) => Models\Department::query()
            ->where('name', 'like', "%{$term}%")
            ->limit($limit)
            ->get()
            ->map(fn (Models\Department $d) => ['title' => $d->name, 'subtitle' => $d->code, 'url' => route('departments.edit', $d)]));
    }
}
