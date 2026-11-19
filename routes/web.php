<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\Web\CompanyController;

Route::get('/', function () {
    return view('welcome');
});

// Registered outside the module middleware group: the "company" middleware
// redirects users without a company here, so it must not run on this route.
Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/companies/onboarding', [CompanyController::class, 'onboarding'])->name('companies.onboarding');
});
