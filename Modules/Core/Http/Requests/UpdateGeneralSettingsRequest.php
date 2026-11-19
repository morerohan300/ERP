<?php

declare(strict_types=1);

namespace Modules\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGeneralSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('core.settings.edit');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'settings.date_format' => ['required', 'string', Rule::in(array_keys(config('locales.date_formats')))],
            'settings.timezone' => ['required', 'string', 'max:64'],
            'settings.locale' => ['required', 'string', Rule::in(array_keys(config('locales.supported')))],
            'settings.fiscal_year_start_month' => ['required', 'integer', 'min:1', 'max:12'],
            'settings.invoice_terms' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
