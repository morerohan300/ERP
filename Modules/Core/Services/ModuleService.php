<?php

declare(strict_types=1);

namespace Modules\Core\Services;

use App\Support\Modules\Module;
use App\Support\Modules\ModuleManager;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Validation\ValidationException;
use Modules\Core\Models\CompanyModule;

/**
 * Enables/disables modules for the current company while respecting
 * dependencies (cannot disable a module others depend on, and enabling a
 * module enables its dependencies).
 */
class ModuleService
{
    public function __construct(
        private readonly ModuleManager $modules,
        private readonly CurrentCompany $currentCompany,
    ) {}

    public function setEnabled(string $key, bool $enabled): void
    {
        $module = $this->modules->findOrFail($key);
        $companyId = $this->currentCompany->getOrFail()->getKey();

        if ($module->core) {
            throw ValidationException::withMessages(['module' => __('Core modules cannot be disabled.')]);
        }

        if ($enabled) {
            foreach ($module->dependencies as $dependency) {
                $dep = $this->modules->findOrFail($dependency);
                if (! $dep->core && ! $this->modules->enabled($dependency, $companyId)) {
                    $this->write($companyId, $dependency, true);
                }
            }
        } else {
            $dependents = $this->modules->all()
                ->filter(fn (Module $m) => in_array($key, $m->dependencies, true) && $this->modules->enabled($m->key, $companyId))
                ->map(fn (Module $m) => $m->label);

            if ($dependents->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'module' => __('Disable these modules first: :modules', ['modules' => $dependents->implode(', ')]),
                ]);
            }
        }

        $this->write($companyId, $key, $enabled);
    }

    private function write(int $companyId, string $key, bool $enabled): void
    {
        CompanyModule::query()->updateOrCreate(
            ['company_id' => $companyId, 'module' => $key],
            ['is_enabled' => $enabled],
        );

        $this->modules->flush($companyId);
    }
}
