<?php

declare(strict_types=1);

namespace Modules\Core\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Modules\Core\Models\ApprovalRequest;

/**
 * Add to any document that can go through an approval workflow.
 * The model must define `approvalDocumentType()` (defaults to its
 * snake-cased class name) and may override `onApprovalCompleted()`.
 *
 * @mixin Model
 */
trait HasApprovals
{
    public function approvalRequests(): MorphMany
    {
        return $this->morphMany(ApprovalRequest::class, 'approvable')->latest();
    }

    public function latestApprovalRequest(): MorphOne
    {
        return $this->morphOne(ApprovalRequest::class, 'approvable')->latestOfMany();
    }

    public function approvalDocumentType(): string
    {
        return str(class_basename(static::class))->snake()->toString();
    }

    public function isPendingApproval(): bool
    {
        return $this->latestApprovalRequest?->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->latestApprovalRequest?->status === 'approved';
    }

    /** Short human label used in approval lists and notifications. */
    public function approvalLabel(): string
    {
        return $this->number ?? $this->name ?? $this->title ?? class_basename(static::class).' #'.$this->getKey();
    }

    /** Hook called by ApprovalService when the workflow finishes. */
    public function onApprovalCompleted(ApprovalRequest $request): void
    {
        // Documents override this to change status (e.g. PO → approved).
    }
}
