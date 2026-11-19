<?php

declare(strict_types=1);

namespace Modules\Core\Services;

use App\Models\User;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Modules\Core\Concerns\HasApprovals;
use Modules\Core\Events\ApprovalCompleted;
use Modules\Core\Events\ApprovalRequested;
use Modules\Core\Models\ApprovalAction;
use Modules\Core\Models\ApprovalRequest;
use Modules\Core\Models\ApprovalStep;
use Modules\Core\Models\ApprovalWorkflow;
use Modules\Core\Models\Department;
use Modules\Core\Notifications\ApprovalDecisionNotification;
use Modules\Core\Notifications\ApprovalPendingNotification;
use Spatie\Permission\Models\Role;

/**
 * Multi-level approval engine.
 *
 *   submit()  → finds the first active workflow whose conditions match the
 *               document; returns null when no approval is needed.
 *   approve() → records the action; when a step reaches min_approvals the
 *               request moves to the next step or completes.
 *   reject()  → completes the request as rejected.
 */
class ApprovalService
{
    public function __construct(private readonly CurrentCompany $currentCompany) {}

    /**
     * @param  Model&HasApprovals  $document
     */
    public function submit(Model $document, User $requester): ?ApprovalRequest
    {
        $workflow = ApprovalWorkflow::query()
            ->activeFor($document->approvalDocumentType())
            ->with('steps')
            ->get()
            ->first(fn (ApprovalWorkflow $w) => $w->steps->isNotEmpty() && $w->matches($document));

        if ($workflow === null) {
            return null;
        }

        if ($document->isPendingApproval()) {
            throw ValidationException::withMessages(['approval' => __('This document is already awaiting approval.')]);
        }

        return DB::transaction(function () use ($workflow, $document, $requester): ApprovalRequest {
            $request = ApprovalRequest::query()->create([
                'approval_workflow_id' => $workflow->getKey(),
                'approvable_type' => $document->getMorphClass(),
                'approvable_id' => $document->getKey(),
                'current_step' => $workflow->steps->first()->step_order,
                'status' => 'pending',
                'requested_by' => $requester->getKey(),
            ]);

            $request->setRelation('workflow', $workflow);
            $this->notifyApprovers($request);
            ApprovalRequested::dispatch($request);

            return $request;
        });
    }

    public function approve(ApprovalRequest $request, User $user, ?string $comment = null): ApprovalRequest
    {
        return DB::transaction(function () use ($request, $user, $comment): ApprovalRequest {
            $request = ApprovalRequest::query()->lockForUpdate()->with('workflow.steps')->findOrFail($request->getKey());
            $step = $this->assertCanAct($request, $user);

            $this->record($request, $step, $user, 'approved', $comment);

            $approvals = $request->actions()
                ->where('approval_step_id', $step->getKey())
                ->where('action', 'approved')
                ->distinct('user_id')
                ->count('user_id');

            if ($approvals < $step->min_approvals) {
                return $request;
            }

            $next = $request->workflow->steps->first(fn (ApprovalStep $s) => $s->step_order > $step->step_order);

            if ($next !== null) {
                $request->update(['current_step' => $next->step_order]);
                $this->notifyApprovers($request);
                ApprovalRequested::dispatch($request);

                return $request;
            }

            return $this->complete($request, 'approved');
        });
    }

    public function reject(ApprovalRequest $request, User $user, string $comment): ApprovalRequest
    {
        return DB::transaction(function () use ($request, $user, $comment): ApprovalRequest {
            $request = ApprovalRequest::query()->lockForUpdate()->with('workflow.steps')->findOrFail($request->getKey());
            $step = $this->assertCanAct($request, $user);

            $this->record($request, $step, $user, 'rejected', $comment);

            return $this->complete($request, 'rejected');
        });
    }

    public function cancel(ApprovalRequest $request, User $user): ApprovalRequest
    {
        if (! $request->isPending()) {
            throw ValidationException::withMessages(['approval' => __('Only pending requests can be cancelled.')]);
        }

        if ($request->requested_by !== $user->getKey() && ! $user->isOwnerOfCurrentCompany()) {
            throw ValidationException::withMessages(['approval' => __('Only the requester can cancel this request.')]);
        }

        $request->update(['status' => 'cancelled', 'completed_at' => now()]);

        return $request;
    }

