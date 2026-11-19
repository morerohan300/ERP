<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\Api\ApprovalController;
use Modules\Core\Http\Controllers\Api\BranchController;
use Modules\Core\Http\Controllers\Api\CompanyController;
use Modules\Core\Http\Controllers\Api\CurrencyController;
use Modules\Core\Http\Controllers\Api\DepartmentController;
use Modules\Core\Http\Controllers\Api\SettingController;
use Modules\Core\Http\Controllers\Api\TaxController;
use Modules\Core\Http\Controllers\Api\UserController;

Route::get('companies/current', [CompanyController::class, 'current'])->name('companies.current');
Route::apiResource('companies', CompanyController::class)->only(['index', 'store', 'show']);

Route::apiResource('users', UserController::class);
Route::apiResource('branches', BranchController::class);
Route::apiResource('departments', DepartmentController::class);
Route::apiResource('taxes', TaxController::class);
Route::get('currencies', [CurrencyController::class, 'index'])->name('currencies.index');

Route::prefix('settings')->name('settings.')->group(function (): void {
    Route::get('/', [SettingController::class, 'index'])->name('index');
    Route::put('/', [SettingController::class, 'update'])->name('update');
});

Route::prefix('approvals')->name('approvals.')->group(function (): void {
    Route::get('/', [ApprovalController::class, 'index'])->name('index');
    Route::get('{approval_request}', [ApprovalController::class, 'show'])->name('show');
});
