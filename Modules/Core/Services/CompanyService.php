<?php

declare(strict_types=1);

namespace Modules\Core\Services;

use App\Models\User;
use App\Support\Modules\ModuleManager;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Events\CompanyCreated;
use Modules\Core\Models\Branch;
use Modules\Core\Models\Company;
use Modules\Core\Models\CompanyModule;
use Modules\Core\Models\FiscalYear;

/**
 * Creates and maintains companies (tenants), including all the defaults a
 * new company needs to be usable immediately.
 */
class CompanyService
{
    public function __construct(
        private readonly PermissionService $permissions,
        private readonly ModuleManager $modules,
        private readonly CurrentCompany $currentCompany,
    ) {}

    /**
     * Create a company with head office, base currency, current fiscal year,
     * module toggles and default roles; make $owner its owner + Administrator.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, User $owner): Company
    {
        return DB::transaction(function () use ($data, $owner): Company {
            $company = Company::query()->create($data + [
                'base_currency_code' => config('tenancy.default_currency'),
                'locale' => 'en',
                'timezone' => 'UTC',
                'fiscal_year_start_month' => 1,
                'date_format' => 'd M Y',
            ]);

            $company->users()->attach($owner->getKey(), ['is_owner' => true, 'joined_at' => now()]);
            $company->currencies()->attach($company->base_currency_code, ['is_active' => true]);

            $this->currentCompany->runAs($company, function () use ($company): void {
                Branch::query()->create([
                    'name' => __('Head Office'),
                    'code' => 'HO',
                    'email' => $company->email,
                    'phone' => $company->phone,
                    'address' => $company->address,
                    'is_head_office' => true,
                    'is_active' => true,
                ]);

                $this->createFiscalYear($company, Carbon::now());
            });

            foreach ($this->modules->all() as $module) {
                if (! $module->core) {
                    CompanyModule::query()->create([
                        'company_id' => $company->getKey(),
                        'module' => $module->key,
                        'is_enabled' => $module->enabledByDefault,
                    ]);
                }
            }

            $this->permissions->sync();
            $this->permissions->createDefaultRoles($company);

            $previousTeam = getPermissionsTeamId();
            setPermissionsTeamId($company->getKey());
            $owner->unsetRelation('roles');
            $owner->assignRole('Administrator');
            setPermissionsTeamId($previousTeam);

            if ($owner->current_company_id === null) {
                $owner->forceFill(['current_company_id' => $company->getKey()])->save();
            }

            CompanyCreated::dispatch($company, $owner);

            return $company;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Company $company, array $data, ?UploadedFile $logo = null): Company
    {
        if ($logo !== null) {
            if ($company->logo_path) {
                Storage::disk('public')->delete($company->logo_path);
            }
            $data['logo_path'] = $logo->store("companies/{$company->uuid}", 'public');
        }

        $company->fill($data)->save();

        return $company->refresh();
    }

    /** Create the fiscal year that contains $date according to the company's start month. */
    public function createFiscalYear(Company $company, Carbon $date): FiscalYear
    {
        $startMonth = $company->fiscal_year_start_month ?: 1;
        $start = Carbon::create($date->year, $startMonth, 1)->startOfDay();
        if ($start->greaterThan($date)) {
            $start->subYear();
        }
        $end = $start->copy()->addYear()->subDay();

        $name = $start->year === $end->year ? "FY {$start->year}" : "FY {$start->year}-".$end->format('y');

        return FiscalYear::query()->firstOrCreate(
            ['start_date' => $start->toDateString()],
            ['name' => $name, 'end_date' => $end->toDateString(), 'is_closed' => false],
        );
    }
}
