<?php

declare(strict_types=1);

namespace Modules\CRM\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\CRM\Http\Requests\Concerns\ChecksCompanyRules;

/** A customer opening a ticket from the portal. */
class PortalTicketRequest extends FormRequest
{
    use ChecksCompanyRules;

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:10000'],
            'ticket_category_id' => ['nullable', $this->existsInCompany('ticket_categories')],
            'priority' => ['nullable', Rule::in(['low', 'medium', 'high'])],
        ];
    }
}
