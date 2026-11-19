<?php

declare(strict_types=1);

namespace Modules\CRM\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\CRM\Http\Requests\Concerns\ChecksCompanyRules;
use Modules\CRM\Models\Ticket;

class TicketRequest extends FormRequest
{
    use ChecksCompanyRules;

    public function authorize(): bool
    {
        return $this->canWrite('crm.ticket');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'contact_id' => ['nullable', $this->existsInCompany('contacts')],
            'ticket_category_id' => ['nullable', $this->existsInCompany('ticket_categories')],
            'priority' => ['required', Rule::in(array_keys(Ticket::PRIORITIES))],
            'status' => [$this->isMethod('POST') ? 'prohibited' : 'required', Rule::in(array_keys(Ticket::STATUSES))],
            'assigned_to' => ['nullable', $this->companyUser()],
            'channel' => ['nullable', Rule::in(array_keys(Ticket::CHANNELS))],
        ];
    }
}
