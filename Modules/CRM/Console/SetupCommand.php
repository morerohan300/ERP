<?php

declare(strict_types=1);

namespace Modules\CRM\Console;

use Illuminate\Console\Command;
use Modules\Core\Models\Company;
use Modules\CRM\Services\CrmSetupService;

class SetupCommand extends Command
{
    protected $signature = 'crm:setup {--company= : UUID of a single company (default: every company)}';

    protected $description = 'Install the CRM starting data (lead statuses, pipeline, SLA policies, templates) for existing companies';

    public function handle(CrmSetupService $setup): int
    {
        $companies = Company::query()
            ->when($this->option('company'), fn ($query, $uuid) => $query->where('uuid', $uuid))
            ->get();

        if ($companies->isEmpty()) {
            $this->components->warn('No matching company found.');

            return self::FAILURE;
        }

        foreach ($companies as $company) {
            $setup->install($company);
            $this->components->info("CRM defaults installed for {$company->name}.");
        }

        return self::SUCCESS;
    }
}
