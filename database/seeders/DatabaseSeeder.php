<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\Core\Models\ApprovalRequest;
use Modules\Core\Models\ApprovalStep;
use Modules\Core\Models\ApprovalWorkflow;
use Modules\Core\Models\Branch;
use Modules\Core\Models\Company;
use Modules\Core\Models\Currency;
use Modules\Core\Models\CustomField;
use Modules\Core\Models\Department;
use Modules\Core\Models\ExchangeRate;
use Modules\Core\Models\LoginHistory;
use Modules\Core\Models\NumberSequence;
use Modules\Core\Models\Tax;
use Modules\Core\Services\CompanyService;
use Modules\Core\Services\PermissionService;
use Modules\Core\Services\SettingsService;

class DatabaseSeeder extends Seeder
{
    private const COMPANY_NAME = 'Ledgerline Demo';

    private const PASSWORD = 'password';

    /** Seed a ready-to-use demo workspace and its sign-in accounts. */
    public function run(): void
    {
        $this->seedCurrencies();

        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@ledgerline.test'],
            [
                'name' => 'Demo Administrator',
                'password' => Hash::make(self::PASSWORD),
                'email_verified_at' => now(),
                'timezone' => 'UTC',
                'locale' => 'en',
                'is_active' => true,
            ],
        );

        $company = Company::query()->where('name', self::COMPANY_NAME)->first();

        if ($company === null) {
            $company = app(CompanyService::class)->create([
                'name' => self::COMPANY_NAME,
                'legal_name' => 'Ledgerline Demo Ltd.',
                'email' => 'hello@ledgerline.test',
                'phone' => '+1 555 0100',
                'website' => 'https://ledgerline.test',
                'tax_number' => 'DEMO-123456789',
                'registration_number' => 'REG-2026-001',
                'industry' => 'Professional Services',
                'address' => [
                    'line1' => '100 Demo Street',
                    'city' => 'New York',
                    'state' => 'NY',
                    'postal_code' => '10001',
                    'country' => 'US',
                ],
                'base_currency_code' => 'USD',
                'locale' => 'en',
                'timezone' => 'UTC',
                'fiscal_year_start_month' => 1,
                'date_format' => 'd M Y',
                'is_active' => true,
            ], $admin);
        }

        $admin->forceFill([
            'password' => Hash::make(self::PASSWORD),
            'email_verified_at' => now(),
            'current_company_id' => $company->getKey(),
            'is_active' => true,
        ])->save();

