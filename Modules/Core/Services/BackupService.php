<?php

declare(strict_types=1);

namespace Modules\Core\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Models\Backup;
use RuntimeException;
use ZipArchive;

/**
 * Creates a zipped mysqldump of the application database and keeps the
 * newest `keep` archives on the configured disk.
 */
class BackupService
{
    public const DISK = 'local';

    public const DIRECTORY = 'backups';

    public function run(Backup $backup): Backup
    {
        $backup->update(['status' => 'running']);

        $tmpDir = storage_path('app/backup-temp');
        File::ensureDirectoryExists($tmpDir);
        $stamp = now()->format('Y-m-d_His');
        $sqlFile = "{$tmpDir}/ledgerline_{$stamp}.sql";
        $zipFile = "{$tmpDir}/ledgerline_{$stamp}.zip";

        try {
            $this->dump($sqlFile);

            $zip = new ZipArchive;
            if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new RuntimeException('Could not create the backup archive.');
            }
            $zip->addFile($sqlFile, basename($sqlFile));
            $zip->close();

            $path = self::DIRECTORY.'/'.basename($zipFile);
            Storage::disk(self::DISK)->put($path, fopen($zipFile, 'rb'));

            $backup->update([
                'disk' => self::DISK,
                'path' => $path,
                'size' => Storage::disk(self::DISK)->size($path),
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            $this->prune((int) config('backup.keep', 14));
        } catch (\Throwable $e) {
            $backup->update(['status' => 'failed', 'error' => mb_substr($e->getMessage(), 0, 2000)]);

            throw $e;
        } finally {
            File::delete([$sqlFile, $zipFile]);
        }

        return $backup;
    }

    public function delete(Backup $backup): void
    {
        if ($backup->path) {
            Storage::disk($backup->disk)->delete($backup->path);
        }
        $backup->delete();
    }

    private function dump(string $target): void
    {
        $connection = config('database.connections.'.config('database.default'));

        if (($connection['driver'] ?? null) !== 'mysql' && ($connection['driver'] ?? null) !== 'mariadb') {
            throw new RuntimeException('Backups are only supported for MySQL/MariaDB connections.');
        }

        $binary = config('backup.mysqldump_path') ?: 'mysqldump';

        $command = [
            $binary,
            '--host='.$connection['host'],
            '--port='.$connection['port'],
            '--user='.$connection['username'],
            '--single-transaction',
            '--quick',
            '--routines',
            '--default-character-set=utf8mb4',
            '--result-file='.$target,
            $connection['database'],
        ];

        // Password via environment variable so it never appears in the process list.
        $result = Process::env(['MYSQL_PWD' => (string) ($connection['password'] ?? '')])
            ->timeout(1800)
            ->run($command);

        if ($result->failed()) {
            throw new RuntimeException('mysqldump failed: '.trim($result->errorOutput()));
        }
    }

    private function prune(int $keep): void
    {
        Backup::query()
            ->where('status', 'completed')
            ->latest('id')
            ->skip($keep)
            ->take(PHP_INT_MAX)
            ->get()
            ->each(fn (Backup $old) => $this->delete($old));
    }
}
