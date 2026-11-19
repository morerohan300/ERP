<?php

declare(strict_types=1);

use App\Models\User;
use App\Support\Tenancy\CurrentCompany;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Models\ApprovalRequest;
use Modules\Core\Models\ApprovalWorkflow;
use Modules\Core\Models\Branch;
use Modules\Core\Models\Company;
use Modules\Core\Models\Currency;
use Modules\Core\Models\CustomField;
use Modules\Core\Models\Department;
use Modules\Core\Models\FiscalYear;
use Modules\Core\Models\Tax;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->withoutVite();
    $this->seed(DatabaseSeeder::class);
    $this->company = Company::query()->where('name', 'Ledgerline Demo')->firstOrFail();
    $this->admin = User::query()->where('email', 'admin@ledgerline.test')->firstOrFail();

    app(CurrentCompany::class)->set($this->company);
    setPermissionsTeamId($this->company->getKey());
    $this->actingAs($this->admin);
});

it('renders every list and settings screen', function (string $route): void {
    $this->get(route($route))->assertOk();
})->with([
    'dashboard',
    'profile.show',
    'approvals.index',
    'audit-log.index',
    'backups.index',
    'branches.index',
    'branches.create',
    'currencies.index',
    'currencies.create',
    'custom-fields.index',
    'custom-fields.create',
    'data-transfers.index',
    'data-transfers.create',
    'departments.index',
    'departments.create',
    'fiscal-years.index',
    'fiscal-years.create',
    'login-history.index',
    'modules.index',
    'numbering.index',
    'roles.index',
    'roles.create',
    'settings.company',
    'settings.communication',
    'settings.general',
    'settings.payments',
    'taxes.index',
    'taxes.create',
    'users.index',
    'users.create',
]);

it('renders representative detail and edit screens', function (string $route, Closure $resolveModel): void {
    $this->get(route($route, $resolveModel()))->assertOk();
})->with([
    'approval request' => ['approvals.show', fn () => ApprovalRequest::query()->firstOrFail()],
    'approval workflow' => ['approval-workflows.edit', fn () => ApprovalWorkflow::query()->firstOrFail()],
    'branch' => ['branches.edit', fn () => Branch::query()->where('code', 'NY')->firstOrFail()],
    'currency' => ['currencies.edit', fn () => Currency::query()->where('code', 'USD')->firstOrFail()],
    'custom field' => ['custom-fields.edit', fn () => CustomField::query()->firstOrFail()],
    'department' => ['departments.edit', fn () => Department::query()->where('code', 'FIN')->firstOrFail()],
    'fiscal year' => ['fiscal-years.edit', fn () => FiscalYear::query()->firstOrFail()],
    'role' => ['roles.edit', fn () => Role::query()->where('name', 'Manager')->firstOrFail()],
    'tax' => ['taxes.edit', fn () => Tax::query()->where('code', 'VAT20')->firstOrFail()],
    'user' => ['users.edit', fn () => User::query()->where('email', 'employee@ledgerline.test')->firstOrFail()],
]);
