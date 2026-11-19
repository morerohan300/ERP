<?php

declare(strict_types=1);

namespace Modules\Core\Services;

use App\Support\Modules\Module;
use App\Support\Modules\ModuleManager;
use Illuminate\Support\Collection;
use Modules\Core\Models\Company;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Keeps the permissions table in sync with every module's module.json and
 * provisions the default roles for a company.
 */
class PermissionService
{
    /**
     * Default roles created for every new company.
     * Values are permission patterns: "*" = all, "crm.*" = whole module,
     * "*.view" = every view permission, or an exact name.
     *
     * @var array<string, array<int, string>>
     */
    public const DEFAULT_ROLES = [
        'Administrator' => ['*'],
        'Manager' => ['*.view', '*.create', '*.edit', '*.export', '*.approve', 'core.approval.view'],
        'Sales' => ['crm.*', 'sales.*.view', 'sales.*.create', 'sales.*.edit', 'inventory.*.view', 'core.approval.view'],
        'Accountant' => ['accounting.*', 'sales.*.view', 'purchase.*.view', 'core.tax.view', 'core.currency.*', 'core.approval.*'],
        'HR Manager' => ['hr.*', 'core.department.*', 'core.user.view', 'core.approval.*'],
        'Employee' => ['projects.task.view', 'projects.timesheet.*', 'hr.leave_request.view', 'hr.leave_request.create', 'core.approval.view'],
    ];

    public function __construct(
        private readonly ModuleManager $modules,
        private readonly PermissionRegistrar $registrar,
    ) {}

    /** Create any permissions declared by modules that don't exist yet. Returns count created. */
    public function sync(): int
    {
        $existing = Permission::query()->where('guard_name', 'web')->pluck('name')->flip();
        $created = 0;

        foreach ($this->modules->allPermissionNames() as $name) {
            if (! $existing->has($name)) {
                Permission::query()->create(['name' => $name, 'guard_name' => 'web']);
                $created++;
            }
        }

        $this->registrar->forgetCachedPermissions();

        return $created;
    }

    /** Create the default role set for a company (idempotent). */
    public function createDefaultRoles(Company $company): void
    {
        $previousTeam = getPermissionsTeamId();
        setPermissionsTeamId($company->getKey());

        try {
            foreach (self::DEFAULT_ROLES as $roleName => $patterns) {
                /** @var Role $role */
                $role = Role::query()->firstOrCreate([
                    'name' => $roleName,
                    'guard_name' => 'web',
                    'company_id' => $company->getKey(),
                ]);

                $role->syncPermissions($this->expand($patterns));
            }
        } finally {
            setPermissionsTeamId($previousTeam);
        }

        $this->registrar->forgetCachedPermissions();
    }

    /**
     * Expand wildcard patterns into concrete permission names.
     *
     * @param  array<int, string>  $patterns
     * @return array<int, string>
     */
    public function expand(array $patterns): array
    {
        $all = collect($this->modules->allPermissionNames());

        return $all->filter(function (string $name) use ($patterns): bool {
            foreach ($patterns as $pattern) {
                if ($pattern === '*' || $pattern === $name || fnmatch($pattern, $name)) {
                    return true;
                }
            }

            return false;
        })->values()->all();
    }

    /**
     * Permissions grouped for the role editor: module => entity => [action => name].
     *
     * @return Collection<string, array{module: Module, entities: array<string, array<string, string>>}>
     */
    public function matrix(): Collection
    {
        return $this->modules->all()->mapWithKeys(function (Module $module): array {
            $entities = [];
            foreach ($module->permissions as $entity => $actions) {
                foreach ($actions as $action) {
                    $entities[$entity][$action] = "{$module->key}.{$entity}.{$action}";
                }
            }

            return [$module->key => ['module' => $module, 'entities' => $entities]];
        })->filter(fn (array $group) => $group['entities'] !== []);
    }
}
