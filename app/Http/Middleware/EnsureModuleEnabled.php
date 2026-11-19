<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Modules\ModuleManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks access to a module's routes when the module is disabled for the
 * active company. Usage: ->middleware('module:crm').
 */
final class EnsureModuleEnabled
{
    public function __construct(private readonly ModuleManager $modules) {}

    public function handle(Request $request, Closure $next, string $module): Response
    {
        if (! $this->modules->enabled($module)) {
            abort(404, __('This module is not enabled for your company.'));
        }

        return $next($request);
    }
}
