<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Models\Company;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->withoutVite();
    $this->seed(DatabaseSeeder::class);
    $this->company = Company::query()->where('name', 'Ledgerline Demo')->firstOrFail();
    $this->admin = User::query()->where('email', 'admin@ledgerline.test')->firstOrFail();
});

it('redirects guests to the login page', function (): void {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

it('renders the Fortify authentication views', function (): void {
    $this->get(route('login'))->assertOk()->assertSee('Welcome back');
    $this->get(route('register'))->assertOk()->assertSee('Create your account');
    $this->get(route('password.request'))->assertOk()->assertSee('Reset password');
});

it('authenticates the seeded administrator', function (): void {
    $response = $this->post(route('login.store'), [
        'email' => 'admin@ledgerline.test',
        'password' => 'password',
    ]);

    $response->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs($this->admin);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Good')
        ->assertSee('Pending approvals');
});

it('shows the profile and two-factor options to a signed-in user', function (): void {
    $this->actingAs($this->admin)
        ->get(route('profile.show'))
        ->assertOk()
        ->assertSee('Change password')
        ->assertSee('Two-factor authentication');
});
