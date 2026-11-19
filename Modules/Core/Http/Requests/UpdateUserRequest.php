<?php

declare(strict_types=1);

namespace Modules\Core\Http\Requests;

use App\Support\Tenancy\CurrentCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('core.user.edit');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $companyId = app(CurrentCompany::class)->id();

        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'roles' => ['array'],
            'roles.*' => ['string', Rule::exists('roles', 'name')->where('company_id', $companyId)],
            'branch_ids' => ['array'],
            'branch_ids.*' => ['integer', Rule::exists('branches', 'id')->where('company_id', $companyId)],
            'department_id' => ['nullable', Rule::exists('departments', 'id')->where('company_id', $companyId)],
            'job_title' => ['nullable', 'string', 'max:255'],
            'locale' => ['nullable', 'string', 'max:10'],
            'timezone' => ['nullable', 'string', 'max:64'],
            'is_active' => ['boolean'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
