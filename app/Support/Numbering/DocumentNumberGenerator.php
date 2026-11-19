<?php

declare(strict_types=1);

namespace App\Support\Numbering;

use App\Support\Tenancy\CurrentCompany;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Core\Models\NumberSequence;

/**
 * Generates gap-free, per-company document numbers such as INV-2026-0001.
 *
 * The sequence row is locked with SELECT ... FOR UPDATE so concurrent
 * requests never receive the same number. Call it inside the same database
 * transaction that saves the document so a rollback also releases the number.
 *
 * Format tokens: {PREFIX} {YYYY} {YY} {MM} {DD} {SEQ} {SEQ:n}
 */
final class DocumentNumberGenerator
{
    public function __construct(private readonly CurrentCompany $currentCompany) {}

    public function next(string $documentType, ?CarbonInterface $date = null): string
    {
        $date ??= Carbon::now();
        $companyId = $this->currentCompany->getOrFail()->getKey();

        return DB::transaction(function () use ($documentType, $date, $companyId): string {
            /** @var NumberSequence|null $sequence */
            $sequence = NumberSequence::query()
                ->where('company_id', $companyId)
                ->where('document_type', $documentType)
                ->lockForUpdate()
                ->first();

            if ($sequence === null) {
                $defaults = config("numbering.defaults.{$documentType}")
                    ?? ['prefix' => strtoupper(substr($documentType, 0, 3)), 'format' => '{PREFIX}-{YYYY}-{SEQ:4}', 'padding' => 4, 'reset_period' => 'yearly'];

                $sequence = NumberSequence::query()->create([
                    'company_id' => $companyId,
                    'document_type' => $documentType,
                    'prefix' => $defaults['prefix'],
                    'format' => $defaults['format'],
                    'padding' => $defaults['padding'] ?? 4,
                    'reset_period' => $defaults['reset_period'] ?? 'yearly',
                    'next_number' => 1,
                    'last_reset_at' => $date,
                ]);

                // Re-read under lock so concurrent creators serialise on the row.
                $sequence = NumberSequence::query()->whereKey($sequence->getKey())->lockForUpdate()->firstOrFail();
            }

            if ($this->shouldReset($sequence, $date)) {
                $sequence->next_number = 1;
                $sequence->last_reset_at = $date;
            }

            $number = $this->format($sequence, $sequence->next_number, $date);

            $sequence->next_number++;
            $sequence->save();

            return $number;
        });
    }

    /** Preview the next number without consuming it (for forms). */
    public function preview(string $documentType, ?CarbonInterface $date = null): string
    {
        $date ??= Carbon::now();
        $sequence = NumberSequence::query()
            ->where('company_id', $this->currentCompany->getOrFail()->getKey())
            ->where('document_type', $documentType)
            ->first();

        if ($sequence === null) {
            $defaults = config("numbering.defaults.{$documentType}") ?? ['prefix' => strtoupper(substr($documentType, 0, 3)), 'format' => '{PREFIX}-{YYYY}-{SEQ:4}', 'padding' => 4];
            $sequence = new NumberSequence($defaults + ['next_number' => 1]);
        }

        $next = $this->shouldReset($sequence, $date) ? 1 : (int) $sequence->next_number;

        return $this->format($sequence, $next, $date);
    }

    public function format(NumberSequence $sequence, int $number, CarbonInterface $date): string
    {
        $padding = (int) ($sequence->padding ?: 4);

        $result = preg_replace_callback('/\{SEQ(?::(\d+))?\}/', function (array $m) use ($number, $padding): string {
            $width = isset($m[1]) ? (int) $m[1] : $padding;

            return str_pad((string) $number, $width, '0', STR_PAD_LEFT);
        }, (string) $sequence->format);

        return strtr((string) $result, [
            '{PREFIX}' => (string) $sequence->prefix,
            '{YYYY}' => $date->format('Y'),
            '{YY}' => $date->format('y'),
            '{MM}' => $date->format('m'),
            '{DD}' => $date->format('d'),
        ]);
    }

    private function shouldReset(NumberSequence $sequence, CarbonInterface $date): bool
    {
        $last = $sequence->last_reset_at;
        if ($last === null) {
            return false;
        }

        return match ($sequence->reset_period) {
            'yearly' => $last->format('Y') !== $date->format('Y'),
            'monthly' => $last->format('Y-m') !== $date->format('Y-m'),
            default => false,
        };
    }
}
