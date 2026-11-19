<?php

declare(strict_types=1);

namespace Modules\CRM\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Modules\CRM\Http\Requests\Concerns\ChecksCompanyRules;

/** Staff giving a contact access to the support portal. */
class PortalUserRequest extends FormRequest
{
    use ChecksCompanyRules;

    public function authorize(): bool
    {
        return (bool) $this->user()?->can('managePortal', $this->route('contact'));
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'password' => ['required', 'confirmed', Password::min(10)->letters()->numbers()],
        ];
    }
}
