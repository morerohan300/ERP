<?php

declare(strict_types=1);

namespace Modules\CRM\Http\Requests\Concerns;

use App\Support\Tenancy\CurrentCompany;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\Unique;
use Illuminate\Validation\Rule;

/**
 * Shared helpers for CRM Form Requests: tenant-safe "exists" / "unique" rules and
 * the create-vs-edit permission check.
 *
 * @mixin \Illuminate\Foundation\Http\FormRequest
 */
trait ChecksCompanyRules
{
    /** Whether the user holds "{permission}.create" (POST) or "{permission}.edit" (anything else). */
    protected function canWrite(string $permission): bool
    {
        return (bool) $this->user()?->can($permission.($this->isMethod('POST') ? '.create' : '.edit'));
    }

    /** A record of $table that belongs to the active company (and is not soft deleted). */
    protected function existsInCompany(string $table, string $column = 'id', bool $softDeletes = true): Exists
    {
        $rule = Rule::exists($table, $column)->where('company_id', $this->companyId());

        return $softDeletes ? $rule->whereNull('deleted_at') : $rule;
    }

    /** A user who is a member of the active company. */
    protected function companyUser(): Exists
    {
        return Rule::exists('company_user', 'user_id')->where('company_id', $this->companyId());
    }

    protected function uniqueInCompany(string $table, string $column, ?int $ignoreId = null): Unique
    {
        $rule = Rule::unique($table, $column)->where('company_id', $this->companyId())->whereNull('deleted_at');

        return $ignoreId === null ? $rule : $rule->ignore($ignoreId);
    }

    protected function companyId(): ?int
    {
        return app(CurrentCompany::class)->id();
    }

    /**
     * Validated input without the custom field values, which go through CustomFieldService.
     *
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return $this->safe()->except('custom_fields');
    }

    /** @return array<string, mixed> */
    public function customFields(): array
    {
        return (array) $this->validated('custom_fields', []);
    }
}
