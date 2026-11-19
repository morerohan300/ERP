<?php

namespace App\Providers;

use App\Models\User;
use App\Support\Database\BlueprintMacros;
use App\Support\Modules\ModuleManager;
use App\Support\Numbering\DocumentNumberGenerator;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register application services and every module's service provider.
     */
    public function register(): void
    {
        BlueprintMacros::register();

        $this->app->scoped(CurrentCompany::class);
        $this->app->scoped(DocumentNumberGenerator::class);

        $this->app->singleton(ModuleManager::class, fn ($app) => new ModuleManager(
            base_path('Modules'),
            $app->make(CurrentCompany::class),
        ));

        foreach ($this->app->make(ModuleManager::class)->all() as $module) {
            if (class_exists($module->provider)) {
                $this->app->register($module->provider);
            }
        }
    }

    /**
     * Bootstrap application-wide behaviour.
     */
    public function boot(): void
    {
        Model::shouldBeStrict(! $this->app->isProduction());
        Model::automaticallyEagerLoadRelationships();

        Relation::morphMap([
            'user' => User::class,
        ]);

        // Owners of a company bypass permission checks inside that company.
        Gate::before(function (User $user, string $ability) {
            return $user->isOwnerOfCurrentCompany() ? true : null;
        });

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->getKey() ?: $request->ip());
        });

        Blade::if('module', fn (string $key) => app(ModuleManager::class)->enabled($key));
    }
}
