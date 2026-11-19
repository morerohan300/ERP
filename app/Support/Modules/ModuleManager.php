<?php

declare(strict_types=1);

namespace App\Support\Modules;

use App\Support\Tenancy\CurrentCompany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Modules\Core\Models\CompanyModule;
use RuntimeException;

/**
 * Discovers modules from Modules/* /module.json, resolves dependency order
 * and answers "is module X enabled for the current company?".
 */
final class ModuleManager
{
    /** @var Collection<string, Module>|null */
    private ?Collection $modules = null;

    public function __construct(
        private readonly string $modulesPath,
        private readonly CurrentCompany $currentCompany,
    ) {}

    /** @return Collection<string, Module> keyed by module key, dependency-ordered */
    public function all(): Collection
    {
        return $this->modules ??= $this->sortByDependencies($this->discover());
    }

    public function find(string $key): ?Module
    {
        return $this->all()->get(strtolower($key));
    }

    public function findOrFail(string $key): Module
    {
        return $this->find($key) ?? throw new RuntimeException("Module [{$key}] does not exist.");
    }

    public function exists(string $key): bool
    {
        return $this->find($key) !== null;
    }

    /** Module is enabled for the active company (core modules always are). */
    public function enabled(string $key, ?int $companyId = null): bool
    {
        $module = $this->find($key);
        if ($module === null) {
            return false;
        }

        if ($module->core) {
            return true;
        }

        $companyId ??= $this->currentCompany->id();
        if ($companyId === null) {
            return $module->enabledByDefault;
        }

        $states = $this->companyStates($companyId);

        $enabled = $states[$module->key] ?? $module->enabledByDefault;
        if (! $enabled) {
            return false;
        }

        foreach ($module->dependencies as $dependency) {
            if (! $this->enabled($dependency, $companyId)) {
                return false;
            }
        }

        return true;
    }

    /** @return Collection<string, Module> */
    public function enabledModules(?int $companyId = null): Collection
    {
        return $this->all()->filter(fn (Module $m) => $this->enabled($m->key, $companyId));
    }

    /** @return array<string, bool> */
    public function companyStates(int $companyId): array
    {
        return Cache::remember(self::cacheKey($companyId), now()->addHour(), function () use ($companyId): array {
            if (! Schema::hasTable('company_modules')) {
                return [];
            }

            return CompanyModule::query()
                ->where('company_id', $companyId)
                ->pluck('is_enabled', 'module')
                ->map(fn ($v) => (bool) $v)
                ->all();
        });
    }

    public function flush(?int $companyId = null): void
    {
        if ($companyId !== null) {
            Cache::forget(self::cacheKey($companyId));
        }
    }

    public static function cacheKey(int $companyId): string
    {
        return "company:{$companyId}:modules";
    }

    /** @return array<int, string> every permission declared by every module */
    public function allPermissionNames(): array
    {
        return $this->all()->flatMap(fn (Module $m) => $m->permissionNames())->values()->all();
    }

    /** @return Collection<string, Module> */
    private function discover(): Collection
    {
        $modules = collect();

        foreach (glob($this->modulesPath.DIRECTORY_SEPARATOR.'*'.DIRECTORY_SEPARATOR.'module.json') ?: [] as $file) {
            $data = json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
            $module = Module::fromArray(dirname($file), $data);
            $modules->put($module->key, $module);
        }

        return $modules;
    }

    /**
     * Topological sort so a module's providers boot after its dependencies.
     *
     * @param  Collection<string, Module>  $modules
     * @return Collection<string, Module>
     */
    private function sortByDependencies(Collection $modules): Collection
    {
        $sorted = [];
        $visiting = [];

        $visit = function (Module $module) use (&$visit, &$sorted, &$visiting, $modules): void {
            if (isset($sorted[$module->key])) {
                return;
            }
            if (isset($visiting[$module->key])) {
                throw new RuntimeException("Circular module dependency detected at [{$module->key}].");
            }
            $visiting[$module->key] = true;

            foreach ($module->dependencies as $dependency) {
                $dep = $modules->get($dependency)
                    ?? throw new RuntimeException("Module [{$module->key}] depends on missing module [{$dependency}].");
                $visit($dep);
            }

            unset($visiting[$module->key]);
            $sorted[$module->key] = $module;
        };

        $modules->sortBy('order')->each($visit);

        return collect($sorted);
    }
}
