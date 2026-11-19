<?php

declare(strict_types=1);

namespace Modules\CRM\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Core\Services\CustomFieldService;
use Modules\CRM\Http\Requests\Concerns\ChecksCompanyRules;

class LeadRequest extends FormRequest
{
    use ChecksCompanyRules;

    public function authorize(): bool
    {
        return $this->canWrite('crm.lead');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'title' => ['nullable', 'string', 'max:150'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'lead_source_id' => ['nullable', $this->existsInCompany('lead_sources')],
            'lead_status_id' => ['nullable', $this->existsInCompany('lead_statuses')->where('is_converted', false)],
            'owner_id' => ['nullable', $this->companyUser()],
            'branch_id' => ['nullable', $this->existsInCompany('branches')],
            'estimated_value' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
            'currency_code' => ['nullable', 'string', 'size:3'],
            'address' => ['nullable', 'array'],
            'address.line1' => ['nullable', 'string', 'max:255'],
            'address.city' => ['nullable', 'string', 'max:100'],
            'address.state' => ['nullable', 'string', 'max:100'],
            'address.postal_code' => ['nullable', 'string', 'max:20'],
            'address.country' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:5000'],
            'lost_reason' => ['nullable', 'string', 'max:255'],
            ...app(CustomFieldService::class)->rules('lead'),
        ];
    }
}
