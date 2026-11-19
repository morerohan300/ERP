<?php

declare(strict_types=1);

namespace Modules\CRM\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\CRM\Http\Requests\Concerns\ChecksCompanyRules;

class ConvertLeadRequest extends FormRequest
{
    use ChecksCompanyRules;

    public function authorize(): bool
    {
        return (bool) $this->user()?->can('convert', $this->route('lead'));
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'create_deal' => ['boolean'],
            'deal_title' => ['nullable', 'string', 'max:255'],
            'deal_value' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
            'account_id' => ['nullable', $this->existsInCompany('crm_accounts')],
        ];
    }
}
