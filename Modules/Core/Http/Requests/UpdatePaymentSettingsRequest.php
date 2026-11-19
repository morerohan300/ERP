<?php

declare(strict_types=1);

namespace Modules\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('core.settings.edit');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'settings.stripe_enabled' => ['boolean'],
            'settings.stripe_key' => ['nullable', 'string', 'max:255'],
            'settings.stripe_secret' => ['nullable', 'string', 'max:255'],
            'settings.stripe_webhook_secret' => ['nullable', 'string', 'max:255'],
            'settings.paypal_enabled' => ['boolean'],
            'settings.paypal_client_id' => ['nullable', 'string', 'max:255'],
            'settings.paypal_secret' => ['nullable', 'string', 'max:255'],
            'settings.razorpay_enabled' => ['boolean'],
            'settings.razorpay_key' => ['nullable', 'string', 'max:255'],
            'settings.razorpay_secret' => ['nullable', 'string', 'max:255'],
        ];
    }
}
