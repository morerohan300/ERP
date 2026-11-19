<?php

declare(strict_types=1);

namespace Modules\CRM\Services;

use Illuminate\Database\Eloquent\Builder;
use Modules\CRM\Models\Contact;
use Modules\CRM\Models\Segment;

/**
 * Resolves the contacts that belong to a segment. Dynamic segments evaluate their
 * rules on demand; static segments use the hand-picked members.
 *
 * Rules are ANDed. Only the fields listed in FIELDS can be filtered on, so rule
 * data coming from a form can never reach the query as a column name.
 */
class SegmentService
{
    /** @var array<string, string> rule field => label */
    public const FIELDS = [
        'first_name' => 'First name',
        'last_name' => 'Last name',
        'email' => 'Email',
        'job_title' => 'Job title',
        'phone' => 'Phone',
        'owner_id' => 'Owner',
        'created_at' => 'Created on',
        'account.name' => 'Account name',
        'account.industry' => 'Account industry',
    ];

    /** @var array<string, string> operator => label */
    public const OPERATORS = [
        'equals' => 'equals',
        'not_equals' => 'does not equal',
        'contains' => 'contains',
        'starts_with' => 'starts with',
        'gt' => 'is after / greater than',
        'lt' => 'is before / less than',
        'filled' => 'is filled in',
        'empty' => 'is empty',
    ];

    /** @return Builder<Contact> */
    public function contacts(Segment $segment): Builder
    {
        if (! $segment->is_dynamic) {
            return Contact::query()->whereIn('id', $segment->members()->select('contacts.id'));
        }

        $query = Contact::query();

        foreach ($segment->rules ?? [] as $rule) {
            $field = $rule['field'] ?? null;
            $operator = $rule['operator'] ?? null;

            if (! isset(self::FIELDS[$field], self::OPERATORS[$operator])) {
                continue;
            }

            str_starts_with($field, 'account.')
                ? $query->whereHas('account', fn (Builder $q) => $this->apply($q, substr($field, 8), $operator, $rule['value'] ?? null))
                : $this->apply($query, $field, $operator, $rule['value'] ?? null);
        }

        return $query;
    }

    public function count(Segment $segment): int
    {
        return $this->contacts($segment)->count();
    }

    /**
     * @param  Builder<*>  $query
     */
    private function apply(Builder $query, string $column, string $operator, mixed $value): void
    {
        $value = is_scalar($value) ? (string) $value : null;
        $like = fn (string $pattern): string => addcslashes($pattern, '%_\\');

        match ($operator) {
            'equals' => $query->where($column, $value),
            'not_equals' => $query->where(fn (Builder $q) => $q->where($column, '!=', $value)->orWhereNull($column)),
            'contains' => $query->where($column, 'like', '%'.$like((string) $value).'%'),
            'starts_with' => $query->where($column, 'like', $like((string) $value).'%'),
            'gt' => $query->where($column, '>', $value),
            'lt' => $query->where($column, '<', $value),
            'filled' => $query->whereNotNull($column)->where($column, '!=', ''),
            'empty' => $query->where(fn (Builder $q) => $q->whereNull($column)->orWhere($column, '')),
        };
    }
}
