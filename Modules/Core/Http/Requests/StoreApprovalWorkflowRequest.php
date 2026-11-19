<?php

declare(strict_types=1);

namespace Modules\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreApprovalWorkflowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('core.approval_workflow.create');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'document_type' => ['required', 'string', 'max:60', Rule::in(array_keys(config('numbering.defaults')))],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'conditions' => ['nullable', 'array'],
            'priority' => ['integer', 'min:0', 'max:65535'],
            'is_active' => ['boolean'],
            'steps' => ['required', 'array', 'min:1'],
            'steps.*.name' => ['required', 'string', 'max:255'],
            'steps.*.approver_type' => ['required', 'string', Rule::in(['user', 'role', 'department_manager'])],
            'steps.*.approver_id' => ['nullable', 'integer'],
            'steps.*.min_approvals' => ['integer', 'min:1', 'max:255'],
        ];
    }
}
