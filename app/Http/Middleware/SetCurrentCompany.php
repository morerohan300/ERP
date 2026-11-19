<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\Tenancy\CurrentCompany;
use Closure;
use Illuminate\Http\Request;
use Modules\Core\Models\Branch;
use Modules\Core\Models\Company;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the active company for the authenticated user and makes it
 * available to tenant scopes, permissions (spatie teams) and views.
 *
 * Web: from the session (set by the company switcher), falling back to the
 * user's last-used company. API: from the X-Company header (company UUID).
 */
final class SetCurrentCompany
{
    public function __construct(private readonly CurrentCompany $currentCompany) {}

    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        $company = $this->resolveCompany($request, $user);

        if ($company === null) {
            if ($request->expectsJson() || $request->is('api/*')) {
                abort(403, 'You do not have access to any company.');
            }

            return redirect()->route('companies.onboarding');
        }

        $this->currentCompany->set($company);
        setPermissionsTeamId($company->getKey());
        $user->unsetRelation('roles')->unsetRelation('permissions');

        $membership = $user->companies()->whereKey($company->getKey())->first()?->pivot;
        $allowedBranches = $membership?->branch_ids ? json_decode((string) $membership->branch_ids, true) : null;
        $this->currentCompany->setAllowedBranchIds($membership?->is_owner ? null : $allowedBranches);

        if ($request->hasSession()) {
            $branchId = $request->session()->get(config('tenancy.branch_session_key'));
            if ($branchId !== null) {
                $branch = Branch::query()->find($branchId);
                if ($branch !== null && ($allowedBranches === null || in_array($branch->getKey(), $allowedBranches, true))) {
                    $this->currentCompany->setBranch($branch);
                }
            }
        }

        if ($user->current_company_id !== $company->getKey()) {
            $user->forceFill(['current_company_id' => $company->getKey()])->saveQuietly();
        }

        view()->share('currentCompany', $company);
        view()->share('currentBranch', $this->currentCompany->branch());

        return $next($request);
    }

    private function resolveCompany(Request $request, User $user): ?Company
    {
        $header = $request->header(config('tenancy.header'));
        if ($header !== null && $header !== '') {
            return $user->companies()->where('companies.uuid', $header)->where('companies.is_active', true)->first()
                ?? abort(403, 'You do not have access to the requested company.');
        }

        $candidates = [];
        if ($request->hasSession()) {
            $candidates[] = $request->session()->get(config('tenancy.session_key'));
        }
        $candidates[] = $user->current_company_id;

        foreach (array_filter($candidates) as $companyId) {
            $company = $user->companies()->whereKey($companyId)->where('companies.is_active', true)->first();
            if ($company !== null) {
                if ($request->hasSession()) {
                    $request->session()->put(config('tenancy.session_key'), $company->getKey());
                }

                return $company;
            }
        }

        $company = $user->companies()->where('companies.is_active', true)->orderBy('companies.name')->first();
        if ($company !== null && $request->hasSession()) {
            $request->session()->put(config('tenancy.session_key'), $company->getKey());
        }

        return $company;
    }
}
