<?php

declare(strict_types=1);

namespace Modules\CRM\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Core\Services\CustomFieldService;
use Modules\CRM\Http\Requests\Concerns\ChecksCompanyRules;

class ContactRequest extends FormRequest
{
    use ChecksCompanyRules;

    public function authorize(): bool
    {
        return $this->canWrite('crm.contact');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'job_title' => ['nullable', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'mobile' => ['nullable', 'string', 'max:40'],
            'crm_account_id' => ['nullable', $this->existsInCompany('crm_accounts')],
            'owner_id' => ['nullable', $this->companyUser()],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'do_not_email' => ['boolean'],
            'address' => ['nullable', 'array'],
            'address.line1' => ['nullable', 'string', 'max:255'],
            'address.city' => ['nullable', 'string', 'max:100'],
            'address.state' => ['nullable', 'string', 'max:100'],
            'address.postal_code' => ['nullable', 'string', 'max:20'],
            'address.country' => ['nullable', 'string', 'max:100'],
            ...app(CustomFieldService::class)->rules('contact'),
        ];
    }
}
