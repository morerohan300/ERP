<?php

declare(strict_types=1);

namespace Modules\CRM\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\CRM\Http\Requests\Concerns\ChecksCompanyRules;

class EmailTemplateRequest extends FormRequest
{
    use ChecksCompanyRules;

    public function authorize(): bool
    {
        return $this->canWrite('crm.email_template');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', $this->uniqueInCompany('email_templates', 'name', $this->route('email_template')?->getKey())],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:50000'],
            'is_active' => ['boolean'],
        ];
    }
}
