<?php

declare(strict_types=1);

namespace Modules\Core\Http\Requests;

use App\Support\Tenancy\CurrentCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaxRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('core.tax.create');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:80'],
            'code' => ['required', 'string', 'max:30', Rule::unique('taxes', 'code')->where('company_id', app(CurrentCompany::class)->id())],
            'rate' => ['required', 'numeric', 'min:0'],
            'type' => ['required', 'string', Rule::in(['percent', 'fixed'])],
            'scope' => ['required', 'string', Rule::in(['sales', 'purchase', 'both'])],
            'is_compound' => ['boolean'],
            'is_inclusive' => ['boolean'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
        ];
    }
}
