<?php

declare(strict_types=1);

namespace App\Support\Money;

use InvalidArgumentException;
use JsonSerializable;
use Stringable;

/**
 * Immutable money value using bcmath string arithmetic (4 decimal places)
 * so financial totals never suffer from float rounding.
 */
final class Money implements JsonSerializable, Stringable
{
    public const SCALE = 4;

    private function __construct(
        private readonly string $amount,
        private readonly string $currency,
    ) {}

    public static function of(string|int|float $amount, string $currency): self
    {
        if (strlen($currency) !== 3) {
            throw new InvalidArgumentException("Invalid currency code [{$currency}].");
        }

        return new self(self::normalize($amount), strtoupper($currency));
    }

    public static function zero(string $currency): self
    {
        return self::of('0', $currency);
    }

    public function amount(): string
    {
        return $this->amount;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function plus(self $other): self
    {
        $this->assertSameCurrency($other);

        return new self(bcadd($this->amount, $other->amount, self::SCALE), $this->currency);
    }

    public function minus(self $other): self
    {
        $this->assertSameCurrency($other);

        return new self(bcsub($this->amount, $other->amount, self::SCALE), $this->currency);
    }

    public function multiply(string|int|float $factor): self
    {
        return new self(self::round(bcmul($this->amount, self::normalize($factor, 8), 8)), $this->currency);
    }

    public function divide(string|int|float $divisor): self
    {
        $divisor = self::normalize($divisor, 8);
        if (bccomp($divisor, '0', 8) === 0) {
            throw new InvalidArgumentException('Division by zero.');
        }

        return new self(self::round(bcdiv($this->amount, $divisor, 8)), $this->currency);
    }

    /** Percentage of this amount, e.g. ->percent('18') for 18% tax. */
    public function percent(string|int|float $rate): self
    {
        return new self(self::round(bcdiv(bcmul($this->amount, self::normalize($rate, 8), 8), '100', 8)), $this->currency);
    }

    public function isZero(): bool
    {
        return bccomp($this->amount, '0', self::SCALE) === 0;
    }

    public function isNegative(): bool
    {
        return bccomp($this->amount, '0', self::SCALE) < 0;
    }

    public function equals(self $other): bool
    {
        return $this->currency === $other->currency && bccomp($this->amount, $other->amount, self::SCALE) === 0;
    }

    public function compare(self $other): int
    {
        $this->assertSameCurrency($other);

        return bccomp($this->amount, $other->amount, self::SCALE);
    }

    /** Round to the currency's display precision (e.g. 2 for USD, 0 for JPY). */
    public function toDecimals(int $decimals = 2): string
    {
        return self::roundTo($this->amount, $decimals);
    }

    public function format(?string $locale = null, int $decimals = 2): string
    {
        $locale ??= app()->getLocale();
        $formatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
        $formatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, $decimals);

        return (string) $formatter->formatCurrency((float) $this->toDecimals($decimals), $this->currency);
    }

    public function jsonSerialize(): array
    {
        return ['amount' => $this->amount, 'currency' => $this->currency];
    }

    public function __toString(): string
    {
        return $this->amount.' '.$this->currency;
    }

    private function assertSameCurrency(self $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException("Currency mismatch: {$this->currency} vs {$other->currency}.");
        }
    }

    private static function normalize(string|int|float $value, int $scale = self::SCALE): string
    {
        if (is_float($value)) {
            $value = sprintf('%.'.($scale + 2).'F', $value);
        }

        $value = trim((string) $value);
        if (! is_numeric($value)) {
            throw new InvalidArgumentException("Invalid amount [{$value}].");
        }

        return self::roundTo($value, $scale);
    }

    private static function round(string $value): string
    {
        return self::roundTo($value, self::SCALE);
    }

    /** Half-up rounding for bcmath strings. */
    public static function roundTo(string $value, int $scale): string
    {
        $offset = '0.'.str_repeat('0', $scale).'5';
        $result = str_starts_with($value, '-')
            ? bcsub($value, $offset, $scale)
            : bcadd($value, $offset, $scale);

        return bccomp($result, '0', $scale) === 0 ? bcadd('0', '0', $scale) : $result;
    }
}
