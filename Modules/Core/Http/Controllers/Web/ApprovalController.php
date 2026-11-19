<?php

declare(strict_types=1);

namespace Modules\Core\Http\Controllers\Web;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Modules\Core\Http\Requests\ApprovalDecisionRequest;
use Modules\Core\Models\ApprovalRequest;
use Modules\Core\Services\ApprovalService;

final class ApprovalController extends Controller
{
    public function __construct(private readonly ApprovalService $approvals) {}

    public function index(): View
    {
        /** @var User $user */
        $user = auth()->user();

        return view('core::approvals.index', [
            'pending' => $this->approvals->pendingFor($user),
            'mine' => ApprovalRequest::query()
                ->where('requested_by', $user->getKey())
                ->with(['workflow', 'approvable', 'requester'])
                ->latest()
                ->limit(25)
                ->get(),
        ]);
    }

    public function show(ApprovalRequest $approvalRequest): View
    {
        Gate::authorize('view', $approvalRequest);

        return view('core::approvals.show', [
            'request' => $approvalRequest->load(['workflow.steps', 'actions.user', 'requester', 'approvable']),
            'canAct' => $this->approvals->canAct($approvalRequest, auth()->user()),
        ]);
    }

    public function approve(ApprovalDecisionRequest $request, ApprovalRequest $approvalRequest): RedirectResponse
    {
        $this->approvals->approve($approvalRequest, $request->user(), $request->input('comment'));

        return $this->status('approvals.index', __('Approval recorded.'));
    }

    public function reject(ApprovalDecisionRequest $request, ApprovalRequest $approvalRequest): RedirectResponse
    {
        $data = $request->validate(['comment' => ['required', 'string', 'max:2000']]);
        $this->approvals->reject($approvalRequest, $request->user(), $data['comment']);

        return $this->status('approvals.index', __('Request rejected.'));
    }

    public function cancel(ApprovalRequest $approvalRequest): RedirectResponse
    {
        Gate::authorize('cancel', $approvalRequest);
        $this->approvals->cancel($approvalRequest, auth()->user());

        return $this->status('approvals.index', __('Request cancelled.'));
    }
}
