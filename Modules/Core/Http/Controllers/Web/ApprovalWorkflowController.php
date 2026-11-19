<?php

declare(strict_types=1);

namespace Modules\Core\Http\Controllers\Web;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Modules\Core\Http\Requests\StoreApprovalWorkflowRequest;
use Modules\Core\Http\Requests\UpdateApprovalWorkflowRequest;
use Modules\Core\Models\ApprovalStep;
use Modules\Core\Models\ApprovalWorkflow;
use Modules\Core\Services\ApprovalService;

final class ApprovalWorkflowController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(ApprovalWorkflow::class, 'approval_workflow');
    }

    public function index(): View
    {
        return view('core::approval-workflows.index', [
            'workflows' => ApprovalWorkflow::query()->with('steps')->orderBy('document_type')->get(),
        ]);
    }

    public function create(ApprovalService $approvals): View
    {
        return view('core::approval-workflows.create', [
            'documentTypes' => array_keys(config('numbering.defaults')),
            'roles' => $approvals->roleOptions(),
            'users' => User::query()->inCurrentCompany()->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function store(StoreApprovalWorkflowRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $steps = $data['steps'];
        unset($data['steps']);

        DB::transaction(function () use ($data, $steps): void {
            $workflow = ApprovalWorkflow::query()->create($data);
            $this->syncSteps($workflow, $steps);
        });

        return $this->status('approval-workflows.index', __('Approval workflow created.'));
    }

    public function show(ApprovalWorkflow $approvalWorkflow): RedirectResponse
    {
        return redirect()->route('approval-workflows.edit', $approvalWorkflow);
    }

    public function edit(ApprovalWorkflow $approvalWorkflow, ApprovalService $approvals): View
    {
        return view('core::approval-workflows.edit', [
            'workflow' => $approvalWorkflow->load('steps'),
            'documentTypes' => array_keys(config('numbering.defaults')),
            'roles' => $approvals->roleOptions(),
            'users' => User::query()->inCurrentCompany()->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function update(UpdateApprovalWorkflowRequest $request, ApprovalWorkflow $approvalWorkflow): RedirectResponse
    {
        $data = $request->validated();
        $steps = $data['steps'];
        unset($data['steps']);

        DB::transaction(function () use ($approvalWorkflow, $data, $steps): void {
            $approvalWorkflow->update($data);
            $this->syncSteps($approvalWorkflow, $steps);
        });

        return $this->status('approval-workflows.index', __('Approval workflow updated.'));
    }

    public function destroy(ApprovalWorkflow $approvalWorkflow): RedirectResponse
    {
        $approvalWorkflow->delete();

        return $this->status('approval-workflows.index', __('Approval workflow deleted.'));
    }

    /** @param array<int, array<string, mixed>> $steps */
    private function syncSteps(ApprovalWorkflow $workflow, array $steps): void
    {
        $keep = [];
        foreach (array_values($steps) as $index => $step) {
            $model = ApprovalStep::query()->updateOrCreate(
                ['approval_workflow_id' => $workflow->getKey(), 'step_order' => $index + 1],
                [
                    'name' => $step['name'],
                    'approver_type' => $step['approver_type'],
                    'approver_id' => $step['approver_type'] === 'department_manager' ? null : ($step['approver_id'] ?? null),
                    'min_approvals' => $step['min_approvals'] ?? 1,
                ],
            );
            $keep[] = $model->getKey();
        }

        ApprovalStep::query()
            ->where('approval_workflow_id', $workflow->getKey())
            ->whereNotIn('id', $keep)
            ->delete();
    }
}
