<?php

declare(strict_types=1);

namespace Modules\Core\Console;

use Illuminate\Console\Command;
use Modules\Core\Models\Company;
use Modules\Core\Services\PermissionService;

/**
 * Creates permissions declared in module.json files. With --roles, also
 * re-applies the default role permission sets for every company (useful
 * after installing a new module).
 */
class SyncPermissionsCommand extends Command
{
    protected $signature = 'erp:sync-permissions {--roles : Re-apply default role permissions for all companies}';

    protected $description = 'Sync module permissions (and optionally default roles) into the database';

    public function handle(PermissionService $permissions): int
    {
        $created = $permissions->sync();
        $this->components->info("Permissions synced ({$created} new).");

        if ($this->option('roles')) {
            Company::query()->each(function (Company $company) use ($permissions): void {
                $permissions->createDefaultRoles($company);
                $this->components->twoColumnDetail($company->name, '<fg=green>roles updated</>');
            });
        }

        return self::SUCCESS;
    }
}
