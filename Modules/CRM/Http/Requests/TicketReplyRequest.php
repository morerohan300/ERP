<?php

declare(strict_types=1);

namespace Modules\CRM\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TicketReplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('update', $this->route('ticket'));
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:10000'],
            'is_internal' => ['boolean'],
        ];
    }
}