    /** Whether $user is an approver for the request's current step. */
    public function canAct(ApprovalRequest $request, User $user): bool
    {
        if (! $request->isPending()) {
            return false;
        }

        $step = $request->currentStepModel();
        if ($step === null) {
            return false;
        }

        $alreadyActed = $request->actions()
            ->where('approval_step_id', $step->getKey())
            ->where('user_id', $user->getKey())
            ->whereIn('action', ['approved', 'rejected'])
            ->exists();

        if ($alreadyActed) {
            return false;
        }

        return $this->approversFor($request, $step)->contains(fn (User $u) => $u->is($user));
    }

    /**
     * Pending requests the user can act on right now.
     *
     * @return Collection<int, ApprovalRequest>
     */
    public function pendingFor(User $user): Collection
    {
        return ApprovalRequest::query()
            ->pending()
            ->with(['workflow.steps', 'approvable', 'requester'])
            ->latest()
            ->get()
            ->filter(fn (ApprovalRequest $r) => $this->canAct($r, $user))
            ->values();
    }

    /**
     * Users who may act on a step.
     *
     * @return Collection<int, User>
     */
    public function approversFor(ApprovalRequest $request, ApprovalStep $step): Collection
    {
        $companyId = $this->currentCompany->id() ?? $request->company_id;

        return match ($step->approver_type) {
            'user' => User::query()->whereKey($step->approver_id)->active()->get(),
            'role' => User::query()
                ->active()
                ->whereHas('companies', fn (Builder $q) => $q->whereKey($companyId))
                ->whereHas('roles', fn (Builder $q) => $q->whereKey($step->approver_id)->where('roles.company_id', $companyId))
                ->get(),
            'department_manager' => $this->departmentManager($request, $companyId),
            default => collect(),
        };
    }

    private function departmentManager(ApprovalRequest $request, int $companyId): Collection
    {
        $departmentId = $request->requester?->companies()->whereKey($companyId)->first()?->pivot?->department_id;
        $managerId = $departmentId ? Department::query()->whereKey($departmentId)->value('manager_id') : null;

        return $managerId ? User::query()->whereKey($managerId)->active()->get() : collect();
    }

    private function assertCanAct(ApprovalRequest $request, User $user): ApprovalStep
    {
        if (! $this->canAct($request, $user)) {
            throw ValidationException::withMessages(['approval' => __('You are not allowed to act on this approval step.')]);
        }

        return $request->currentStepModel();
    }

    private function record(ApprovalRequest $request, ApprovalStep $step, User $user, string $action, ?string $comment): void
    {
        ApprovalAction::query()->create([
            'approval_request_id' => $request->getKey(),
            'approval_step_id' => $step->getKey(),
            'user_id' => $user->getKey(),
            'action' => $action,
            'comment' => $comment,
            'acted_at' => now(),
        ]);
    }

    private function complete(ApprovalRequest $request, string $status): ApprovalRequest
    {
        $request->update(['status' => $status, 'completed_at' => now()]);

        $document = $request->approvable;
        if ($document !== null && method_exists($document, 'onApprovalCompleted')) {
            $document->onApprovalCompleted($request);
        }

        if ($request->requester !== null) {
            $request->requester->notify(new ApprovalDecisionNotification($request));
        }

        ApprovalCompleted::dispatch($request);

        return $request;
    }

    private function notifyApprovers(ApprovalRequest $request): void
    {
        $step = $request->currentStepModel();
        if ($step === null) {
            return;
        }

        Notification::send($this->approversFor($request, $step), new ApprovalPendingNotification($request));
    }

    /** Role options for the workflow editor (current company only). */
    public function roleOptions(): Collection
    {
        return Role::query()->where('company_id', $this->currentCompany->id())->orderBy('name')->pluck('name', 'id');
    }
}