        app(CurrentCompany::class)->runAs($company, fn () => $this->seedCompany($company, $admin));
    }

    private function seedCurrencies(): void
    {
        foreach ([
            ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'decimals' => 2],
            ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€', 'decimals' => 2],
            ['code' => 'GBP', 'name' => 'British Pound', 'symbol' => '£', 'decimals' => 2],
            ['code' => 'AED', 'name' => 'UAE Dirham', 'symbol' => 'د.إ', 'decimals' => 2],
            ['code' => 'SAR', 'name' => 'Saudi Riyal', 'symbol' => '﷼', 'decimals' => 2],
            ['code' => 'INR', 'name' => 'Indian Rupee', 'symbol' => '₹', 'decimals' => 2],
            ['code' => 'THB', 'name' => 'Thai Baht', 'symbol' => '฿', 'decimals' => 2],
        ] as $currency) {
            Currency::query()->updateOrCreate(['code' => $currency['code']], $currency);
        }
    }

    private function seedCompany(Company $company, User $admin): void
    {
        app(PermissionService::class)->createDefaultRoles($company);

        $company->currencies()->sync([
            'USD' => ['is_active' => true],
            'EUR' => ['is_active' => true],
            'GBP' => ['is_active' => true],
            'THB' => ['is_active' => true],
        ]);

        foreach ([
            ['USD', 'EUR', 0.92],
            ['USD', 'GBP', 0.78],
            ['EUR', 'USD', 1.09],
        ] as [$from, $to, $rate]) {
            ExchangeRate::query()->firstOrCreate(
                ['from_currency' => $from, 'to_currency' => $to, 'effective_date' => '2026-01-01'],
                ['rate' => $rate, 'source' => 'Demo rates'],
            );
        }

        $branches = [];
        foreach ([
            ['NY', 'New York Office', 'newyork@ledgerline.test'],
            ['LDN', 'London Office', 'london@ledgerline.test'],
        ] as [$code, $name, $email]) {
            $branches[$code] = Branch::query()->firstOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'email' => $email,
                    'phone' => '+1 555 010'.($code === 'NY' ? '1' : '2'),
                    'address' => ['city' => $code === 'NY' ? 'New York' : 'London', 'country' => $code === 'NY' ? 'US' : 'GB'],
                    'is_head_office' => false,
                    'is_active' => true,
                ],
            );
        }

        $departments = [];
        foreach ([
            ['FIN', 'Finance', 'Finance department'],
            ['SAL', 'Sales', 'Revenue and customer relationships'],
            ['OPS', 'Operations', 'Day-to-day delivery'],
        ] as [$code, $name, $description]) {
            $departments[$code] = Department::query()->firstOrCreate(
                ['code' => $code],
                ['name' => $name, 'description' => $description],
            );
        }

        $manager = $this->ensureUser('manager@ledgerline.test', 'Demo Manager', 'Manager', $company, [
            'branch_ids' => json_encode([$branches['NY']->getKey()]),
            'department_id' => $departments['FIN']->getKey(),
            'job_title' => 'Operations Manager',
        ]);
        $accountant = $this->ensureUser('accountant@ledgerline.test', 'Demo Accountant', 'Accountant', $company, [
            'department_id' => $departments['FIN']->getKey(),
            'job_title' => 'Accountant',
        ]);
        $this->ensureUser('hr@ledgerline.test', 'Demo HR Manager', 'HR Manager', $company, [
            'branch_ids' => json_encode([$branches['NY']->getKey()]),
            'department_id' => $departments['OPS']->getKey(),
            'job_title' => 'HR Manager',
        ]);
        $employee = $this->ensureUser('employee@ledgerline.test', 'Demo Employee', 'Employee', $company, [
            'branch_ids' => json_encode([$branches['LDN']->getKey()]),
            'department_id' => $departments['SAL']->getKey(),
            'job_title' => 'Sales Executive',
        ]);

        foreach (['FIN' => $manager, 'SAL' => $manager, 'OPS' => $employee] as $code => $user) {
            $departments[$code]->forceFill(['manager_id' => $user->getKey()])->save();
        }

        foreach ([
            ['VAT5', 'VAT 5%', 5],
            ['VAT10', 'VAT 10%', 10],
            ['VAT20', 'VAT 20%', 20],
        ] as [$code, $name, $rate]) {
            Tax::query()->firstOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'rate' => $rate,
                    'type' => 'percent',
                    'scope' => 'both',
                    'is_active' => true,
                ],
            );
        }

        app(CompanyService::class)->createFiscalYear($company, now()->subYear());

        foreach (config('numbering.defaults', []) as $type => $defaults) {
            NumberSequence::query()->firstOrCreate(['document_type' => $type], $defaults + ['next_number' => 1]);
        }

        CustomField::query()->firstOrCreate(
            ['entity_type' => 'user', 'name' => 'cost_center'],
            ['label' => 'Cost center', 'type' => 'text', 'sort_order' => 1, 'is_active' => true],
        );
        CustomField::query()->firstOrCreate(
            ['entity_type' => 'user', 'name' => 'employment_type'],
            [
                'label' => 'Employment type',
                'type' => 'select',
                'options' => ['Full-time', 'Part-time', 'Contract'],
                'sort_order' => 2,
                'is_active' => true,
            ],
        );

        $workflow = ApprovalWorkflow::query()->firstOrCreate(
            ['name' => 'Expense approval'],
            [
                'document_type' => 'expense',
                'description' => 'Two-step review for company expenses.',
                'priority' => 1,
                'is_active' => true,
            ],
        );

        foreach ([
            ['step_order' => 1, 'name' => 'Manager review', 'user' => $manager],
            ['step_order' => 2, 'name' => 'Finance approval', 'user' => $accountant],
        ] as $step) {
            ApprovalStep::query()->firstOrCreate(
                ['approval_workflow_id' => $workflow->getKey(), 'step_order' => $step['step_order']],
                [
                    'name' => $step['name'],
                    'approver_type' => 'user',
                    'approver_id' => $step['user']->getKey(),
                    'min_approvals' => 1,
                ],
            );
        }

        ApprovalRequest::query()->firstOrCreate(
            [
                'approvable_type' => 'department',
                'approvable_id' => $departments['FIN']->getKey(),
                'status' => 'pending',
            ],
            [
                'approval_workflow_id' => $workflow->getKey(),
                'current_step' => 1,
                'requested_by' => $employee->getKey(),
            ],
        );

        app(SettingsService::class)->setMany('general', [
            'date_format' => 'd M Y',
            'locale' => 'en',
            'timezone' => 'UTC',
            'fiscal_year_start_month' => 1,
            'invoice_terms' => 'Payment due within 30 days.',
        ]);

        if (LoginHistory::query()->where('user_id', $admin->getKey())->doesntExist()) {
            LoginHistory::query()->create([
                'user_id' => $admin->getKey(),
                'email' => $admin->email,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (demo seed)',
                'successful' => true,
                'event' => 'login',
                'occurred_at' => now()->subMinutes(15),
            ]);
        }

        $this->command?->info('Demo workspace ready. Sign in with admin@ledgerline.test / password');
    }

    /** @param array<string, mixed> $pivot */
    private function ensureUser(string $email, string $name, string $role, Company $company, array $pivot): User
    {
        $user = User::query()->firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make(self::PASSWORD),
                'email_verified_at' => now(),
                'timezone' => 'UTC',
                'locale' => 'en',
                'is_active' => true,
                'current_company_id' => $company->getKey(),
            ],
        );

        if (! $user->belongsToCompany($company)) {
            $company->users()->attach($user->getKey(), $pivot + ['is_owner' => false, 'joined_at' => now()]);
        } else {
            $company->users()->updateExistingPivot($user->getKey(), $pivot);
        }

        $previousTeam = getPermissionsTeamId();
        setPermissionsTeamId($company->getKey());

        try {
            $user->unsetRelation('roles');
            $user->syncRoles([$role]);
        } finally {
            setPermissionsTeamId($previousTeam);
        }

        return $user;
    }
}
