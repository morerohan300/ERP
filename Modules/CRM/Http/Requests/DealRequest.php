<?php

declare(strict_types=1);

namespace Modules\CRM\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Core\Services\CustomFieldService;
use Modules\CRM\Http\Requests\Concerns\ChecksCompanyRules;

class DealRequest extends FormRequest
{
    use ChecksCompanyRules;

    public function authorize(): bool
    {
        return $this->canWrite('crm.deal');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $deal = $this->route('deal');
        $pipelineId = $deal?->pipeline_id ?? $this->input('pipeline_id');

        return [
            'title' => ['required', 'string', 'max:255'],
            // A deal stays in the pipeline it was created in.
            'pipeline_id' => $deal === null ? ['required', $this->existsInCompany('pipelines')] : ['prohibited'],
            'pipeline_stage_id' => [
                $deal === null ? 'nullable' : 'required',
                $this->existsInCompany('pipeline_stages')->where('pipeline_id', $pipelineId)->where('is_won', false)->where('is_lost', false),
            ],
            'contact_id' => ['nullable', $this->existsInCompany('contacts')],
            'crm_account_id' => ['nullable', $this->existsInCompany('crm_accounts')],
            'owner_id' => ['nullable', $this->companyUser()],
            'branch_id' => ['nullable', $this->existsInCompany('branches')],
            'value' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
            'currency_code' => ['nullable', 'string', 'size:3'],
            'expected_close_date' => ['nullable', 'date'],
            'probability' => ['nullable', 'integer', 'between:0,100'],
            'description' => ['nullable', 'string', 'max:5000'],
            'products' => ['nullable', 'array', 'max:100'],
            'products.*.description' => ['required_with:products.*.unit_price', 'nullable', 'string', 'max:255'],
            'products.*.quantity' => ['nullable', 'numeric', 'min:0.0001', 'max:999999999'],
            'products.*.unit_price' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
            'products.*.discount_percent' => ['nullable', 'numeric', 'between:0,100'],
            ...app(CustomFieldService::class)->rules('deal'),
        ];
    }

    /**
     * Line items keep the order they were submitted in; blank rows are dropped by DealService.
     *
     * @return array<int, array<string, mixed>>|null null when the form had no product section
     */
    public function lines(): ?array
    {
        return $this->has('products') ? array_values((array) $this->validated('products', [])) : null;
    }

    /** @return array<string, mixed> */
    public function payload(): array
    {
        return $this->safe()->except(['custom_fields', 'products']);
    }
}
