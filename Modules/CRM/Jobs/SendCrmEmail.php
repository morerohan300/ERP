<?php

declare(strict_types=1);

namespace Modules\CRM\Jobs;

use App\Support\Tenancy\CurrentCompany;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Core\Models\Company;
use Modules\CRM\Models\EmailLog;
use Modules\CRM\Services\EmailService;

/**
 * Delivers a queued CRM email. Carries ids rather than models because tenant models
 * cannot be restored before the company context is set.
 */
class SendCrmEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function __construct(public readonly int $companyId, public readonly int $emailLogId) {}

    public function handle(CurrentCompany $currentCompany, EmailService $emails): void
    {
        $currentCompany->runAs(Company::query()->findOrFail($this->companyId), function () use ($emails): void {
            $log = EmailLog::query()->find($this->emailLogId);

            if ($log !== null && $log->status === 'queued') {
                $emails->deliver($log);
            }
        });
    }
}
