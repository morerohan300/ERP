<?php

declare(strict_types=1);

namespace Modules\Core\Http\Requests;

use App\Support\Tenancy\CurrentCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Core\Models\CustomField;

class StoreCustomFieldRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('core.custom_field.create');
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('options'))) {
            $options = array_values(array_filter(array_map('trim', explode(',', $this->input('options'))), fn ($value) => $value !== ''));
            $this->merge(['options' => $options]);
        }
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'entity_type' => ['required', 'string', 'max:60'],
            'name' => ['required', 'string', 'max:60', 'regex:/^[a-z0-9_]+$/', Rule::unique('custom_fields', 'name')
                ->where('company_id', app(CurrentCompany::class)->id())
                ->where('entity_type', $this->input('entity_type'))],
            'label' => ['required', 'string', 'max:120'],
            'type' => ['required', 'string', Rule::in(CustomField::TYPES)],
            'options' => ['nullable', 'array'],
            'options.*' => ['string', 'max:255'],
            'default_value' => ['nullable', 'string', 'max:255'],
            'help_text' => ['nullable', 'string', 'max:255'],
            'is_required' => ['boolean'],
            'show_in_table' => ['boolean'],
            'is_active' => ['boolean'],
            'sort_order' => ['integer', 'min:0', 'max:65535'],
        ];
    }
}
