<?php

declare(strict_types=1);

namespace Modules\Core\Http\Requests;

use App\Support\Tenancy\CurrentCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFiscalYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('core.fiscal_year.edit');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:40'],
            'start_date' => ['required', 'date', Rule::unique('fiscal_years', 'start_date')->where('company_id', app(CurrentCompany::class)->id())->ignore($this->route('fiscal_year')?->getKey())],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'is_closed' => ['boolean'],
            'closed_at' => ['nullable', 'date'],
        ];
    }
}
