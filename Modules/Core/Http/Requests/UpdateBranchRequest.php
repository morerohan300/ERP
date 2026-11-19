<?php

declare(strict_types=1);

namespace Modules\Core\Http\Requests;

use App\Support\Tenancy\CurrentCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('core.branch.edit');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $branch = $this->route('branch');

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:20', Rule::unique('branches', 'code')
                ->where('company_id', app(CurrentCompany::class)->id())
                ->ignore($branch?->getKey())],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'address' => ['nullable', 'array'],
            'is_head_office' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }
}
