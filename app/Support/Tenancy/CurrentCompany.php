<?php

declare(strict_types=1);

namespace App\Support\Tenancy;

use Modules\Core\Models\Branch;
use Modules\Core\Models\Company;

/**
 * Request-scoped holder for the active company (tenant) and branch.
 *
 * Bound as a scoped singleton so each request / queued job starts clean.
 * Everything that is company-specific (global scopes, numbering, settings)
 * resolves the tenant through this class — never from the session directly.
 */
final class CurrentCompany
{
    private ?Company $company = null;

    private ?Branch $branch = null;

    /** @var array<int>|null null = all branches allowed */
    private ?array $allowedBranchIds = null;

    public function set(?Company $company): void
    {
        $this->company = $company;
        $this->branch = null;
    }

    public function get(): ?Company
    {
        return $this->company;
    }

    public function id(): ?int
    {
        return $this->company?->getKey();
    }

    public function check(): bool
    {
        return $this->company !== null;
    }

    /**
     * @throws \RuntimeException when no company is active
     */
    public function getOrFail(): Company
    {
        return $this->company ?? throw new \RuntimeException('No active company has been set for this request.');
    }

    public function setBranch(?Branch $branch): void
    {
        $this->branch = $branch;
    }

    public function branch(): ?Branch
    {
        return $this->branch;
    }

    public function branchId(): ?int
    {
        return $this->branch?->getKey();
    }

    /** @param array<int>|null $ids */
    public function setAllowedBranchIds(?array $ids): void
    {
        $this->allowedBranchIds = $ids === null ? null : array_map('intval', $ids);
    }

    /** @return array<int>|null */
    public function allowedBranchIds(): ?array
    {
        return $this->allowedBranchIds;
    }

    /**
     * Temporarily run a callback as another company (used by seeders,
     * scheduled jobs and queued listeners), restoring the previous state.
     *
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    public function runAs(Company $company, callable $callback): mixed
    {
        $previousCompany = $this->company;
        $previousBranch = $this->branch;
        $previousAllowed = $this->allowedBranchIds;

        $this->company = $company;
        $this->branch = null;
        $this->allowedBranchIds = null;

        try {
            return $callback();
        } finally {
            $this->company = $previousCompany;
            $this->branch = $previousBranch;
            $this->allowedBranchIds = $previousAllowed;
        }
    }

    public function forget(): void
    {
        $this->company = null;
        $this->branch = null;
        $this->allowedBranchIds = null;
    }
}
