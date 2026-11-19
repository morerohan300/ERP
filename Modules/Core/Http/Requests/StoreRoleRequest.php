<?php

declare(strict_types=1);

namespace Modules\Core\Http\Requests;

use App\Support\Tenancy\CurrentCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('core.role.create');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->where('company_id', app(CurrentCompany::class)->id())],
            'permissions' => ['array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')],
        ];
    }
}
