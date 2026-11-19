<?php

declare(strict_types=1);

namespace App\Support\Money;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Modules\Core\Models\ExchangeRate;
use RuntimeException;

/**
 * Converts amounts using the company's exchange-rate table. Uses the most
 * recent rate on or before the given date; falls back to the inverse pair.
 */
final class CurrencyConverter
{
    public function rate(string $from, string $to, ?CarbonInterface $date = null): string
    {
        $from = strtoupper($from);
        $to = strtoupper($to);

        if ($from === $to) {
            return '1.00000000';
        }

        $date ??= Carbon::today();

        $direct = ExchangeRate::query()
            ->where('from_currency', $from)
            ->where('to_currency', $to)
            ->whereDate('effective_date', '<=', $date)
            ->orderByDesc('effective_date')
            ->value('rate');

        if ($direct !== null) {
            return (string) $direct;
        }

        $inverse = ExchangeRate::query()
            ->where('from_currency', $to)
            ->where('to_currency', $from)
            ->whereDate('effective_date', '<=', $date)
            ->orderByDesc('effective_date')
            ->value('rate');

        if ($inverse !== null && bccomp((string) $inverse, '0', 8) !== 0) {
            return bcdiv('1', (string) $inverse, 8);
        }

        throw new RuntimeException("No exchange rate found for {$from} → {$to} on {$date->toDateString()}.");
    }

    public function convert(Money $money, string $to, ?CarbonInterface $date = null): Money
    {
        $rate = $this->rate($money->currency(), $to, $date);

        return Money::of($money->multiply($rate)->amount(), $to);
    }
}
