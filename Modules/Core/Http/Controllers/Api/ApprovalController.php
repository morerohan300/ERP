<?php

declare(strict_types=1);

namespace Modules\Core\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Modules\Core\Http\Resources\ApprovalRequestResource;
use Modules\Core\Models\ApprovalRequest;
use Modules\Core\Services\ApprovalService;

final class ApprovalController extends Controller
{
    public function __construct(private readonly ApprovalService $approvals) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('core.approval.view');

        $query = ApprovalRequest::query()->with(['workflow', 'requester', 'approvable']);

        if ($request->input('scope') === 'mine') {
            $query->where('requested_by', $request->user()->getKey());
        } elseif ($request->input('scope') !== 'all') {
            $query->pending();
        }

        return ApprovalRequestResource::collection($query->latest()->paginate(min(max((int) $request->input('per_page', 25), 1), 100)));
    }

    public function show(ApprovalRequest $approvalRequest): ApprovalRequestResource
    {
        Gate::authorize('view', $approvalRequest);

        return new ApprovalRequestResource($approvalRequest->load(['workflow.steps', 'requester', 'approvable', 'actions']));
    }
}
