<?php

declare(strict_types=1);

namespace Modules\CRM\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Throwable;

/**
 * Converts between the signed-in user's timezone (what forms show and accept) and
 * UTC (what the database stores).
 */
final class UserTime
{
    public static function zone(): string
    {
        return auth()->user()?->timezone ?: config('app.timezone');
    }

    /** Parse a form value typed in the user's timezone into the app (storage) timezone. */
    public static function fromInput(?string $value): ?Carbon
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value, self::zone())->setTimezone(config('app.timezone'));
        } catch (Throwable) {
            return null;
        }
    }

    /** Value for an <input type="datetime-local"> in the user's timezone. */
    public static function forInput(?CarbonInterface $value): string
    {
        return $value === null ? '' : Carbon::instance($value)->setTimezone(self::zone())->format('Y-m-d\TH:i');
    }

    /** Human readable date and time in the user's timezone. */
    public static function display(?CarbonInterface $value, string $format = 'd M Y, H:i'): string
    {
        return $value === null ? '—' : Carbon::instance($value)->setTimezone(self::zone())->format($format);
    }
}
