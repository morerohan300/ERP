<?php

declare(strict_types=1);

namespace Modules\CRM\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Core\Services\CustomFieldService;
use Modules\CRM\Http\Requests\Concerns\ChecksCompanyRules;

class AccountRequest extends FormRequest
{
    use ChecksCompanyRules;

    public function authorize(): bool
    {
        return $this->canWrite('crm.account');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $self = $this->route('account')?->getKey();

        $address = fn (string $key): array => [
            $key => ['nullable', 'array'],
            "{$key}.line1" => ['nullable', 'string', 'max:255'],
            "{$key}.city" => ['nullable', 'string', 'max:100'],
            "{$key}.state" => ['nullable', 'string', 'max:100'],
            "{$key}.postal_code" => ['nullable', 'string', 'max:20'],
            "{$key}.country" => ['nullable', 'string', 'max:100'],
        ];

        return [
            'name' => ['required', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'website' => ['nullable', 'string', 'max:255'],
            'industry' => ['nullable', 'string', 'max:100'],
            'employee_count' => ['nullable', 'integer', 'min:0', 'max:10000000'],
            'annual_revenue' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
            'tax_number' => ['nullable', 'string', 'max:50'],
            'owner_id' => ['nullable', $this->companyUser()],
            'parent_id' => array_filter(['nullable', $this->existsInCompany('crm_accounts'), $self !== null ? Rule::notIn([$self]) : null]),
            ...$address('billing_address'),
            ...$address('shipping_address'),
            ...app(CustomFieldService::class)->rules('crm_account'),
        ];
    }
}
