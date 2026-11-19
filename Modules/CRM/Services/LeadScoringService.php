<?php

declare(strict_types=1);

namespace Modules\CRM\Services;

use App\Support\Tenancy\CurrentCompany;
use Modules\CRM\Models\Lead;
use Modules\CRM\Models\LeadScoringRule;

/**
 * Scores a lead from 0 to 100 by adding the points of every active rule it satisfies.
 */
class LeadScoringService
{
    public function __construct(private readonly CurrentCompany $company) {}

    public function score(Lead $lead): int
    {
        if (! $this->company->check()) {
            return 0;
        }

        $total = LeadScoringRule::query()->active()->get()
            ->filter(fn (LeadScoringRule $rule): bool => $this->matches($rule, $lead))
            ->sum('points');

        return max(0, min(100, (int) $total));
    }

    public function matches(LeadScoringRule $rule, Lead $lead): bool
    {
        $actual = data_get($lead, $rule->field);
        $expected = $rule->value;

        return match ($rule->operator) {
            'filled' => filled($actual),
            'empty' => blank($actual),
            'equals' => $actual !== null && mb_strtolower((string) $actual) === mb_strtolower((string) $expected),
            'not_equals' => $actual === null || mb_strtolower((string) $actual) !== mb_strtolower((string) $expected),
            'contains' => $actual !== null && $expected !== null && $expected !== '' && str_contains(mb_strtolower((string) $actual), mb_strtolower((string) $expected)),
            'gt' => is_numeric($actual) && is_numeric($expected) && (float) $actual > (float) $expected,
            'lt' => is_numeric($actual) && is_numeric($expected) && (float) $actual < (float) $expected,
            default => false,
        };
    }

    /** Re-score every open lead of the active company (used after the rules change). */
    public function rescoreAll(): int
    {
        $count = 0;

        Lead::query()->open()->each(function (Lead $lead) use (&$count): void {
            $score = $this->score($lead);
            if ($lead->score !== $score) {
                Lead::query()->whereKey($lead->getKey())->update(['score' => $score]);
                $count++;
            }
        });

        return $count;
    }
}
