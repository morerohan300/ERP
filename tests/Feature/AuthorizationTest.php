<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Models\Branch;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->withoutVite();
    $this->seed(DatabaseSeeder::class);
});

it('allows an owner to open administrative screens', function (): void {
    $admin = User::query()->where('email', 'admin@ledgerline.test')->firstOrFail();

    $this->actingAs($admin)
        ->get(route('settings.general'))
        ->assertOk()
        ->assertSee('General settings');
});

it('denies employees access to settings and branch management', function (): void {
    $employee = User::query()->where('email', 'employee@ledgerline.test')->firstOrFail();

    $this->actingAs($employee)
        ->get(route('settings.general'))
        ->assertForbidden();

    $this->actingAs($employee)
        ->post(route('branches.store'), [
            'name' => 'Unauthorized Branch',
            'code' => 'NOPE',
        ])
        ->assertForbidden();

    expect(Branch::query()->where('code', 'NOPE')->exists())->toBeFalse();
});

it('requires authentication for the API', function (): void {
    $this->getJson('/api/v1/core/branches')->assertUnauthorized();
});
