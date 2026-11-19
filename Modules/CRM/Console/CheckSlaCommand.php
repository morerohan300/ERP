<?php

declare(strict_types=1);

namespace Modules\CRM\Console;

use App\Support\Modules\ModuleManager;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Console\Command;
use Modules\Core\Models\Company;
use Modules\CRM\Models\Ticket;
use Modules\CRM\Notifications\SlaBreachedNotification;

class CheckSlaCommand extends Command
{
    protected $signature = 'crm:check-sla';

    protected $description = 'Notify the assignee of every open ticket that has missed its SLA deadline';

    public function handle(CurrentCompany $currentCompany, ModuleManager $modules): int
    {
        $notified = 0;

        foreach (Company::query()->cursor() as $company) {
            if (! $modules->enabled('crm', $company->getKey())) {
                continue;
            }

            $currentCompany->runAs($company, function () use (&$notified): void {
                Ticket::query()->breached()
                    ->whereNull('sla_breach_notified_at')
                    ->whereNotNull('assigned_to')
                    ->with('assignee')
                    ->get()
                    ->each(function (Ticket $ticket) use (&$notified): void {
                        $ticket->assignee?->notify(SlaBreachedNotification::for($ticket));
                        $ticket->forceFill(['sla_breach_notified_at' => now()])->save();
                        $notified++;
                    });
            });
        }

        $this->components->info("Notified {$notified} SLA breach(es).");

        return self::SUCCESS;
    }
}
