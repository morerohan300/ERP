<?php

declare(strict_types=1);

namespace Modules\CRM\Http\Middleware;

use App\Support\Tenancy\CurrentCompany;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/** Allows a request through only for an active portal customer belonging to the URL's company. */
class EnsurePortalUser
{
    public function __construct(private readonly CurrentCompany $currentCompany) {}

    public function handle(Request $request, Closure $next): Response
    {
        $guard = Auth::guard('portal');
        $user = $guard->user();

        if ($user === null || ! $user->is_active || (int) $user->company_id !== $this->currentCompany->id()) {
            $guard->logout();

            return redirect()->route('crm.portal.login', ['company' => $request->route('company')]);
        }

        return $next($request);
    }
}
