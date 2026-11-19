<?php

declare(strict_types=1);

namespace Modules\Core\Http\Requests;

use App\Support\Tenancy\CurrentCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('core.branch.create');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:20', Rule::unique('branches', 'code')->where('company_id', app(CurrentCompany::class)->id())],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'address' => ['nullable', 'array'],
            'is_head_office' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ['code.unique' => __('A branch with this code already exists.')];
    }
}
