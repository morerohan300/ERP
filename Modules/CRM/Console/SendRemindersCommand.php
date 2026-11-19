<?php

declare(strict_types=1);

namespace Modules\CRM\Console;

use App\Support\Modules\ModuleManager;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Console\Command;
use Modules\Core\Models\Company;
use Modules\CRM\Models\Activity;
use Modules\CRM\Notifications\ActivityReminderNotification;

class SendRemindersCommand extends Command
{
    protected $signature = 'crm:send-reminders';

    protected $description = 'Notify owners of activities whose reminder time has passed';

    public function handle(CurrentCompany $currentCompany, ModuleManager $modules): int
    {
        $sent = 0;
        $tooOld = now()->subMinutes((int) config('crm.reminder_lookback_minutes', 120));

        foreach (Company::query()->cursor() as $company) {
            if (! $modules->enabled('crm', $company->getKey())) {
                continue;
            }

            $currentCompany->runAs($company, function () use ($tooOld, &$sent): void {
                Activity::query()->open()
                    ->whereNotNull('reminder_at')
                    ->whereNull('reminder_sent_at')
                    ->where('reminder_at', '<=', now())
                    ->with('owner')
                    ->get()
                    ->each(function (Activity $activity) use ($tooOld, &$sent): void {
                        // Reminders missed by a long outage are closed silently instead of arriving hours late.
                        if ($activity->reminder_at->greaterThanOrEqualTo($tooOld) && $activity->owner !== null) {
                            $activity->owner->notify(ActivityReminderNotification::for($activity));
                            $sent++;
                        }

                        $activity->forceFill(['reminder_sent_at' => now()])->save();
                    });
            });
        }

        $this->components->info("Sent {$sent} reminder(s).");

        return self::SUCCESS;
    }
}
