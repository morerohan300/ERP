<?php

declare(strict_types=1);

namespace Modules\Core\Console;

use Illuminate\Console\Command;
use Modules\Core\Models\Backup;
use Modules\Core\Services\BackupService;

/**
 * Runs a database backup synchronously (used by the scheduler).
 */
class BackupCommand extends Command
{
    protected $signature = 'erp:backup';

    protected $description = 'Create a zipped database backup';

    public function handle(BackupService $service): int
    {
        $backup = Backup::query()->create(['status' => 'queued']);

        try {
            $service->run($backup);
        } catch (\Throwable $e) {
            $this->components->error('Backup failed: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->components->info("Backup created: {$backup->path} ({$backup->humanSize()})");

        return self::SUCCESS;
    }
}
