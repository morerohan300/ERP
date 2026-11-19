<?php

declare(strict_types=1);

namespace Modules\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Core\Models\Company;

class StoreCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Company::class);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'tax_number' => ['nullable', 'string', 'max:255'],
            'registration_number' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'website' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'array'],
            'base_currency_code' => ['nullable', 'string', 'size:3', Rule::exists('currencies', 'code')],
            'locale' => ['nullable', 'string', 'max:10'],
            'timezone' => ['nullable', 'string', 'max:64'],
            'fiscal_year_start_month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'date_format' => ['nullable', 'string', 'max:20'],
            'industry' => ['nullable', 'string', 'max:60'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ];
    }

    /** @return array<string, mixed> */
    protected function prepareForValidation(): void
    {
        if ($this->filled('base_currency_code')) {
            $this->merge(['base_currency_code' => mb_strtoupper((string) $this->input('base_currency_code'))]);
        }
    }
}
