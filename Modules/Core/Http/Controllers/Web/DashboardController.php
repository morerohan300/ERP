<?php

declare(strict_types=1);

namespace Modules\Core\Http\Controllers\Web;

use App\Models\User;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Modules\Core\Models\ApprovalRequest;
use Modules\Core\Models\Branch;
use Modules\Core\Models\Department;
use Modules\Core\Models\LoginHistory;
use Modules\Core\Services\ApprovalService;

final class DashboardController extends Controller
{
    public function index(CurrentCompany $company, ApprovalService $approvals): View
    {
        /** @var User $user */
        $user = auth()->user();
        $companyId = $company->id();

        $stats = [
            'users' => User::query()->inCurrentCompany()->count(),
            'branches' => Branch::query()->count(),
            'departments' => Department::query()->count(),
            'pending_approvals' => ApprovalRequest::query()->pending()->count(),
        ];

        $pending = $approvals->pendingFor($user)->take(5);

        $activity = DB::table('activity_log')
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->latest('created_at')
            ->limit(8)
            ->get(['description', 'event', 'created_at']);

        $logins = LoginHistory::query()
            ->where('user_id', $user->getKey())
            ->latest('occurred_at')
            ->limit(5)
            ->get();

        return view('core::dashboard', compact('stats', 'pending', 'activity', 'logins'));
    }
}
