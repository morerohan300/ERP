<?php

declare(strict_types=1);

namespace Modules\Core\Models;

use App\Models\User;
use App\Support\Concerns\HasAuditColumns;
use App\Support\Concerns\HasUuid;
use App\Support\Numbering\HasDocumentNumber;
use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $current_step
 * @property string $status pending|approved|rejected|cancelled
 */
#[Fillable(['approval_workflow_id', 'approvable_type', 'approvable_id', 'current_step', 'status', 'requested_by', 'completed_at'])]
class ApprovalRequest extends Model
{
    use BelongsToCompany, HasAuditColumns, HasDocumentNumber, HasUuid;

    protected static string $documentType = 'approval_request';

    protected function casts(): array
    {
        return [
            'current_step' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(ApprovalWorkflow::class, 'approval_workflow_id');
    }

    public function approvable(): MorphTo
    {
        return $this->morphTo();
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function actions(): HasMany
    {
        return $this->hasMany(ApprovalAction::class)->latest('acted_at');
    }

    public function currentStepModel(): ?ApprovalStep
    {
        return $this->workflow->steps->firstWhere('step_order', $this->current_step);
    }

    /** @param Builder<ApprovalRequest> $query */
    public function scopePending(Builder $query): void
    {
        $query->where('status', 'pending');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
