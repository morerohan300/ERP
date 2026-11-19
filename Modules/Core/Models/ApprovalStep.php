<?php

declare(strict_types=1);

namespace Modules\Core\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Role;

/**
 * @property int $step_order
 * @property string $approver_type user|role|department_manager
 * @property int|null $approver_id
 * @property int $min_approvals
 */
#[Fillable(['approval_workflow_id', 'step_order', 'name', 'approver_type', 'approver_id', 'min_approvals'])]
class ApprovalStep extends Model
{
    protected function casts(): array
    {
        return [
            'step_order' => 'integer',
            'min_approvals' => 'integer',
        ];
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(ApprovalWorkflow::class, 'approval_workflow_id');
    }

    public function approverUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function approverRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'approver_id');
    }

    public function approverLabel(): string
    {
        return match ($this->approver_type) {
            'user' => $this->approverUser?->name ?? __('Unknown user'),
            'role' => __('Role: :role', ['role' => $this->approverRole?->name ?? '?']),
            'department_manager' => __('Requester\'s department manager'),
            default => '—',
        };
    }
}
