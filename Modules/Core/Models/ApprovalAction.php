<?php

declare(strict_types=1);

namespace Modules\Core\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['approval_request_id', 'approval_step_id', 'user_id', 'action', 'comment', 'acted_at'])]
class ApprovalAction extends Model
{
    protected function casts(): array
    {
        return ['acted_at' => 'datetime'];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(ApprovalRequest::class, 'approval_request_id');
    }

    public function step(): BelongsTo
    {
        return $this->belongsTo(ApprovalStep::class, 'approval_step_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
