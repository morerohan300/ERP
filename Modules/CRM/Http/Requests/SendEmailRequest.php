<?php

declare(strict_types=1);

namespace Modules\CRM\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\CRM\Http\Requests\Concerns\ChecksCompanyRules;

class SendEmailRequest extends FormRequest
{
    use ChecksCompanyRules;

    public function authorize(): bool
    {
        return (bool) $this->user()?->can('crm.email.send');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'email_template_id' => ['nullable', $this->existsInCompany('email_templates')],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:20000'],
            'cc' => ['nullable', 'email', 'max:255'],
        ];
    }
}
