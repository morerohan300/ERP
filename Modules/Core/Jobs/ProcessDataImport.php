<?php

declare(strict_types=1);

namespace Modules\Core\Jobs;

use App\Models\User;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Core\Imports\UsersImport;
use Modules\Core\Models\Company;
use Modules\Core\Models\DataTransfer;
use Modules\Core\Services\UserService;

/**
 * Runs an uploaded import in the background, in the context of the
 * company and user that started it.
 */
class ProcessDataImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 900;

    public int $tries = 1;

    /**
     * Importers by "module.entity". Modules register theirs in their providers.
     *
     * @var array<string, class-string>
     */
    public static array $importers = [
        'core.user' => UsersImport::class,
    ];

    public function __construct(public readonly DataTransfer $transfer) {}

    public function handle(CurrentCompany $currentCompany): void
    {
        $transfer = $this->transfer;
        $company = Company::query()->findOrFail($transfer->company_id);
        $importer = self::$importers["{$transfer->module}.{$transfer->entity}"] ?? null;

        if ($importer === null) {
            $transfer->update(['status' => 'failed', 'errors' => [__('No importer is registered for this entity.')]]);

            return;
        }

        $user = $transfer->created_by ? User::query()->find($transfer->created_by) : null;
        if ($user !== null) {
            Auth::setUser($user);
        }

        $currentCompany->runAs($company, function () use ($transfer, $importer, $company): void {
            setPermissionsTeamId($company->getKey());

            try {
                $instance = $importer === UsersImport::class
                    ? new UsersImport($transfer, app(UserService::class))
                    : app($importer, ['transfer' => $transfer]);

                Excel::import($instance, $transfer->file_path, 'local');
            } catch (\Throwable $e) {
                $transfer->update(['status' => 'failed', 'errors' => [$e->getMessage()], 'completed_at' => now()]);
            }
        });
    }
}
