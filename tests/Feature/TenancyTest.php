<?php

declare(strict_types=1);

use App\Models\User;
use App\Support\Tenancy\CurrentCompany;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Models\Branch;
use Modules\Core\Models\Company;
use Modules\Core\Services\CompanyService;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->withoutVite();
    $this->seed(DatabaseSeeder::class);
    $this->company = Company::query()->where('name', 'Ledgerline Demo')->firstOrFail();
    $this->admin = User::query()->where('email', 'admin@ledgerline.test')->firstOrFail();
});

it('scopes tenant models to the active company', function (): void {
    $otherOwner = User::factory()->create([
        'email' => 'other-owner@ledgerline.test',
        'password' => bcrypt('password'),
        'email_verified_at' => now(),
    ]);

    $otherCompany = app(CompanyService::class)->create([
        'name' => 'Other Company',
        'base_currency_code' => 'USD',
        'locale' => 'en',
        'timezone' => 'UTC',
        'fiscal_year_start_month' => 1,
        'date_format' => 'd M Y',
    ], $otherOwner);

    app(CurrentCompany::class)->runAs($otherCompany, fn () => Branch::query()->create([
        'name' => 'Other Branch',
        'code' => 'OTHER',
        'is_active' => true,
    ]));

    app(CurrentCompany::class)->set($this->company);
    setPermissionsTeamId($this->company->getKey());

    expect(Branch::query()->where('code', 'OTHER')->exists())->toBeFalse();
    expect(Branch::query()->where('code', 'NY')->exists())->toBeTrue();

    $this->actingAs($this->admin)
        ->get(route('branches.index'))
        ->assertOk()
        ->assertSee('NY')
        ->assertDontSee('OTHER');
});

it('rejects an API request for a company the user does not belong to', function (): void {
    $otherOwner = User::factory()->create([
        'email' => 'api-owner@ledgerline.test',
        'password' => bcrypt('password'),
        'email_verified_at' => now(),
    ]);

    app(CompanyService::class)->create([
        'name' => 'API Company',
        'base_currency_code' => 'USD',
        'locale' => 'en',
        'timezone' => 'UTC',
        'fiscal_year_start_month' => 1,
        'date_format' => 'd M Y',
    ], $otherOwner);

    $token = $otherOwner->createToken('tests')->plainTextToken;

    $this->withToken($token)
        ->withHeader(config('tenancy.header'), $this->company->uuid)
        ->getJson('/api/v1/core/branches')
        ->assertForbidden();
});
