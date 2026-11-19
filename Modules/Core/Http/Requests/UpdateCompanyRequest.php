<?php

declare(strict_types=1);

namespace Modules\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('company'));
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
            'base_currency_code' => ['required', 'string', 'size:3', Rule::exists('currencies', 'code')],
            'locale' => ['required', 'string', 'max:10'],
            'timezone' => ['required', 'string', 'max:64'],
            'fiscal_year_start_month' => ['required', 'integer', 'min:1', 'max:12'],
            'date_format' => ['required', 'string', Rule::in(array_keys(config('locales.date_formats')))],
            'industry' => ['nullable', 'string', 'max:60'],
            'is_active' => ['boolean'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
