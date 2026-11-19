<?php

declare(strict_types=1);

namespace Modules\Core\Services;

use App\Support\Tenancy\CurrentCompany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Modules\Core\Models\Setting;

/**
 * Typed, cached access to key/value settings. Settings are grouped
 * (general, mail, sms, payments ...) and stored per company; values in
 * $encryptedKeys are encrypted at rest.
 */
class SettingsService
{
    /** Keys whose values are always encrypted at rest. */
    public const ENCRYPTED_KEYS = [
        'mail.password',
        'sms.api_key', 'sms.api_secret', 'sms.auth_token',
        'payments.stripe_secret', 'payments.stripe_webhook_secret',
        'payments.paypal_secret', 'payments.razorpay_secret',
    ];

    public function __construct(private readonly CurrentCompany $currentCompany) {}

    public function get(string $group, string $key, mixed $default = null, ?int $companyId = null): mixed
    {
        return $this->group($group, $companyId)[$key] ?? $default;
    }

    /**
     * All settings in a group for the company (company values override globals).
     *
     * @return array<string, mixed>
     */
    public function group(string $group, ?int $companyId = null): array
    {
        $companyId ??= $this->currentCompany->id();

        return Cache::remember($this->cacheKey($group, $companyId), now()->addDay(), function () use ($group, $companyId): array {
            return Setting::query()
                ->where('group', $group)
                ->where(fn ($q) => $q->whereNull('company_id')->when($companyId, fn ($q) => $q->orWhere('company_id', $companyId)))
                ->orderByRaw('company_id IS NULL DESC')
                ->get()
                ->mapWithKeys(fn (Setting $s) => [$s->key => $this->decode($s)])
                ->all();
        });
    }

    public function set(string $group, string $key, mixed $value, ?int $companyId = null): void
    {
        $this->setMany($group, [$key => $value], $companyId);
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public function setMany(string $group, array $values, ?int $companyId = null): void
    {
        $companyId ??= $this->currentCompany->id();

        foreach ($values as $key => $value) {
            $encrypted = in_array("{$group}.{$key}", self::ENCRYPTED_KEYS, true);

            // Leaving a secret field blank keeps the stored value.
            if ($encrypted && ($value === null || $value === '')) {
                continue;
            }

            $json = json_encode($value, JSON_THROW_ON_ERROR);

            Setting::query()->updateOrCreate(
                ['company_id' => $companyId, 'group' => $group, 'key' => $key],
                ['value' => $encrypted ? Crypt::encryptString($json) : $json, 'is_encrypted' => $encrypted],
            );
        }

        Cache::forget($this->cacheKey($group, $companyId));
    }

    /** Whether a secret has been stored (without revealing it). */
    public function hasSecret(string $group, string $key, ?int $companyId = null): bool
    {
        return $this->get($group, $key, null, $companyId) !== null;
    }

    private function decode(Setting $setting): mixed
    {
        if ($setting->value === null) {
            return null;
        }

        $raw = $setting->is_encrypted ? Crypt::decryptString($setting->value) : $setting->value;

        return json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    }

    private function cacheKey(string $group, ?int $companyId): string
    {
        return 'settings:'.($companyId ?? 'global').':'.$group;
    }
}
