<?php

declare(strict_types=1);

use App\Models\User;
use App\Support\Tenancy\CurrentCompany;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Models\Branch;
use Modules\Core\Models\Company;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(DatabaseSeeder::class);
    $this->company = Company::query()->where('name', 'Ledgerline Demo')->firstOrFail();
    $this->admin = User::query()->where('email', 'admin@ledgerline.test')->firstOrFail();
    $this->token = $this->admin->createToken('tests')->plainTextToken;

    app(CurrentCompany::class)->set($this->company);
    setPermissionsTeamId($this->company->getKey());
});

it('lists branches as JSON with public UUIDs', function (): void {
    $branch = Branch::query()->where('code', 'NY')->firstOrFail();

    $response = $this->withToken($this->token)
        ->withHeader(config('tenancy.header'), $this->company->uuid)
        ->getJson('/api/v1/core/branches');

    $response->assertOk()
        ->assertJsonStructure(['data', 'links', 'meta'])
        ->assertJsonFragment([
            'id' => $branch->uuid,
            'code' => 'NY',
            'name' => 'New York Office',
        ]);
});

it('creates a branch through the API', function (): void {
    $this->withToken($this->token)
        ->withHeader(config('tenancy.header'), $this->company->uuid)
        ->postJson('/api/v1/core/branches', [
            'name' => 'API Branch',
            'code' => 'API',
            'is_active' => true,
        ])
        ->assertCreated()
        ->assertJsonFragment(['code' => 'API']);

    expect(Branch::query()->where('code', 'API')->exists())->toBeTrue();
});

it('returns the current company for the authenticated user', function (): void {
    $this->withToken($this->token)
        ->withHeader(config('tenancy.header'), $this->company->uuid)
        ->getJson('/api/v1/core/companies/current')
        ->assertOk()
        ->assertJsonFragment([
            'id' => $this->company->uuid,
            'name' => 'Ledgerline Demo',
        ]);
});
