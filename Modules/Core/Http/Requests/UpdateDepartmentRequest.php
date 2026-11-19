<?php

declare(strict_types=1);

namespace Modules\Core\Http\Requests;

use App\Support\Tenancy\CurrentCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('core.department.edit');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $companyId = app(CurrentCompany::class)->id();
        $department = $this->route('department');

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:20', Rule::unique('departments', 'code')->where('company_id', $companyId)->ignore($department?->getKey())],
            'branch_id' => ['nullable', Rule::exists('branches', 'id')->where('company_id', $companyId)],
            'parent_id' => ['nullable', Rule::exists('departments', 'id')->where('company_id', $companyId)],
            'manager_id' => ['nullable', Rule::exists('users', 'id')],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
