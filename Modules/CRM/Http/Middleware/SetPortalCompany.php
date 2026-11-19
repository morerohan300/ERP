<?php

declare(strict_types=1);

namespace Modules\CRM\Http\Middleware;

use App\Support\Modules\ModuleManager;
use App\Support\Tenancy\CurrentCompany;
use Closure;
use Illuminate\Http\Request;
use Modules\Core\Models\Company;
use Symfony\Component\HttpFoundation\Response;

/**
 * Portal URLs carry the company's UUID (/portal/{company}/...). This middleware activates that
 * company for the request, and hides the portal when the company is inactive or CRM is switched off.
 */
class SetPortalCompany
{
    public function __construct(
        private readonly CurrentCompany $currentCompany,
        private readonly ModuleManager $modules,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $company = Company::query()
            ->where('uuid', (string) $request->route('company'))
            ->where('is_active', true)
            ->first();

        abort_if($company === null || ! $this->modules->enabled('crm', $company->getKey()), 404);

        $this->currentCompany->set($company);

        return $next($request);
    }
}
