<?php

declare(strict_types=1);

namespace Modules\Core\Http\Controllers\Web;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Core\Http\Requests\UpdateCommunicationSettingsRequest;
use Modules\Core\Http\Requests\UpdateGeneralSettingsRequest;
use Modules\Core\Http\Requests\UpdatePaymentSettingsRequest;
use Modules\Core\Services\SettingsService;

final class SettingController extends Controller
{
    public function __construct(private readonly SettingsService $settings) {}

    public function general(): View
    {
        return view('core::settings.general', [
            'settings' => $this->settings->group('general'),
            'dateFormats' => config('locales.date_formats'),
            'locales' => config('locales.supported'),
        ]);
    }

    public function updateGeneral(UpdateGeneralSettingsRequest $request): RedirectResponse
    {
        $this->settings->setMany('general', $request->validated()['settings']);

        return $this->status('settings.general', __('General settings saved.'));
    }

    public function communication(): View
    {
        return view('core::settings.communication', [
            'mail' => $this->settings->group('mail'),
            'sms' => $this->settings->group('sms'),
            'secrets' => [
                'mail.password' => $this->settings->hasSecret('mail', 'password'),
                'sms.api_key' => $this->settings->hasSecret('sms', 'api_key'),
            ],
        ]);
    }

    public function updateCommunication(UpdateCommunicationSettingsRequest $request): RedirectResponse
    {
        $values = $request->validated()['settings'];

        $this->settings->setMany('mail', [
            'mailer' => $values['mail_mailer'],
            'host' => $values['mail_host'] ?? null,
            'port' => $values['mail_port'] ?? null,
            'username' => $values['mail_username'] ?? null,
            'password' => $values['mail_password'] ?? null,
            'from_address' => $values['mail_from_address'] ?? null,
            'from_name' => $values['mail_from_name'] ?? null,
        ]);

        $this->settings->setMany('sms', [
            'provider' => $values['sms_provider'] ?? null,
            'api_key' => $values['sms_api_key'] ?? null,
            'api_secret' => $values['sms_api_secret'] ?? null,
        ]);

        return $this->status('settings.communication', __('Communication settings saved.'));
    }

    public function payments(): View
    {
        return view('core::settings.payments', [
            'settings' => $this->settings->group('payments'),
        ]);
    }

    public function updatePayments(UpdatePaymentSettingsRequest $request): RedirectResponse
    {
        $this->settings->setMany('payments', $request->validated()['settings']);

        return $this->status('settings.payments', __('Payment settings saved.'));
    }
}
