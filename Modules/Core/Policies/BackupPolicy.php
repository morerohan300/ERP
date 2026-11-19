<?php

declare(strict_types=1);

namespace Modules\Core\Policies;

use App\Models\User;
use Modules\Core\Models\Backup;

/**
 * Backups contain every company's data, so only company owners who also
 * hold the backup permission may manage them.
 */
class BackupPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isOwnerOfCurrentCompany() && $user->can('core.backup.view');
    }

    public function create(User $user): bool
    {
        return $user->isOwnerOfCurrentCompany() && $user->can('core.backup.create');
    }

    public function download(User $user, Backup $backup): bool
    {
        return $backup->status === 'completed' && $this->viewAny($user);
    }

    public function delete(User $user, Backup $backup): bool
    {
        return $user->isOwnerOfCurrentCompany() && $user->can('core.backup.delete');
    }
}
