<?php

declare(strict_types=1);

namespace Modules\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCommunicationSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('core.settings.edit');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'settings.mail_mailer' => ['required', 'string', Rule::in(['smtp', 'log', 'array'])],
            'settings.mail_host' => ['nullable', 'string', 'max:255'],
            'settings.mail_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'settings.mail_username' => ['nullable', 'string', 'max:255'],
            'settings.mail_password' => ['nullable', 'string', 'max:255'],
            'settings.mail_from_address' => ['nullable', 'email', 'max:255'],
            'settings.mail_from_name' => ['nullable', 'string', 'max:255'],
            'settings.sms_provider' => ['nullable', 'string', 'max:50'],
            'settings.sms_api_key' => ['nullable', 'string', 'max:255'],
            'settings.sms_api_secret' => ['nullable', 'string', 'max:255'],
        ];
    }
}
