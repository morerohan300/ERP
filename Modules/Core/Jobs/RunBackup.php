<?php

declare(strict_types=1);

namespace Modules\Core\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Core\Models\Backup;
use Modules\Core\Services\BackupService;

final class RunBackup implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1800;

    public function __construct(public readonly Backup $backup) {}

    public function handle(BackupService $service): void
    {
        $service->run($this->backup);
    }
}
