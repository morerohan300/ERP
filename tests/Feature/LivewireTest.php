<?php

declare(strict_types=1);

use App\Models\User;
use App\Support\Tenancy\CurrentCompany;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Core\Livewire\TaxesTable;
use Modules\Core\Livewire\UsersTable;
use Modules\Core\Models\Company;

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

it('filters users by search term', function (): void {
    Livewire::test(UsersTable::class)
        ->set('search', 'Demo Manager')
        ->assertSee('Demo Manager')
        ->assertDontSee('Demo Employee');
});

it('filters taxes by search term', function (): void {
    Livewire::test(TaxesTable::class)
        ->set('search', 'VAT 20%')
        ->assertSee('VAT 20%')
        ->assertDontSee('VAT 5%');
});

it('sorts users when the sort action is called', function (): void {
    Livewire::test(UsersTable::class)
        ->call('sortBy', 'name')
        ->assertSet('direction', 'desc')
        ->call('sortBy', 'name')
        ->assertSet('direction', 'asc');
});
