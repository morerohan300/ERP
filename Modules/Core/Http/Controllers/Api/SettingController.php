<?php

declare(strict_types=1);

namespace Modules\Core\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Modules\Core\Services\SettingsService;

final class SettingController extends Controller
{
    public function __construct(private readonly SettingsService $settings) {}

    public function index(): JsonResponse
    {
        Gate::authorize('core.settings.view');

        return response()->json([
            'general' => $this->settings->group('general'),
            'mail' => $this->sanitize($this->settings->group('mail'), ['password']),
            'sms' => $this->sanitize($this->settings->group('sms'), ['api_key', 'api_secret', 'auth_token']),
            'payments' => $this->sanitize($this->settings->group('payments'), [
                'stripe_secret', 'stripe_webhook_secret', 'paypal_secret', 'razorpay_secret',
            ]),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        Gate::authorize('core.settings.edit');
        $data = $request->validate(['group' => ['required', 'string', 'max:40'], 'settings' => ['required', 'array']]);
        $this->settings->setMany($data['group'], $data['settings']);

        return response()->json(['message' => __('Settings saved.')]);
    }

    /** @param array<string, mixed> $values @param array<int, string> $secrets */
    private function sanitize(array $values, array $secrets): array
    {
        foreach ($secrets as $key) {
            if (array_key_exists($key, $values)) {
                $values[$key] = $values[$key] === null ? null : '••••••••';
            }
        }

        return $values;
    }
}
