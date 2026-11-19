<?php

declare(strict_types=1);

namespace App\Support\Modules;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

/**
 * Base provider every module extends. Wires up routes, migrations, views,
 * translations, config, policies, event listeners and Livewire components
 * from the module's conventional folders.
 */
abstract class ModuleServiceProvider extends ServiceProvider
{
    /** Module key, e.g. "crm". */
    protected string $moduleKey;

    /** Module folder name, e.g. "CRM". */
    protected string $moduleName;

    /** @var array<class-string, class-string> model => policy */
    protected array $policies = [];

    /** @var array<class-string, array<int, class-string>> event => listeners */
    protected array $listen = [];

    /** @var array<string, class-string> alias => Livewire component class */
    protected array $livewire = [];

    public function register(): void
    {
        $config = $this->modulePath('Config/config.php');
        if (is_file($config)) {
            $this->mergeConfigFrom($config, $this->moduleKey);
        }
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom($this->modulePath('Database/Migrations'));
        $this->loadViewsFrom($this->modulePath('Resources/views'), $this->moduleKey);
        $this->loadTranslationsFrom($this->modulePath('Resources/lang'), $this->moduleKey);
        $this->loadJsonTranslationsFrom($this->modulePath('Resources/lang'));

        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }

        $events = $this->app['events'];
        foreach ($this->listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                $events->listen($event, $listener);
            }
        }

        if (class_exists(Livewire::class)) {
            foreach ($this->livewire as $alias => $component) {
                Livewire::component("{$this->moduleKey}.{$alias}", $component);
            }
        }

        $this->registerRoutes();
    }

    protected function registerRoutes(): void
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        $web = $this->modulePath('Routes/web.php');
        if (is_file($web)) {
            $middleware = ['web', 'auth', 'verified', 'company'];
            if (! $this->isCoreModule()) {
                $middleware[] = "module:{$this->moduleKey}";
            }

            Route::middleware($middleware)
                ->prefix($this->isCoreModule() ? '' : $this->moduleKey)
                ->name($this->isCoreModule() ? '' : "{$this->moduleKey}.")
                ->group($web);
        }

        $api = $this->modulePath('Routes/api.php');
        if (is_file($api)) {
            $middleware = ['api', 'auth:sanctum', 'company', 'throttle:api'];
            if (! $this->isCoreModule()) {
                $middleware[] = "module:{$this->moduleKey}";
            }

            Route::middleware($middleware)
                ->prefix("api/v1/{$this->moduleKey}")
                ->name("api.{$this->moduleKey}.")
                ->group($api);
        }
    }

    protected function isCoreModule(): bool
    {
        return app(ModuleManager::class)->find($this->moduleKey)?->core ?? false;
    }

    protected function modulePath(string $path = ''): string
    {
        return base_path('Modules'.DIRECTORY_SEPARATOR.$this->moduleName.($path !== '' ? DIRECTORY_SEPARATOR.$path : ''));
    }
}
