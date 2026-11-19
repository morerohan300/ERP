<?php

declare(strict_types=1);

use App\Models\User;
use App\Support\Tenancy\CurrentCompany;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Models\Branch;
use Modules\Core\Models\Company;
use Modules\Core\Models\Department;
use Modules\Core\Models\Tax;

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

it('creates, updates and deletes a branch', function (): void {
    $this->post(route('branches.store'), [
        'name' => 'Test Branch',
        'code' => 'TST',
        'email' => 'test@ledgerline.test',
        'is_active' => true,
    ])->assertRedirect(route('branches.index'));

    $branch = Branch::query()->where('code', 'TST')->firstOrFail();
    expect($branch->name)->toBe('Test Branch');

    $this->put(route('branches.update', $branch), [
        'name' => 'Renamed Branch',
        'code' => 'TST',
        'is_active' => true,
    ])->assertRedirect(route('branches.index'));

    expect($branch->fresh()->name)->toBe('Renamed Branch');

    $this->delete(route('branches.destroy', $branch))->assertRedirect(route('branches.index'));
    expect(Branch::query()->where('code', 'TST')->exists())->toBeFalse();
});

it('creates and updates a department', function (): void {
    $branch = Branch::query()->where('code', 'NY')->firstOrFail();

    $this->post(route('departments.store'), [
        'name' => 'Quality Assurance',
        'code' => 'QA',
        'branch_id' => $branch->getKey(),
        'description' => 'Checks deliverables before release.',
    ])->assertRedirect(route('departments.index'));

    $department = Department::query()->where('code', 'QA')->firstOrFail();
    $this->put(route('departments.update', $department), [
        'name' => 'Quality & Assurance',
        'code' => 'QA',
    ])->assertRedirect(route('departments.index'));

    expect(Department::query()->where('code', 'QA')->value('name'))->toBe('Quality & Assurance');
});

it('creates a tax with validation rules', function (): void {
    $this->post(route('taxes.store'), [
        'name' => 'Test Tax',
        'code' => 'TAXTEST',
        'rate' => 7.5,
        'type' => 'percent',
        'scope' => 'sales',
        'is_active' => true,
    ])->assertRedirect(route('taxes.index'));

    expect((float) Tax::query()->where('code', 'TAXTEST')->firstOrFail()->rate)->toBe(7.5);

    $this->post(route('taxes.store'), [
        'name' => 'Invalid Tax',
        'code' => 'TAXBAD',
        'rate' => 10,
        'type' => 'percent',
        'scope' => 'unknown',
    ])->assertSessionHasErrors('scope');
});
