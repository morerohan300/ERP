<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\Web\ApprovalController;
use Modules\Core\Http\Controllers\Web\ApprovalWorkflowController;
use Modules\Core\Http\Controllers\Web\AuditLogController;
use Modules\Core\Http\Controllers\Web\BackupController;
use Modules\Core\Http\Controllers\Web\BranchController;
use Modules\Core\Http\Controllers\Web\CompanyController;
use Modules\Core\Http\Controllers\Web\CurrencyController;
use Modules\Core\Http\Controllers\Web\CustomFieldController;
use Modules\Core\Http\Controllers\Web\DashboardController;
use Modules\Core\Http\Controllers\Web\DataTransferController;
use Modules\Core\Http\Controllers\Web\DepartmentController;
use Modules\Core\Http\Controllers\Web\FiscalYearController;
use Modules\Core\Http\Controllers\Web\LoginHistoryController;
use Modules\Core\Http\Controllers\Web\ModuleController;
use Modules\Core\Http\Controllers\Web\NumberSequenceController;
use Modules\Core\Http\Controllers\Web\ProfileController;
use Modules\Core\Http\Controllers\Web\RoleController;
use Modules\Core\Http\Controllers\Web\SettingController;
use Modules\Core\Http\Controllers\Web\TaxController;
use Modules\Core\Http\Controllers\Web\UserController;

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');

// Company onboarding lives outside the company middleware (see routes/web.php).
Route::get('/companies/create', [CompanyController::class, 'create'])->name('companies.create');
Route::post('/companies', [CompanyController::class, 'store'])->name('companies.store');
Route::post('/companies/{company}/switch', [CompanyController::class, 'switch'])->name('companies.switch');
Route::get('/settings/company', [CompanyController::class, 'edit'])->name('settings.company');
Route::put('/settings/company', [CompanyController::class, 'update'])->name('settings.company.update');

Route::prefix('approvals')->name('approvals.')->group(function (): void {
    Route::get('/', [ApprovalController::class, 'index'])->name('index')->middleware('permission:core.approval.view');
    Route::get('/{approval_request}', [ApprovalController::class, 'show'])->name('show');
    Route::post('/{approval_request}/approve', [ApprovalController::class, 'approve'])->name('approve');
    Route::post('/{approval_request}/reject', [ApprovalController::class, 'reject'])->name('reject');
    Route::post('/{approval_request}/cancel', [ApprovalController::class, 'cancel'])->name('cancel');
});

Route::resource('branches', BranchController::class)->except(['show']);
Route::resource('departments', DepartmentController::class)->except(['show']);
Route::resource('users', UserController::class)->except(['show']);
Route::post('users/export', [UserController::class, 'export'])->name('users.export');
Route::resource('roles', RoleController::class)->except(['show']);

Route::prefix('settings')->name('settings.')->group(function (): void {
    Route::get('general', [SettingController::class, 'general'])->name('general')->middleware('permission:core.settings.view');
    Route::put('general', [SettingController::class, 'updateGeneral'])->name('general.update')->middleware('permission:core.settings.edit');
    Route::get('communication', [SettingController::class, 'communication'])->name('communication')->middleware('permission:core.settings.view');
    Route::put('communication', [SettingController::class, 'updateCommunication'])->name('communication.update')->middleware('permission:core.settings.edit');
    Route::get('payments', [SettingController::class, 'payments'])->name('payments')->middleware('permission:core.settings.view');
    Route::put('payments', [SettingController::class, 'updatePayments'])->name('payments.update')->middleware('permission:core.settings.edit');
});

Route::prefix('currencies')->name('currencies.')->group(function (): void {
    Route::get('/', [CurrencyController::class, 'index'])->name('index')->middleware('permission:core.currency.view');
    Route::get('create', [CurrencyController::class, 'create'])->name('create')->middleware('permission:core.currency.create');
    Route::post('/', [CurrencyController::class, 'store'])->name('store')->middleware('permission:core.currency.create');
    Route::get('{currency}/edit', [CurrencyController::class, 'edit'])->name('edit')->middleware('permission:core.currency.edit');
    Route::put('{currency}', [CurrencyController::class, 'update'])->name('update')->middleware('permission:core.currency.edit');
    Route::delete('{currency}', [CurrencyController::class, 'destroy'])->name('destroy')->middleware('permission:core.currency.delete');
    Route::post('{currency}/toggle', [CurrencyController::class, 'toggle'])->name('toggle')->middleware('permission:core.currency.edit');
    Route::post('rates', [CurrencyController::class, 'storeRate'])->name('rates.store')->middleware('permission:core.currency.edit');
    Route::delete('rates/{exchange_rate}', [CurrencyController::class, 'destroyRate'])->name('rates.destroy')->middleware('permission:core.currency.delete');
});

Route::resource('taxes', TaxController::class)->except(['show']);
Route::resource('fiscal-years', FiscalYearController::class)->except(['show']);

Route::prefix('numbering')->name('numbering.')->group(function (): void {
    Route::get('/', [NumberSequenceController::class, 'index'])->name('index')->middleware('permission:core.numbering.view');
    Route::put('{number_sequence}', [NumberSequenceController::class, 'update'])->name('update')->middleware('permission:core.numbering.edit');
});

Route::prefix('modules')->name('modules.')->middleware('permission:core.module.view')->group(function (): void {
    Route::get('/', [ModuleController::class, 'index'])->name('index');
    Route::post('{module}/toggle', [ModuleController::class, 'toggle'])->name('toggle')->middleware('permission:core.module.edit');
});

Route::resource('custom-fields', CustomFieldController::class)->except(['show']);
Route::resource('approval-workflows', ApprovalWorkflowController::class)->except(['show']);

Route::prefix('audit-log')->name('audit-log.')->group(function (): void {
    Route::get('/', [AuditLogController::class, 'index'])->name('index')->middleware('permission:core.audit_log.view');
    Route::get('export', [AuditLogController::class, 'export'])->name('export')->middleware('permission:core.audit_log.export');
});
Route::get('login-history', [LoginHistoryController::class, 'index'])->name('login-history.index')->middleware('permission:core.audit_log.view');

Route::prefix('data-transfers')->name('data-transfers.')->group(function (): void {
    Route::get('/', [DataTransferController::class, 'index'])->name('index')->middleware('permission:core.user.import');
    Route::get('create', [DataTransferController::class, 'create'])->name('create')->middleware('permission:core.user.import');
    Route::post('/', [DataTransferController::class, 'store'])->name('store')->middleware('permission:core.user.import');
    Route::get('{data_transfer}/download', [DataTransferController::class, 'download'])->name('download')->middleware('permission:core.user.import');
});

Route::prefix('backups')->name('backups.')->group(function (): void {
    Route::get('/', [BackupController::class, 'index'])->name('index')->middleware('permission:core.backup.view');
    Route::post('/', [BackupController::class, 'store'])->name('store')->middleware('permission:core.backup.create');
    Route::get('{backup}/download', [BackupController::class, 'download'])->name('download')->middleware('permission:core.backup.view');
    Route::delete('{backup}', [BackupController::class, 'destroy'])->name('destroy')->middleware('permission:core.backup.delete');
});
