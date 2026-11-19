<?php

declare(strict_types=1);

namespace Modules\CRM\Services;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * Adds working minutes to a start time according to a business-hours schedule.
 *
 * The schedule is {"timezone": "Europe/London", "days": {"mon": ["09:00", "17:00"], ...}}.
 * Days that are missing are closed. A null or empty schedule means 24/7 and
 * simply adds the minutes.
 */
final class BusinessHours
{
    /** Never look further than this many days ahead (guards against schedules with no open day). */
    private const MAX_DAYS = 400;

    /**
     * @param  array{timezone?: string, days?: array<string, array{0: string, 1: string}>}|null  $schedule
     */
    public function addMinutes(CarbonInterface $start, int $minutes, ?array $schedule): CarbonImmutable
    {
        $start = CarbonImmutable::instance($start);
        $days = $schedule['days'] ?? [];

        if ($days === [] || $minutes <= 0) {
            return $start->addMinutes($minutes);
        }

        $cursor = $start->setTimezone($schedule['timezone'] ?? 'UTC');
        $remaining = $minutes * 60;

        for ($i = 0; $i < self::MAX_DAYS; $i++) {
            $window = $this->window($cursor, $days);

            if ($window !== null) {
                [$open, $close] = $window;
                $from = $cursor->greaterThan($open) ? $cursor : $open;
                $available = $close->getTimestamp() - $from->getTimestamp();

                if ($available > 0) {
                    if ($remaining <= $available) {
                        return $from->addSeconds($remaining)->setTimezone($start->getTimezone());
                    }

                    $remaining -= $available;
                }
            }

            $cursor = $cursor->addDay()->startOfDay();
        }

        return $start->addMinutes($minutes);
    }

    /**
     * Opening and closing time of the day $date falls on, or null when closed.
     *
     * @param  array<string, array{0: string, 1: string}>  $days
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}|null
     */
    private function window(CarbonImmutable $date, array $days): ?array
    {
        $hours = $days[strtolower($date->format('D'))] ?? null;

        if (! is_array($hours) || count($hours) < 2) {
            return null;
        }

        $open = $date->setTimeFromTimeString((string) $hours[0]);
        $close = $date->setTimeFromTimeString((string) $hours[1]);

        return $close->greaterThan($open) ? [$open, $close] : null;
    }
}
